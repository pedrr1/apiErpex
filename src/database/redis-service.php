<?php
require_once __DIR__ . '/../security/env.php';
require_once __DIR__ . '/elastic-service.php';
require_once __DIR__ . '/../controllers/log.php';

$redis = new Redis();
$user = $env['DB_HOST_REDIS'];
Elasticsearch::init($env['DB_HOST_ES']);

$headers = getallheaders();

try {
    // Conecta no Redis
    $redis->connect($user, 6379);

} catch (Exception $e) {
    $log = Log::logApp(
        level: 'Error',
        message: 'Não foi possivel se conectar com o Redis',
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
