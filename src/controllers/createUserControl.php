<?php
require_once __DIR__ . '/../http/createUser/createUserRequest.php';
require_once __DIR__ . '/../http/createUser/createUserResponse.php';
require_once __DIR__ . '/../repository/createUser-repository.php';
require_once __DIR__ . '/../repository/cache/createUser-cache.php';
require_once __DIR__ . '/../services/createUser.php';
require_once __DIR__ . '/email/send-email.php';
require_once __DIR__ . '/../exceptions/apiException.php';
require_once __DIR__ . '/../database/elastic-service.php';
require_once __DIR__ . '/log.php';

//Cuida do fluxo de todas as etapas da cração de usuario
class createUserControl
{

   private CreateUserRequest $request;
   private CreateUserResponse $response;
   private EmailService $email;
   private CreateUserCache $cache;
   private CreateUserRepository $repository;
   private CreateUserService $service;
   private array $env;

   public function __construct(mysqli $db, Redis $redis, array $env)
   {
      $this->response = new CreateUserResponse();
      $this->email = new EmailService($env);
      $this->cache = new CreateUserCache($redis);
      $this->repository = new CreateUserRepository($db, $this->cache);
      $this->service = new CreateUserService($this->repository, $this->email);
      $this->env = $env;
   }


   public function createAcount(): void
   {

      try {
         $this->request = new CreateUserRequest();
         $traceId = bin2hex(random_bytes(16));

         $start = microtime(true);
         $headers = $this->request->getHeaders();
         $service = [
            'name' => $headers['X-Client-App'],
            'version' => $headers['X-version-app'] ?? null
         ];

         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->request, 'getHeaders', $service, $duration, $traceId);

         $start = microtime(true);
         $body = $this->request->getBody();
         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->request, 'getBody', $service, $duration, $traceId);

         $start = microtime(true);
         $body = $this->request->authAllInfo($this->env);
         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->request, 'authAllInfo', $service, $duration, $traceId);

         $start = microtime(true);
         $this->service->insertAcount($body['NameUser'], $body['EmailUser'], $body['CpfUser'], $body['TelefoneUser'] ?? null, $body['GoogleUid'] ?? null, $body['Password']);
         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->service, 'insertAcount', $service, $duration, $traceId);

         $start = microtime(true);
         $this->response->createAcount();
         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->response, 'createAcount', $service, $duration, $traceId);
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


   public function generateCode(): void
   {

      try {

         $this->request = new CreateUserRequest();
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
         $this->response->addCode($body['EmailUser']);
         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->response, 'addCode', $service, $duration, $traceId);
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

   public function authCode(): void
   {
      try {

         $this->request = new CreateUserRequest();
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
         $this->request->authCode($this->env);
         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->request, 'authCode', $service, $duration, $traceId);

         $start = microtime(true);
         $this->service->insertCode($body['EmailUser'], $body['EmailCode']);
         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->service, 'insertCode', $service, $duration, $traceId);

         $start = microtime(true);
         $this->response->insertCode($body['EmailUser']);
         $duration = (int)((microtime(true) - $start) * 1000);
         $this->logSucess($this->response, 'insertCode', $service, $duration, $traceId);
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
