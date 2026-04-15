<?php

namespace Company\Log;

use Company\Log\Logger as VrLogger;
use Pacs\Log\AuditLogHandler;
use Pacs\Log\PacsFluentdHandler;

class Logger {
    const FILE_LOGGER = 1;
    const ELASTIC_LOGGER = 2;
    const PACS_FLUENTD_LOGGER = 3;
    const AUDIT_FLUENTD_LOGGER = 4;

    public static $config;

    private static $loggers = [];

    public static function setConfig($conf)
    {
        static::$config = $conf;
    }

    public static function makeLogger($loggerType, $channel)
    {
        if (array_key_exists($loggerType, self::$loggers)) {
            $loggersList = self::$loggers[$loggerType];
            if (!array_key_exists($channel, $loggersList))
                self::$loggers[$loggerType][$channel] = array_values($loggersList)[0]->withName($channel);

            return self::$loggers[$loggerType][$channel];
        }

        switch ($loggerType) {
            case static::FILE_LOGGER:
                $logger = FileLogger::makeInstance($channel);
                break;
            case static::ELASTIC_LOGGER:
                $logger = ElasticLogger::makeInstance($channel);
                break;
            case static::PACS_FLUENTD_LOGGER:
                $config = VrLogger::$config["fluentdLogger"];
                $handler = new PacsFluentdHandler($config['host'], $config['port'], $config['level']);
                $logger = FluentdLogger::makeInstance($channel, $handler);
                break;
            case static::AUDIT_FLUENTD_LOGGER:
                $config = VrLogger::$config["fluentdLogger"];
                $handler = new AuditLogHandler($config['host'], $config['port'], $config['level']);
                $logger = FluentdLogger::makeInstance($channel, $handler);
                break;
            default:
                $logger = null;
        }

        self::$loggers[$loggerType][$channel] = $logger;
        return $logger;
    }
}