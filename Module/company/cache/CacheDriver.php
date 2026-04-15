<?php


namespace Company\Cache;

use Exception;

abstract class CacheDriver
{
    const PRIVATE_CACHE = "private_cache";
    const SHARE_CACHE = "share_cache";
    const PRIVATE_MEMORY_CACHE = "private_memory_cache";

    static protected $instances=[];

    static function setInstance($type, $instance){
        static::$instances[$type] = $instance;
    }

    /**
     * @param $type
     * @return PhpFileCache|Redis|APCU
     */
    static function getInstance($type) {
        if(!isset(static::$instances[$type]))
            throw new \Exception("cache type not setInstance yet: $type");
        
        return static::$instances[$type];
    }
}