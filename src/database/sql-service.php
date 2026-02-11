<?php
require_once __DIR__ . '/../security/env.php';
require_once __DIR__ . '/elastic-service.php';
require_once __DIR__ . '/../controllers/log.php';

$host = $env['DB_HOST'];
$dbname = $env['DB_DTBASE'];
$pass = $env['DB_PASS'];
$user = $env['DB_USER'];

Elasticsearch::init($env['DB_HOST_ES']);
$headers = getallheaders();

try {
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }

} catch (Exception $e) {
        $log = Log::logApp(
        level: 'Error',
        message: 'Não foi possivel se conectar com o MariaDb',
        environment: $env['APP_ENV'],
        service: [
                'name' => $headers['X-client-app'] ?? null,
                'version' => $headers['X-clien-appVersion'] ?? null
        ],
        client: [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $headers['User-Agent'] ?? null
        ],
        error: [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'stacktrace' => $e->getTraceAsString()
        ]
    );

    Elasticsearch::postDoc('erp-logs-app', $log);
    
        http_response_code(500);
        exit;
}


?>
