<?php
require_once __DIR__ . '/../../exceptions/apiException.php';
require_once __DIR__ . '/../../database/elastic-service.php';
require_once __DIR__ . '/../../controllers/log.php';
abstract class BaseCache{

        protected Redis $redis;

        public function __construct(Redis $redis){
                $this->redis = $redis;
        }

        protected function logCache(string $endpoint, string $metodo, int $duration):void{
                $log = Log::logMetric(
                endpoint: $endpoint,
                method: $metodo,
                responseTimeMs: $duration,
                cacheHit: true
        );

        Elasticsearch::postDoc('erp-metrics-performance', $log);
        }

}

?>
