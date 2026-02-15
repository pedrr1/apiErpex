<?php
require_once __DIR__ . '/base-cache.php';
class CreateUserCache extends BaseCache{


        public function setEmailCode(string $email, string $code):void{
        $redisKey = 'verify:email:cad:' . strtolower($email);
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

    public function compareEmailCode(string $email):string{
        $redisKey = 'verify:email:cad:' . strtolower($email);
        $start = microtime(true);
        $savedCode = $this->redis->get($redisKey);
        $duration = (int)((microtime(true) - $start) * 1000);

        $this->logCache(__DIR__, __METHOD__, $duration);
        if (!$savedCode) {
        throw new ApiException(
                'Código não exite ou Expirou',
                429
            ); // código não existe ou expirou
        }

        return $savedCode;
    }

    public function delEmailCode(string $email):void{
        $redisKey = 'verify:email:cad:' . strtolower($email);
        $start = microtime(true);
        $this->redis->del($redisKey);
        $duration = (int)((microtime(true) - $start) * 1000);

        $this->logCache(__DIR__, __METHOD__, $duration);
    }
}
?>
