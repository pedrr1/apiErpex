<?php
require_once __DIR__ . '/base-cache.php';
class UserCache extends BaseCache
{
    public function getInfos(string $id): ?array
    {
        $keyuser = "user:$id";
        
        $start = microtime(true);
        if ($this->redis->exists($keyuser)) {
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logCache(__DIR__, __METHOD__, $duration);
            
            return json_decode($this->redis->get($keyuser), true);
        }
        
        return null;
    }

    public function setInfos(array $user): void
    {
        $keyuser = "user:" . $user['uuid_request'];
        
        $start = microtime(true);
        $this->redis->setex($keyuser, 600, json_encode($user));
        $duration = (int)((microtime(true) - $start) * 1000);
        $this->logCache(__DIR__, __METHOD__, $duration);
    }

    public function getDevices(string $idDevice): ?array
    {
        $keyDevice = "device:$idDevice";
        
        $start = microtime(true);
        if ($this->redis->exists($keyDevice)) {
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logCache(__DIR__, __METHOD__, $duration);
            
            return json_decode($this->redis->get($keyDevice), true);
        }
        
        return null;
    }

        public function setDevices(array $deviceData): void
        {
            $keyDevice = "device:$deviceData[device_uuid]";
            
            $start = microtime(true);
            $this->redis->setex($keyDevice, 600, json_encode($deviceData));
            $duration = (int)((microtime(true) - $start) * 1000);
            $this->logCache(__DIR__, __METHOD__, $duration);


        }
    }
?>