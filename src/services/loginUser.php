<?php
require_once __DIR__ . '/../exceptions/apiException.php';

class LoginUserService
{
    private LoginUserRepository $repository;
    private EmailService $email;

    public function __construct(LoginUserRepository $repository,EmailService $email)
    {
        $this->repository = $repository;
        $this->email = $email;
    }

    public function getUser(string $login, ?string $password): array{
       $user = $this->repository->getUser($login);
       
       if (isset($password) && $user['senha_hash'] !== $password) {
           throw new ApiException("Senha invalida", 401);
       }

       unset($user['senha_hash']);

       return $user;
    }

    public function createCode(string $email){
       $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); 
       $this->repository->setCodeEmail($code, $email);
       $this->email->enviarCodigo($email, $code);
    }

}