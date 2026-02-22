<?php
require_once __DIR__ . '/base-repository.php';
class UserRepository extends BaseRepository
{

    private UserCache $cache;

    public function __construct(mysqli $db, UserCache $cache)
    {
        parent::__construct($db);
        $this->cache = $cache;
    }

    public function getInfos(string $idRequest): array
    {
        $user = $this->cache->getInfos($idRequest);

        if (empty($user)) {
            $user = $this->setInfos($idRequest);
            $this->cache->setInfos($user);

            if (!empty($user['foto_perfil'])) {
                $user['foto_perfil'] = 'https://api.sophia-me13.site/src/repository/fotos/user/' . $user['foto_perfil'];
            }

            return $user;
        }

        if (!empty($user['foto_perfil'])) {
            $user['foto_perfil'] = 'https://api.sophia-me13.site/src/repository/fotos/user/' . $user['foto_perfil'];
        }
        return $user;
    }

    public function addDevice(string $idDevice, string $deviceIP, string $adress, string $userAgent): void
    {
        $deviceCache = $this->cache->getDevices($idDevice);

        if (!empty($deviceCache)) {
            throw new ApiException("Dispositivo já registrado.", 400);
        }

        $device = $this->getDevice($idDevice);
        if (!empty($device)) {
            $this->cache->setDevices($device);
            throw new ApiException("Dispositivo já registrado.", 400);
        }

        
        $this->setDevices($idDevice, $deviceIP, $adress, $userAgent);
    }

    public function setDevices(string $idDevice, string $deviceIP, string $adress, string $userAgent): void
    {
        $stmt = $this->db->prepare("INSERT INTO dispositivos (device_uuid, ip, endereco_proprio, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $idDevice, $deviceIP, $adress, $userAgent);
        $start = microtime(true);
        $stmt->execute();
        $duration = microtime(true) - $start;

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $stmt->affected_rows,
            action: 'INSERT',
            entidade: 'dispositivos',
            entidadeId: (int)$stmt->insert_id ?: null,
                before: null,
                after: [
                    'device_uuid' => $idDevice,
                    'ip' => $deviceIP,
                    'endereco_proprio' => $adress,
                    'user_agent' => $userAgent
                ]
        );
    }
    
