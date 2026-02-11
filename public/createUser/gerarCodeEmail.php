<?php
require_once __DIR__ . '/../../src/database/redis-service.php';
require_once __DIR__ . '/../../src/database/sql-service.php';
require_once __DIR__ . '/../../src/security/env.php';
require_once __DIR__ . '/../../src/controllers/createUserControl.php';

$create = new createUserControl($conn, $redis, $env);
$create->gerarCodigo();
?>
