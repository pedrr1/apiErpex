<?php
require_once __DIR__ . '/../exceptions/apiException.php';
require_once __DIR__ . '/../database/elastic-service.php';
require_once __DIR__ . '/log.php';
require_once __DIR__ . '/../services/loginUser.php';
require_once __DIR__ . '/../http/loginUser/loginUserRequest.php';
require_once __DIR__ . '/../http/loginUser/loginUserResponse.php';
require_once __DIR__ . '/../repository/loginUserRepository.php';
require_once __DIR__ . '/../repository/cache/loginUserCache.php';
require_once __DIR__ . '/email/send-email.php';


class loginUserControl
{
    private LoginUserRepository $repository;
    private LoginUserRequest $request;
    private LoginUserResponse $response;
    private LoginUserService $service;
    private loginUserCache $cache;
    private EmailService $email;
    private array $env;

    public function __construct(mysqli $db, Redis $redis, array $env)
    {

        $this->response = new LoginUserResponse();
        $this->cache = new LoginUserCache($redis);
        $this->repository = new LoginUserRepository($db, $this->cache);
        $this->env = $env;
        $this->email = new EmailService($env);
        $this->service = new LoginUserService($this->repository, $this->email);
    }

    public function loginUser(): void
    {
        try {
            $this->request = new LoginUserRequest();
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
            $this->request->authLogin($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authLogin', $service, $duration, $traceId);

            $start = microtime(true);
            $user = $this->service->getUser($body['NameUser'] ?? $body['EmailUser'] ?? $body['GoogleUid'] ?? '', $body['Password'] ?? null);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->service, 'getUser', $service, $duration, $traceId);

            $start = microtime(true);
            $this->response->loginResponse($user);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->response, 'loginResponse', $service, $duration, $traceId);
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

    public function recoverPassword(): void
    {
        try{
            $this->request = new LoginUserRequest();
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
            $this->request->authEmail($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authEmail', $service, $duration, $traceId);

            $start = microtime(true);
            $this->service->createCode($body['EmailUser']);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->service, 'createCode', $service, $duration, $traceId);
            
            $start = microtime(true);
            $this->response->loginResponse(['message' => "Código enviado com sucesso"]);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->response, 'loginResponse', $service, $duration, $traceId);
        
        }
        catch (\Throwable $e){
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

    public function getPasswordCode(): void{

        try{
            $this->request = new LoginUserRequest();
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
            $this->request->authEmail($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authEmail', $service, $duration, $traceId);

            $start = microtime(true);
            $this->request->authCode($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authCode', $service, $duration, $traceId);

            $start = microtime(true);
            $this->service->getCode($body['EmailUser'], $body['CodeEmail']);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->service, 'getCode', $service, $duration, $traceId);
            
            $start = microtime(true);
            $this->response->loginResponse(['message' => "Código recebido com sucesso!!"]);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->response, 'loginResponse', $service, $duration, $traceId);
        
        }
        catch (\Throwable $e){
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

    public function updatePassword(): void{

        try{
            $this->request = new LoginUserRequest();
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
            $this->request->authEmail($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authEmail', $service, $duration, $traceId);


            $start = microtime(true);
            $this->request->authPass($this->env);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->request, 'authPass', $service, $duration, $traceId);

            $start = microtime(true);
            $this->service->updatePass($body["EmailUser"], $body['PassUser']);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->service, 'updatePass', $service, $duration, $traceId);
            
            $start = microtime(true);
            $this->response->loginResponse(['message' => "Senha alterada"]);
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logSucess($this->response, 'loginResponse', $service, $duration, $traceId);
        
        }
        catch (\Throwable $e){
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
