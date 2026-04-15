<?php

namespace Company\Kafka;

class KafkaClient
{
    static protected $config;
    static protected $replicationFactor;
    function __construct()
    {
    }

    static function config($config)
    {
        static::$config = $config;
    }

    static function getConfig(){
        return static::$config;
    }

    static function setReplicationFactor($replicationFactor){
        static::$replicationFactor = $replicationFactor;
    }

    static function getReplicationFactor(){
        return static::$replicationFactor;
    }
}
