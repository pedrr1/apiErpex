<?php
require_once __DIR__ . '/../exceptions/apiException.php';
require_once __DIR__ . '/../database/elastic-service.php';
require_once __DIR__ . '/../controllers/log.php';
abstract class BaseRepository {
    protected mysqli $db;
    protected CreateUserCache $cache;

    public function __construct(mysqli $db, CreateUserCache $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    protected function logRepository(string $endpoint, string $metodo, int $duration, int $rows,
                                    string $action, string $entidade, ?int $entidadeId = null, ?array $before = null,
                                    ?array $after = null, ?string $userId = null, ?string $roleName = null):void{
        $log = Log::logMetric(
                    endpoint: $endpoint,
                    method: $metodo,
                    cacheHit: false,
                    db: [
                        'query_time_ms' => $duration,
                        'rows' => $rows
                    ]
                    );
                Elasticsearch::postDoc('erp-metrics-performance', $log);

                $logAudit = Log::logAudit(
                    action: $action,
                    entity: $entidade,
                    entityId: $entidadeId,
                    before: $before,
                    after: $after,
                    clientIp: $_SERVER['REMOTE_ADDR'],
                    performedBy: [
                        'user_id' => $userId, 
                        'role' => $roleName
                    ]
                );
                Elasticsearch::postDoc('erp-logs-audit', $logAudit);
    } 
    

}
?>
