<?php
require_once __DIR__ . '/../response-base.php';

class CreateUserResponse extends BaseResponse {

    public function addCode(string $email): void {
        $this->data = [
            'Message' => 'Codigo Enviado',
            'Email' => $email
        ];
        $this->send();
    }

     public function insertCode(string $email): void {
        $this->data = [
            'Message' => 'Codigo Validado',
            'Email' => $email
        ];
        $this->send();
    }

    public function createAcount(): void {
     $this->data = [
            'Message' => 'Conta criada com exito'
        ];
        $this->send();
    }
}

?>
