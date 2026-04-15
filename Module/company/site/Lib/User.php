<?php

namespace Company\User;

class User extends Macroable {

    protected static $instance;

    static function setInstance(User $instance) {
        static::$instance = $instance;
    }

    /**
     * 
     * @return User
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
