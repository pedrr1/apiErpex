<?php
require_once __DIR__ . '/../exceptions/apiException.php';

class UserService
{
    private UserRepository $repository;
    private EmailService $email;
    private PixController $pix;

    public function __construct(UserRepository $repository, EmailService $email, PixController $pix)
    {
        $this->repository = $repository;
        $this->email = $email;
        $this->pix = $pix;
    }

    public function getUser(string $idRequest): array
    {
        return $this->repository->getInfos($idRequest);
    }

    public function checkDevices(string $idRequest): void
    {
        $this->repository->checkDevicesAndSetCache($idRequest);
    }

    public function generatePix(int $plano, array $env): array
    {
        if ($plano === 6) {
            return $this->pix->generatePix(5.00, "Plano Base", $env);
        }

        if ($plano === 9) {
            return $this->pix->generatePix(10.00, "Plano Plus", $env);
        }
        throw new ApiException("Plano invalido", 400);
    }


    function getDeviceIP(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        // Se for IPv4 → retorna normal
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ip;
        }

        // Se for IPv6 → pega só os 4 primeiros blocos
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return implode(':', array_slice($parts, 0, 4));
        }

        throw new ApiException("Não foi possível determinar o IP do dispositivo", 400);
    }

    function getIpInfo(): array
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,isp,query";

        $response = file_get_contents($url);
        $data = json_decode($response, true);

        return $data;
    }

    function getDeviceType() {

    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('/windows/i', $userAgent)) {
        return "Windows";
    } 
    elseif (preg_match('/mac/i', $userAgent)) {
        return "MacOS";
    } 
    elseif (preg_match('/android/i', $userAgent)) {
        return "Android";
    } 
    elseif (preg_match('/iphone|ios/i', $userAgent)) {
        return "iOS";
    } 
    else {
        return "Desconhecido";
    }
}

    public function addDevices(string $idDevice): void
    {

            $deviceIP = $this->getDeviceIP();
            $infoIp = $this->getIpInfo();
            $adress = $infoIp['city']. '-'. $infoIp['regionName'] .'-'. $infoIp['country'];
            $userAgent = $this->getDeviceType();
            $this->repository->addDevice($idDevice, $deviceIP, $adress, $userAgent);
            return;

    }

    public function addSignature(string $idRequest, string $idPix, array $env): void
    {
        $pixResult = $this->pix->checkPix($idPix, $env);

        if ($pixResult['status'] === 'approved') {
            if ($pixResult['description'] === "Plano Base") {
                $this->repository->addPlan($idRequest, 1);
                return;
            } elseif ($pixResult['description'] === "Plano Plus") {
                $this->repository->addPlan($idRequest, 2);
                return;
            }
        } elseif ($pixResult['status'] === 'pending' || $pixResult['status'] === 'rejected') {
            throw new ApiException("Pagamento não aprovado", 400);
        }

        throw new ApiException("Pagamento não encontrado", 404);
    }
}
