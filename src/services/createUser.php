<?php
require_once __DIR__ . '/../exceptions/apiException.php';
class CreateUserService
{

    private CreateUserRepository $repository;
    private EmailService $mail;

    public function __construct(CreateUserRepository $repository,  EmailService $mail)
    {
        $this->repository = $repository;
        $this->mail = $mail;
    }

    public function createCode(string $email): void
    {
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        // regra de negócio
        $this->repository->verifyEmailCode($email, $code);
        $this->mail->sendCode($email, $code, 'codigo_cadastro');
    }

    public function insertCode(string $email, string $code)
    {
        $this->repository->insertCodeEmail($email, $code);
    }
    public function insertAcount(
        string $name,
        string $email,
        string $cpf,
        ?string $telefone = null,
        ?string $googleUid = null,
        string $senhaHash
    ): void {
        $uidRequest = bin2hex(random_bytes(16));
        $this->authAllInfo($name, $email, $cpf, $telefone);
        $this->repository->getAllInfos($name, $email, $cpf, $telefone, $uidRequest, $googleUid);
        $pathFoto = $this->repository->addFoto($uidRequest);
        $this->repository->insertAcount($name, $email, $cpf, $telefone, $uidRequest, $googleUid, $senhaHash, $pathFoto);
    }

    private function authAllInfo(string $name, string $email, string $cpf, ?string $telefone = null): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new ApiException(
                "O campo username só pode conter letras, números, _ e -",
                422
            );
        }

        if (strlen($name) < 5) {
            throw new ApiException(
                "O campo username deve ter no mínimo 5 caracteres",
                422
            );
        }


        $this->authCpf($cpf);
        $this->authCelular($telefone);
        $this->authFoto();
    }
    private function authFoto(): void
    {
        if (
            isset($_FILES['UserFoto']) &&
            $_FILES['UserFoto']['error'] === UPLOAD_ERR_OK
        ) {

            $info = getimagesize($_FILES['UserFoto']['tmp_name']);
            if ($info === false) {
                throw new ApiException("Arquivo não é uma imagem válida", 422);
            }

            // pega extensão
            $extensao = strtolower(pathinfo($_FILES['UserFoto']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png'];

            if (!in_array($extensao, $permitidos)) {
                throw new ApiException("Extensão não permitida", 422);
            }
        }
    }

    private function authCpf(string $cpf): void
    {
        // Remove tudo que não for número (caso queira aceitar com pontos e traço)
        $cpf = preg_replace('/\D/', '', $cpf);

        // Deve ter 11 dígitos
        if (strlen($cpf) != 11) {
            throw new ApiException("O CPF só pode ter 11 caracteres", 422);
        }

        // Não pode ter todos os dígitos iguais
        if (preg_match('/^(.)\1{10}$/', $cpf)) {
            throw new ApiException("O CPF não pode ter todos os dígitos iguais", 422);
        }

        // Valida 1º e 2º dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($c = 0; $c < $t; $c++) {
                $soma += intval($cpf[$c]) * (($t + 1) - $c);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if (intval($cpf[$t]) != $digito) {
                throw new ApiException("CPF inválido", 422);
            }
        }
    }


    private function authCelular(?string $celular = null): void
    {
        if (!empty($celular)) {
            // Remove tudo que não for número
            $numero = preg_replace('/\D/', '', $celular);

            // Deve ter exatamente 11 dígitos (DDD + 9 dígitos)
            if (!preg_match('/^\d{11}$/', $numero)) {
                throw new ApiException(
                    "Número de celular inválido. Deve ter DDD e 9 dígitos",
                    422
                );
            }

            // O primeiro dígito do número (após DDD) deve ser 9
            $inicio = substr($numero, 2, 1);
            if ($inicio != '9') {
                throw new ApiException(
                    "Número de celular inválido. Celular deve começar com 9",
                    422
                );
            }
        }
    }

}
