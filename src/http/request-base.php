<?php
require_once __DIR__ . '/../exceptions/apiException.php';

class BaseRequest
{
    protected array $headers;
    protected array $rawBody;

    public function __construct()
    {
        $this->headers = getallheaders() ?: [];

        // Se tiver upload (multipart/form-data)
        if (!empty($_FILES) && isset($_POST['UrlFotoUser'])) {
            $this->rawBody = $_POST;

            foreach ($_FILES as $key => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $this->rawBody[$key] = $file;
                }
            }

            return;
        }

        // JSON normal
        $raw = file_get_contents("php://input");

        if ($raw === '' || $raw === false) {
            throw new ApiException("Body vazio", 400);
        }

        $this->rawBody = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($this->rawBody)) {
            throw new ApiException("JSON inválido", 400);
        }
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): array
    {
        return $this->rawBody;
    }

    protected function authHandleToken(array $env): void
    {
        if (!isset($this->headers['Authorization'])) {
            throw new ApiException("Token não informado", 401);
        }

        $token = trim($this->headers['Authorization']);

        if (
            $token !== $env['API_TOKEN_DESKTOP'] &&
            $token !== $env['API_TOKEN_WEB']
        ) {
            throw new ApiException("Token inválido", 401);
        }
    }

    protected function authHandleApplication(): void
    {
        if (!isset($this->headers['X-Client-App'])) {
            throw new ApiException("Aplicação não autorizada", 401);
        }
    }
}
