<?php
require_once __DIR__ . '/base-cache.php';
class loginUserCache extends BaseCache
{

    public function loginCacheName(string $login): ?array
    {
        $login = strtolower(trim($login));

        $keyEmail = "user:email:$login";
        $keyNome  = "user:nome:$login";
        $keyidGoogle = "user:idGoogle:$login";

        if ($this->redis->exists($keyEmail)) {
            return json_decode($this->redis->get($keyEmail), true);
        }

        if ($this->redis->exists($keyNome)) {
            return json_decode($this->redis->get($keyNome), true);
        }

        if ($this->redis->exists($keyidGoogle)) {
            return json_decode($this->redis->get($keyidGoogle), true);
        }

        return null;
    }

    public function cachearUsuario( array $user): void
    {

        if (!empty($user['email'])) {
            $keyEmail = "user:email:" . strtolower(trim($user['email']));
            $this->redis->setex($keyEmail, 600, json_encode($user));
        }

        if (!empty($user['nome'])) {
            $keyNome = "user:nome:" . strtolower(trim($user['nome']));
            $this->redis->setex($keyNome, 600, json_encode($user));
        }
        if (!empty($user['google_uid'])) {
            $keyidGoogle = "user:idGoogle:" . strtolower(trim($user['google_uid']));
            $this->redis->setex($keyidGoogle, 600, json_encode($user));
        }
    }

    public function setCodeEmail (string $code, string $email): void{
         $redisKey = 'verify:email:recover:' . strtolower($email);
        $start = microtime(true);
            $created = $this->redis->set(
            $redisKey,
            $code,
            ['nx', 'ex' => 300]
        );
        $duration = (int)((microtime(true) - $start) * 1000);

        $this->logCache(__DIR__, __METHOD__, $duration);


        if ($created === false) {
            throw new ApiException(
                'Código já enviado.',
                429
            );
        }
    }
}
