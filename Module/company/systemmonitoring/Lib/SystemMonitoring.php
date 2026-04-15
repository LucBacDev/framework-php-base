<?php

namespace Company\SystemMonitoring\Lib;

use Company\Cache\CacheDriver;
use Company\Cache\Redis;

class SystemMonitoring
{
    /**
     * @param $key string
     * @param $status bool
     * @param $expireTime int
     * @return void
     * @throws \Exception
     */
    static function setStatus($key, $status = true, $expireTime = 30) {
        $cacheInstance = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);

        // chi ho tro system monitoring voi redis
        if ($cacheInstance instanceof Redis) {
            $systemMonitoringKey = self::createSystemMonitoringKey($key);
            $curVal = $cacheInstance->get($systemMonitoringKey);
            if ($status) {
                if ($curVal === false) {
                    $cacheInstance->set($systemMonitoringKey, 0, $expireTime);
                }
            } else {
                if ($curVal < 10)
                    $cacheInstance->incr($systemMonitoringKey);
                $cacheInstance->expire($systemMonitoringKey, $expireTime);
            }
        }
    }

    /**
     * @param $key string
     * @return bool|int
     * @throws \Exception
     */
    static function getStatus($key) {
        $cache = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
        $status = $cache->get(self::createSystemMonitoringKey($key));
        return is_bool($status) ? $status : (int) $status;
    }

    /**
     * @param $serviceKey string
     * @return string
     */
    static function createSystemMonitoringKey($serviceKey) {
        return "systemmonitoring/$serviceKey";
    }
}