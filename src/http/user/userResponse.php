<?php
require_once __DIR__ . '/../response-base.php';

class UserResponse extends BaseResponse
{
    public function userResponse($user): void
    {
        $this->data = $user;
        $this->send();
    }
}
?>