    public function getDevice(string $idDevice): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM dispositivos WHERE device_uuid = ? LIMIT 1");
        $stmt->bind_param("s", $idDevice);
        $start = microtime(true);
        $stmt->execute();
        $result = $stmt->get_result();
        $duration = microtime(true) - $start;

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $result->num_rows,
            action: 'SELECT',
            entidade: 'dispositivos'
        );

        if ($device = $result->fetch_assoc()) {
            return $device;
        }

        return null;
    }

    public function checkDevicesAndSetCache(string $idRequest): void
    {
        $user = $this->getInfos($idRequest);
        $idUser = $user['id'];

        $this->checkDevices($idUser);
       
    }

    public function setInfos(string $idRequest): array
    {
        $stmt = $this->db->prepare("
            SELECT id, uuid_request, nome, email, telefone, cpf, foto_perfil
            FROM usuarios
            WHERE uuid_request = ? AND status_id != 3
            LIMIT 1
            ");

        $stmt->bind_param("s", $idRequest);

        $start = microtime(true);
        $stmt->execute();
        $result = $stmt->get_result();
        $duration = microtime(true) - $start;

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $result->num_rows,
            action: 'SELECT',
            entidade: 'usuarios',
            entidadeId: isset($user['id']) ? (int)$user['id'] : null
        );

        if ($user = $result->fetch_assoc()) {
            return $user;
        }

        throw new ApiException("Usuário não encontrado", 404);
    }


    public function checkDevices(string $idUser): void
    {
        $stmt = $this->db->prepare("SELECT 
                    CASE 
                        WHEN a.id IS NOT NULL
                        AND COUNT(du.id) < p.max_dispositivos 
                    THEN 1
                    ELSE 0
                    END AS pode_conectar
                    FROM usuarios u
                        LEFT JOIN assinaturas a 
                        ON a.usuario_id = u.id
                    AND a.status_assinatura_id = 1
                        LEFT JOIN planos p 
                        ON p.id = a.plano_id
                        LEFT JOIN dispositivos_usuarios du 
                        ON du.usuario_id = u.id
                    AND du.status_dispositivo_id = 1
                    WHERE u.id = ?
                    GROUP BY u.id, p.max_dispositivos, a.id;");

        $stmt->bind_param("i", $idUser);
        $start = microtime(true);
        $stmt->execute();
        $result = $stmt->get_result();
        $duration = microtime(true) - $start;

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $result->num_rows,
            action: 'SELECT',
            entidade: 'usuarios, assinaturas, dispositivos_usuarios'
        );

        if ($row = $result->fetch_assoc()) {
            if ($row['pode_conectar'] == 0) {
                throw new ApiException("Limite de dispositivos atingido para este usuário", 403);
            }

          

        } else {
            throw new ApiException("Usuário não encontrado ou sem assinatura ativa", 404);
        }
    }


    
    public function addPlan(int $idRequest, int $idPlano): void
    {
        $user = $this->getInfos($idRequest);
        $idUser = $user['id'];
        
        $stmt = $this->db->prepare("UPDATE assinaturas
        SET plano_id = 2
        WHERE usuario_id = ?
          AND status_assinatura_id = 1
          AND plano_id = 1
          AND ? = 2");
        $stmt->bind_param("ii", $idUser, $idPlano);
        $start = microtime(true);
        $stmt->execute();
        $duration = microtime(true) - $start;

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $stmt->affected_rows,
            action: 'INSERT',
            entidade: 'assinaturas',
            entidadeId: (int)$stmt->insert_id ?: null
        );

        $stmt = $this->db->prepare("
        INSERT INTO assinaturas (
            usuario_id,
            plano_id,
            status_assinatura_id,
            inicio,
            fim
        )
        SELECT
            ?,
            ?,
            CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM assinaturas
                    WHERE usuario_id = ?
                      AND status_assinatura_id = 1
                )
                THEN 2
                ELSE 1
            END,
            COALESCE(
                (
                    SELECT GREATEST(MAX(fim), NOW())
                    FROM assinaturas
                    WHERE usuario_id = ?
                      AND status_assinatura_id <> 3
                ),
                NOW()
            ),
            DATE_ADD(
                COALESCE(
                    (
                        SELECT GREATEST(MAX(fim), NOW())
                        FROM assinaturas
                        WHERE usuario_id = ?
                          AND status_assinatura_id <> 3
                    ),
                    NOW()
                ),
                INTERVAL 1 MONTH
            )
    ");
    
        $stmt->bind_param("iiiii", $idUser, $idPlano, $idUser, $idUser, $idUser);
        $start = microtime(true);
        $stmt->execute();
        $duration = microtime(true) - $start;

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $stmt->affected_rows,
            action: 'INSERT',
            entidade: 'assinaturas',
            entidadeId: (int)$stmt->insert_id ?: null,
                before: null,
                after: [
                    'usuario_id' => $idUser,
                    'plano_id' => $idPlano,
                    'status_assinatura_id' => (int)(($stmt->affected_rows > 0) ? 1 : 2),
                    'inicio' => date('Y-m-d H:i:s'),
                    'fim' => date('Y-m-d H:i:s', strtotime('+1 month'))
                ]
        );


    }

    public function getSetDevices(string $idDevice, ?string $nameDevice, ?string $typeDevice, ?string $ipDevice, ?string $localDevice): int
    {
        $stmt = $this->db->prepare("SELECT * FROM dispositivos WHERE device_uuid = ? LIMIT 1");
        $stmt->bind_param("s", $idDevice);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: 0,
            rows: $result->num_rows,
            action: 'SELECT',
            entidade: 'dispositivos'
        );
        
        if (!$row) {
            $stmt = $this->db->prepare("INSERT INTO dispositivos (device_uuid, nome_dispositivo, user_agent, ip, endereco_proprio) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $idDevice, $nameDevice, $typeDevice, $ipDevice, $localDevice);
            $start = microtime(true);
            $stmt->execute();
            $duration = microtime(true) - $start;

            $this->logRepository(
                endpoint: __DIR__,
                metodo: __METHOD__,
                duration: $duration,
                rows: $stmt->affected_rows,
                action: 'INSERT',
                entidade: 'dispositivos',
                entidadeId: (int)$stmt->insert_id ?: null,
                    before: null,
                    after: [
                        'device_uuid' => $idDevice,
                        'nome_dispositivo' => $nameDevice,
                        'user_agent' => $typeDevice,
                        'ip' => $ipDevice,
                        'endereco_proprio' => $localDevice
                    ]
            );

            return (int)$stmt->insert_id;
        }
        return $row['id'];
    }

    
    public function addDeviceUser(string $idRequest, array $deviceInfo): void
    {
        $user = $this->getInfos($idRequest);
        $idUser = $user['id'];
        $deviceId = $this->getSetDevices(
            $deviceInfo['device_uuid'] ,
            $deviceInfo['nome_dispositivo'] ,
            $deviceInfo['user_agent'] ,
            $deviceInfo['ip'] ,
            $deviceInfo['endereco_proprio']
        );

        $stmt = $this->db->prepare("INSERT INTO dispositivos_usuarios (usuario_id, dispositivo_id, status_dispositivo_id) VALUES (?, ?, 1)");
        $stmt->bind_param("is", $idUser, $deviceId);
        $start = microtime(true);
        $stmt->execute();
        $duration = microtime(true) - $start;

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: $duration,
            rows: $stmt->affected_rows,
            action: 'INSERT',
            entidade: 'dispositivos_usuarios',
            entidadeId: (int)$stmt->insert_id ?: null,
                before: null,
                after: [
                    'usuario_id' => $idUser,
                    'dispositivo_id' => $deviceId,
                    'status_dispositivo_id' => 1
                ]
        );
    }

    public function getDeviceUser(int $idUser, int $idDevice): array
    {
        
        
        $stmt = $this->db->prepare("SELECT * FROM dispositivos_usuarios WHERE usuario_id = ? AND dispositivo_id = ?");
        $stmt->bind_param("ii", $idUser, $idDevice);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        $this->logRepository(
            endpoint: __DIR__,
            metodo: __METHOD__,
            duration: 0,
            rows: $result->num_rows,
            action: 'SELECT',
            entidade: 'dispositivos_usuarios'
        );
        
        return $row;
    }
}