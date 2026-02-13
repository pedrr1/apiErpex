<?php
require_once __DIR__ . '/../exceptions/apiException.php';

class LoginUserService
{
    private LoginUserRepository $repository;

    public function __construct(LoginUserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUser(string $login){
       $user = $this->repository->getUser($login);
       $user['foto_perfil'] = 'https://api.sophia-me13.site/src/repository/fotos/user/'.$user['foto_perfil'];
       return $user;
    }

}