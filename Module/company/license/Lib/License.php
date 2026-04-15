<?php

namespace Company\License;

class License extends Macroable {

    protected static $instance;

    static function setInstance(User $instance) {
        static::$instance = $instance;
    }

    /**
     * 
     * @return license
     */
    static function getInstance() {
        if (!static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    function __construct() {
        ;
    }

}
