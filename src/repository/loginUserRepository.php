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
        SELECT nome, email, senha_hash, cpf, telefone, foto_perfil, uuid_request
        FROM usuarios
        WHERE email = ? OR nome = ?
        LIMIT 1
        ");

        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

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
}
