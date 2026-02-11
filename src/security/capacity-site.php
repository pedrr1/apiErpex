<?php
require_once __DIR__ . '../database/redis-service.php';
require_once __DIR__ . '../database/elastic-service.php';
require_once __DIR__ . '../controllers/log.php';
require_once 'env.php';
class antiDdosRedis{

public static function veriMaxReq():void{
Elasticsearch::init($env['DB_HOST_ES']);
$key = 'capacity:site:' . time();
$start = microtime(true);

$count = $redis->incr($key);
if ($count === 1) {
    $redis->expire($key, 1);
}

if ($count > 300) {
   $success = false;
   $duration = (int)((microtime(true) - $start) * 1000);

    // Aqui faz o log de limite excedido
    $log = Log::logApp(
        level: 'warning',
        message: 'Rate limit excedido',
        environment: $env['APP_ENV'] ?? null,
        service: ['name' => 'redis-service', 'version' => '1.0'],
        trace: ['id' => $traceId],
        client: [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ],
        http: [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'path' => $_SERVER['REQUEST_URI'] ?? null,
            'status_code' => 503,
            'response_time_ms' => $duration,
        ]
    );

    $log = Elasticsearch::formatArray($log);
    Elasticsearch::postDoc('erp-logs-app', $log);
   }

$duration = (int)((microtime(true) - $start) * 1000);
$success = true;
$metric = Log::logMetric(
    endpoint: 'capacity-site',
    method: 'INCR',
    responseTimeMs: $duration,
    cacheHit: $success
);

Elasticsearch::postDoc('erp-metrics-performance', Elasticsearch::formatArray($metric));

if (!$success) {
    http_response_code(503);
    exit;
}
}
}
?>
