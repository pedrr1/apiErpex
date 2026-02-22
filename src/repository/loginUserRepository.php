<?php
require_once __DIR__ . '/base-repository.php';
class LoginUserRepository extends BaseRepository
{
    
    private LoginUserCache $cache;
     
    public function __construct(mysqli $db, LoginUserCache $cache) {
        parent::__construct($db);
        $this->cache = $cache;
    }
   
    
    private function loginUserSQL(string $login): array
    {
        $stmt = $this->db->prepare("
        SELECT senha_hash, uuid_request
        FROM usuarios
        WHERE (email = ? OR nome = ? OR google_uid = ?) AND status_id != 3
        LIMIT 1
        ");

        $stmt->bind_param("sss", $login, $login, $login);
        $start = microtime(true);

        $stmt->execute();
        $result = $stmt->get_result();
        $duration = (int)((microtime(true) - $start) * 1000);

        if ($user = $result->fetch_assoc()) {
            $this->logRepository(
                endpoint: __DIR__,
                metodo: __METHOD__,
                duration: $duration,
                rows: $result->num_rows,
                action: 'SELECT',
                entidade: 'usuarios',
                entidadeId: isset($user['id']) ? (int)$user['id'] : null
            );
        }

        if (!$user) {
            throw new ApiException("Usuário não encontrado", 404);
        }
      
        return $user;
    }

    public function getUser(string $login):array{
        $user = $this->cache->loginCacheName($login);
        
        if (empty($user)){
            $user = $this->loginUserSQL($login);
            $this->cache->cachearUsuario($user);

            return $user;
        }
        
        return $user;   
       }
    
    public function setCodeEmail(string $code, string $email):void{
        $this->cache->setCodeEmail($code, $email);
    }
}