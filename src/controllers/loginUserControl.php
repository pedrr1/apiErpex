<?php
require_once __DIR__ . '/../exceptions/apiException.php';
require_once __DIR__ . '/../database/elastic-service.php';
require_once __DIR__ . '/log.php';
require_once __DIR__ . '/../services/loginUser.php';

class loginUserControl{
    private LoginUserService $service;
}