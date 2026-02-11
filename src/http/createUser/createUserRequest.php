<?php
require_once __DIR__ . '/../request-base.php';

class CreateUserRequest extends BaseRequest
{
    private function authFoto(): void
    {
        if (!isset($_FILES['UserFoto'])) {
            throw new ApiException("Arquivo não enviado", 422);
        }

        if ($_FILES['UserFoto']['error'] !== UPLOAD_ERR_OK) {
            throw new ApiException("Erro ao fazer upload", 500);
        }
    }

    public function authEmail(array $env): void
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();

        $email = $this->rawBody['EmailUser'] ?? '';

        if (empty($email)) {
            throw new ApiException("Email inválido", 400);
        }
    }

    public function authCode(array $env): void
    {
        $this->authEmail($env);

        $code = $this->rawBody['EmailCode'] ?? null;

        if (is_null($code)) {
            throw new ApiException("Código de email não pode ser null", 400);
        }
    }

    public function authAllInfo(array $env): array
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();
        $this->authFoto();

        $requiredFields = [
            'EmailUser' => 'Email inválido',
            'NameUser'  => 'Nome inválido',
            'CpfUser'   => 'CPF inválido',
            'Password'  => 'Senha inválida'
        ];

        foreach ($requiredFields as $field => $errorMessage) {
            if (empty($this->rawBody[$field])) {
                throw new ApiException($errorMessage, 400);
            }
        }

        return $this->rawBody;
    }
}
?>
