<?php
require_once __DIR__ . '/../request-base.php';

class LoginUserRequest extends BaseRequest{
     public function authEmail(array $env): void
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();
        $email = $this->rawBody['EmailUser'] ?? '';

        if (empty($email)) {
            throw new ApiException("Email inválido", 400);
        }
    }
}
?>