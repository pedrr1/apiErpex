<?php
require_once __DIR__ . '/../exceptions/apiException.php';
require_once __DIR__ . '/../database/elastic-service.php';
require_once __DIR__ . '/log.php';
require_once __DIR__ . '/../repository/userRepository.php';
require_once __DIR__ . '/../repository/cache/userCache.php';
require_once __DIR__ . '/../http/user/userResponse.php';
require_once __DIR__ . '/../http/user/userRequest.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/email/send-email.php';
require_once __DIR__ . '/pix/generate_pix.php';

class UserControl
{
    private UserRepository $repository;
    private UserRequest $request;
    private UserResponse $response;
    private UserService $service;
    private UserCache $cache;
    private EmailService $email;
    private PixController $pix;
    private array $env;

    public function __construct(mysqli $db, Redis $redis, array $env)
    {
        $this->response = new UserResponse();
        $this->cache = new UserCache($redis);
        $this->repository = new UserRepository($db, $this->cache);
        $this->env = $env;
        $this->email = new EmailService($env);
        $this->pix = new PixController();
        $this->service = new UserService($this->repository, $this->email, $this->pix);
    }

    public function getInfos(): void
    {
        try {
            $this->request = new UserRequest();
            $traceId = bin2hex(random_bytes(16));

            $start = microtime(true);
            $headers = $this->request->getHeaders();
            $service = [
                'name' => $headers['X-Client-App'],
                'version' => $headers['X-Version-App'] ?? null
            ];

            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getHeaders', $service, $duration, $traceId);

            $start = microtime(true);
            $body = $this->request->getBody();
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getBody', $service, $duration, $traceId);

            $start = microtime(true);
            $this->request->authUser($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authUser', $service, $duration, $traceId);

            $start = microtime(true);
            $user = $this->service->getUser($body['IdRequest']);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->service, 'getUser', $service, $duration, $traceId);

            $start = microtime(true);
            $this->response->userResponse($user);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->response, 'userResponse', $service, $duration, $traceId);
        } catch (\Throwable $e) {
            $statusCode = 500;

            if ($e instanceof ApiException && isset($e->statusCode)) {
                $statusCode = $e->statusCode;
            }

            $error = [
                'type'       => get_class($e),
                'message'    => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ];

            $this->logError($service ?? null, $e->getFile(), $statusCode, $traceId ?? null, $error);
            http_response_code($statusCode);

            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'traceId' => $traceId ?? null
                ]
            ]);
            exit;
        }
    }

    public function generatePix(): void
    {
        try {
            $this->request = new UserRequest();
            $traceId = bin2hex(random_bytes(16));

            $start = microtime(true);
            $headers = $this->request->getHeaders();
            $service = [
                'name' => $headers['X-Client-App'],
                'version' => $headers['X-Version-App'] ?? null
            ];

            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getHeaders', $service, $duration, $traceId);

            $start = microtime(true);
            $body = $this->request->getBody();
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getBody', $service, $duration, $traceId);

            $start = microtime(true);
            $this->request->authPlan($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authPlan', $service, $duration, $traceId);

            $start = microtime(true);
            $pixData = $this->service->generatePix($body['Plano'], $this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->service, 'generatePix', $service, $duration, $traceId);

            $start = microtime(true);
            $this->response->userResponse($pixData);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->response, 'userResponse', $service, $duration, $traceId);
        } catch (\Throwable $e) {
            $statusCode = 500;

            if ($e instanceof ApiException && isset($e->statusCode)) {
                $statusCode = $e->statusCode;
            }

            $error = [
                'type'       => get_class($e),
                'message'    => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ];

            $this->logError($service ?? null, $e->getFile(), $statusCode, $traceId ?? null, $error);
            http_response_code($statusCode);

            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'traceId' => $traceId ?? null
                ]
            ]);
            exit;
        }
    }

    public function addDevices(): void
    {

        try {
            $this->request = new UserRequest();
            $traceId = bin2hex(random_bytes(16));

            $start = microtime(true);
            $headers = $this->request->getHeaders();
            $service = [
                'name' => $headers['X-Client-App'],
                'version' => $headers['X-Version-App'] ?? null
            ];

            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getHeaders', $service, $duration, $traceId);

            $start = microtime(true);
            $body = $this->request->getBody();
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getBody', $service, $duration, $traceId);

            $start = microtime(true);
            $this->request->authDevice($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authDevice', $service, $duration, $traceId);

            $start = microtime(true);
            $this->service->addDevices($body['IdDevice']);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->service, 'checkDevices', $service, $duration, $traceId);

           $start = microtime(true);
            $this->response->userResponse(['message' => 'Dispositivo adicionado com sucesso']);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->response, 'userResponse', $service, $duration, $traceId);

        } catch (\Throwable $e) {
            $statusCode = 500;

            if ($e instanceof ApiException && isset($e->statusCode)) {
                $statusCode = $e->statusCode;
            }

            $error = [
                'type'       => get_class($e),
                'message'    => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ];

            $this->logError($service ?? null, $e->getFile(), $statusCode, $traceId ?? null, $error);
            http_response_code($statusCode);

            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'traceId' => $traceId ?? null
                ]
            ]);
            exit;
        }
    }

    public function addDeviceUser(): void
    {
        try {
            $this->request = new UserRequest();
            $traceId = bin2hex(random_bytes(16));

            $start = microtime(true);
            $headers = $this->request->getHeaders();
            $service = [
                'name' => $headers['X-Client-App'],
                'version' => $headers['X-Version-App'] ?? null
            ];

            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getHeaders', $service, $duration, $traceId);

            $start = microtime(true);
            $body = $this->request->getBody();
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getBody', $service, $duration, $traceId);

            $start = microtime(true);
            $this->request->authUser($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authUser', $service, $duration, $traceId);

            $start = microtime(true);
            $this->request->authDevice($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authDevice', $service, $duration, $traceId);

            $start = microtime(true);
            $this->service->addDeviceUser($body['IdRequest'], $body['DeviceInfo']);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->service, 'addDeviceUser', $service, $duration, $traceId);


            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            $statusCode = 500;

            if ($e instanceof ApiException && isset($e->statusCode)) {
                $statusCode = $e->statusCode;
            }

            $error = [
                'type'       => get_class($e),
                'message'    => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString(),
            ];

            $this->logError($service ?? null, $e->getFile(), $statusCode, $traceId ?? null, $error);
            http_response_code($statusCode);

            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'traceId' => $traceId ?? null
                ]
            ]);
            exit;
        }
    }

    private function addSignature(): void
    {
        try {
            $this->request = new UserRequest();
            $traceId = bin2hex(random_bytes(16));

            $start = microtime(true);
            $headers = $this->request->getHeaders();
            $service = [
                'name' => $headers['X-Client-App'],
                'version' => $headers['X-Version-App'] ?? null
            ];

            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getHeaders', $service, $duration, $traceId);

            $start = microtime(true);
            $body = $this->request->getBody();
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'getBody', $service, $duration, $traceId);

            $start = microtime(true);
            $this->request->authPix($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authPix', $service, $duration, $traceId);

                $start = microtime(true);
                $this->service->addSignature($body['PixId']);
        } catch (\Throwable $e) {
            // Tratamento de erros semelhante aos outros métodos
        }
    }

    private function logError(?array $service = null, string $path, ?int $code = null, $traceId, array $error): void
    {
        $log = Log::logApp(
            level: 'Error',
            message: 'Erro no fluxo da requisição',
            environment: $this->env['APP_ENV'],
            service: $service ?? null,
            http: [
                'path' => $path,
                'status_code' => $code
            ],
            client: [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido'
            ],
            trace: [
                'id' => $traceId ?? null
            ],
            error: $error

        );
        Elasticsearch::postDoc('erp-logs-app', $log);
    }

    private function logSucess(object $obj, string $metodo, ?array $service = null, int $duration, $traceId): void
    {
        $ref = new ReflectionMethod($obj, $metodo);

        $log = Log::logApp(
            level: 'INFO',
            environment: $this->env['APP_ENV'],
            service: $service ?? null,
            http: [
                'method' => $ref->getDeclaringClass()->getName() . '::' . $ref->getName(),
                'path' => $ref->getFileName(),
                'status_code' => 200,
                'response_time_ms' => $duration
            ],
            client: [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'desconhecido'
            ],
            trace: [
                'id' => $traceId
            ]
        );

        Elasticsearch::postDoc('erp-logs-app', $log);
    }
}
