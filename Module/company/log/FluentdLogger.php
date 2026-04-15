<?php

namespace Company\Log;

use Monolog\Logger;

class FluentdLogger
{
    public static function makeInstance($channel, $handler): Logger
    {
        $logger = new Logger($channel);
        $logger->pushHandler($handler);
        return $logger;
    }
}