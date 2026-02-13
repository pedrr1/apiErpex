<?php
require_once __DIR__ . '/base-cache.php';
class loginUserCache extends BaseCache
{

    public function loginCacheName(string $login): ?array
    {
        $login = strtolower(trim($login));

        $keyEmail = "user:email:$login";
        $keyNome  = "user:nome:$login";

        if ($this->redis->exists($keyEmail)) {
            return json_decode($this->redis->get($keyEmail), true);
        }

        if ($this->redis->exists($keyNome)) {
            return json_decode($this->redis->get($keyNome), true);
        }

        return null;
    }

    function cachearUsuario( array $user): void
    {

        if (!empty($user['email'])) {
            $keyEmail = "user:email:" . strtolower(trim($user['email']));
            $this->redis->setex($keyEmail, 600, json_encode($user));
        }

        if (!empty($user['nome'])) {
            $keyNome = "user:nome:" . strtolower(trim($user['nome']));
            $this->redis->setex($keyNome, 600, json_encode($user));
        }
    }
}
