<?php
require_once __DIR__ . '/../response-base.php';

class LoginUserResponse extends BaseResponse{
    public function loginResponse($user):void{
        $this->data = $user;
        $this->send();
    }
}
?>