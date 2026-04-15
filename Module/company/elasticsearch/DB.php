<?php

namespace Company\ElasticSearch;

use Elasticsearch\ClientBuilder;

class DB
{

    static protected $conns = [];
    static protected $settings = [];
    static protected $config = [];

    /**
     * Config cấu hình kết nối
     * @param array $config https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html
     * @param type $connName tên config
     */
    static function config($config, $connName = 'default')
    {
        static::$config[$connName] = $config;
    }

    static function getInstance($name = 'default')
    {
        if (is_null(static::$config[$name]))
            throw new \Exception("Config not found: name=" . $name);

        if (!isset(static::$conns[$name]))
            static::$conns[$name] = ClientBuilder::fromConfig(static::$config[$name]);

        if (!isset(static::$conns[$name])) {
            throw new \Exception("No connection defined");
        }

        return static::$conns[$name];
    }

    // settings when create index
    static function setSettings($settings)
    {
        self::$settings = $settings;
    }

    static function getSettings()
    {
        return self::$settings;
    }
}
