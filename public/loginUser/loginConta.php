<?php
require_once __DIR__ . '/../../src/database/redis-service.php';
require_once __DIR__ . '/../../src/database/sql-service.php';
require_once __DIR__ . '/../../src/security/env.php';
require_once __DIR__ . '/../../src/controllers/loginUserControl.php';

$create = new loginUserControl($conn, $redis, $env);
$create->loginUser();
?>