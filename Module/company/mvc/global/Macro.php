<?php

class Macroable implements MacroInterface {

    /**
     *
     * @var callable 
     */
    protected static $macros = [];

    public static function macro($functionName, $callback) {
        static::$macros[$functionName] = $callback;
    }

    public function __call($method, $parameters) {
        if (!static::hasMacro($method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }
        $macro = static::$macros[$method];
        if ($macro instanceof Closure) {
            return call_user_func_array($macro->bindTo($this, static::class), $parameters);
        }
        return call_user_func_array($macro, $parameters);
    }
    
    static function hasMacro($method){
        return isset(static::$macros[$method]);  
    }
}
