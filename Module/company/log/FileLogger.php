<?php
namespace Company\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Company\Log\Logger as VrLogger;

class FileLogger extends Logger{
    static $configKey = "fileLogger";

    /**
     * @throws \Exception
     */
    static function makeInstance($channel)
    {
        $path = VrLogger::$config[self::$configKey]['path'];
        $stream = new StreamHandler($path, VrLogger::$config[self::$configKey]['level']);
        $logger = new FileLogger($channel);
        $logger->pushHandler($stream);
        return $logger;
    }

    public function __construct($channel, array $handlers = array(), array $processors = array())
    {
        parent::__construct($channel, $handlers, $processors);
    }

    private function rotate()
    {
        $logname = VrLogger::$config[self::$configKey]['path'];
        if (file_exists($logname) && filesize($logname) > VrLogger::$config[self::$configKey]['maxSize']) {
            $path_parts = pathinfo($logname);
            $pattern = $path_parts['dirname']. '/'. $path_parts['filename']. "-%d.". $path_parts['extension'];

            // delete last log
            $fn = sprintf($pattern, VrLogger::$config[self::$configKey]['maxFiles']);
            if (file_exists($fn))
                unlink($fn);

            // shift file names (add '-%index' before the extension)
            for ($i = VrLogger::$config[self::$configKey]['maxFiles']-1; $i > 0; $i--) {
                $fn = sprintf($pattern, $i);
                if(file_exists($fn))
                    rename($fn, sprintf($pattern, $i+1));
            }
            rename($logname, sprintf($pattern, 1));
        }
    }

    public function info($message, array $context = array()): bool
    {
        $this->rotate();
        $result = parent::info($message, $context);
        $this->close();
        return $result;
    }

    public function warning($message, array $context = array()): bool
    {
        $this->rotate();
        return parent::warning($message, $context); 
    }

    public function debug($message, array $context = array()): bool
    {
        $this->rotate();
        return parent::debug($message, $context); 
    }

    public function error($message, array $context = array()): bool
    {
        $this->rotate();
        return parent::error($message, $context); 
    }
}