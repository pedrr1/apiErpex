<?php
require_once __DIR__ . '/../request-base.php';

class UserRequest extends BaseRequest
{
    public function authUser(array $env): void
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();
        $idRequest = $this->rawBody['IdRequest'] ?? '';

        if (empty($idRequest)) {
            throw new ApiException("Identificador Invalido", 400);
        }
    }

    public function authDevice(array $env): void
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();
        $deviceInfo = $this->rawBody['IdDevice'] ?? '';

        if (empty($deviceInfo)) {
            throw new ApiException("Informações do dispositivo são obrigatórias", 400);
        }
    }

    public function authPix(array $env): void
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();
        $plano = $this->rawBody['PixId'] ?? null;

        if (is_null($plano)) {
            throw new ApiException("PixId Invalido", 400);
        }
    }

    public function authAllInfo(array $env): array
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();

        $requiredFields = [
            'NameUser'  => 'Nome inválido',
            'CpfUser'   => 'CPF inválido',
        ];

        foreach ($requiredFields as $field => $errorMessage) {
            if (empty($this->rawBody[$field])) {
                throw new ApiException($errorMessage, 400);
            }
        }

        return $this->rawBody;
    }

    public function authPlan(array $env): void
    {
        $this->authHandleToken($env);
        $this->authHandleApplication();
        $plano = $this->rawBody['Plano'] ?? null;

        if (is_null($plano)) {
            throw new ApiException("Plano Invalido", 400);
        }

    }
}
?>