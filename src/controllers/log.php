<?php
class Log {
    // Templates privados
    private static array $log = [
        '@timestamp' => null,
        'level' => null,
        'message' => null,
        'environment' => null,
        'service' => ['name' => null, 'version' => null],
        'http' => ['method' => null, 'path' => null, 'status_code' => null, 'response_time_ms' => null],
        'client' => ['ip' => null, 'user_agent' => null],
        'user' => ['id' => null, 'role' => null],
        'trace' => ['id' => null],
        'error' => ['type' => null, 'message' => null, 'stacktrace' => null],
    ];

    private static array $metric = [
        '@timestamp' => null,
        'endpoint' => null,
        'method' => null,
        'response_time_ms' => null,
        'cache_hit' => null,
        'db' => ['query_time_ms' => null, 'rows' => null],
    ];

    private static array $auditLog = [
        '@timestamp' => null,
        'action' => null,
        'entity' => null,
        'entity_id' => null,
        'before' => null,
        'after' => null,
        'client_ip' => null,
        'performed_by' => ['user_id' => null, 'role' => null],
    ];

    /**
     * Retorna um log de app pronto, preenchendo apenas os campos passados
     * Qualquer campo que não quiser preencher, passa null ou omite
     */
    public static function logApp(
        ?string $timestamp = null,
        ?string $level = null,
        ?string $message = null,
        ?string $environment = null,
        ?array $service = null,
        ?array $http = null,
        ?array $client = null,
        ?array $user = null,
        ?array $trace = null,
        ?array $error = null
    ): array {
        $log = self::$log;
        $log['@timestamp'] = $timestamp ?? gmdate('c');
        $log['level'] = $level;
        $log['message'] = $message;
        $log['environment'] = $environment;

        if ($service) $log['service'] = array_merge($log['service'], $service);
        if ($http) $log['http'] = array_merge($log['http'], $http);
        if ($client) $log['client'] = array_merge($log['client'], $client);
        if ($user) $log['user'] = array_merge($log['user'], $user);
        if ($trace) $log['trace'] = array_merge($log['trace'], $trace);
        if ($error) $log['error'] = array_merge($log['error'], $error);

        return $log;
    }

    /**
     * Retorna uma métrica pronta
     */
    public static function logMetric(
        ?string $timestamp = null,
        ?string $endpoint = null,
        ?string $method = null,
        ?int $responseTimeMs = null,
        ?bool $cacheHit = null,
        ?array $db = null
    ): array {
        $metric = self::$metric;
        $metric['@timestamp'] = $timestamp ?? gmdate('c');
        $metric['endpoint'] = $endpoint;
        $metric['method'] = $method;
        $metric['response_time_ms'] = $responseTimeMs;
        $metric['cache_hit'] = $cacheHit;
        if ($db) $metric['db'] = array_merge($metric['db'], $db);

        return $metric;
    }

    /**
     * Retorna log de auditoria pronto
     */
    public static function logAudit(
        ?string $timestamp = null,
        ?string $action = null,
        ?string $entity = null,
        ?string $entityId = null,
        ?array $before = null,
        ?array $after = null,
        ?string $clientIp = null,
        ?array $performedBy = null
    ): array {
        $audit = self::$auditLog;
        $audit['@timestamp'] = $timestamp ?? gmdate('c');
        $audit['action'] = $action;
        $audit['entity'] = $entity;
        $audit['entity_id'] = $entityId;
        $audit['before'] = $before;
        $audit['after'] = $after;
        $audit['client_ip'] = $clientIp;
        if ($performedBy) $audit['performed_by'] = array_merge($audit['performed_by'], $performedBy);

        return $audit;
    }
}

