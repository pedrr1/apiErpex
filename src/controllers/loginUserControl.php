<?php
require_once __DIR__ . '/../exceptions/apiException.php';
require_once __DIR__ . '/../database/elastic-service.php';
require_once __DIR__ . '/log.php';
require_once __DIR__ . '/../services/loginUser.php';
require_once __DIR__ . '/../http/loginUser/loginUserRequest.php';
require_once __DIR__ . '/../http/loginUser/loginUserResponse.php';
require_once __DIR__ . '/../repository/loginUserRepository.php';
require_once __DIR__ . '/../repository/cache/loginUserCache.php';

class loginUserControl{
    private LoginUserRepository $repository;
    private LoginUserRequest $request;
    private LoginUserResponse $response;
    private LoginUserService $service;
    private loginUserCache $cache;
    private array $env;

    public function __construct(mysqli $db, Redis $redis, array $env){
        $this->response = new LoginUserResponse();
      $this->email = new EmailService($env);
      $this->cache = new LoginUserCache($redis);
      $this->repository = new LoginUserRepository($db, $this->cache);
      $this->service = new LoginUserService($this->repository);
      $this->env = $env;
    }

    public function loginUser(){
        $this->request = new LoginUserRequest();
        $headers = $this->request->getHeaders();
        $body = $this->request->getBody();
        
        $this->request->authEmail($this->env);
        $user=$this->service->getUser(($headers['NameUser']?? $headers['EmailUser']));
        $this->response->loginResponse($user);
    }
}