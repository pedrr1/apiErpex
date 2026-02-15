<?php
require_once __DIR__ . '/../request-base.php';

class LoginUserRequest extends BaseRequest{
     public function authLogin(array $env): void
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();
        $email = $this->rawBody['EmailUser'] ?? '';
        $name = $this->rawBody['NameUser'] ?? '';

        if (empty($email) && empty($name)) {
            throw new ApiException("Identificador Invalido", 400);
        }
    }

    public function authEmail(array $env):void
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();
        $email = $this->rawBody['EmailUser'] ?? '';

        if (empty($email)) {
            throw new ApiException("Email Invalido", 400);
        }
    }
}
?>