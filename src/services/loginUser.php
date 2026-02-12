<?php
require_once __DIR__ . '/../exceptions/apiException.php';

class LoginUserService
{
    private LoginUserRepository $repository;

    public function __construct(LoginUserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUser(){
        
    }

}