<?php

interface MacroInterface {
    
    /**
     * Đăng ký method mới cho class
     * @param string $functionName
     * @param callable $callback
     */
    static function macro($functionName, $callback);
}
