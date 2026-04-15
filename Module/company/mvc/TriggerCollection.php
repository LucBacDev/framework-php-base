<?php

namespace Company\MVC;

class TriggerCollection implements \Iterator {

    protected $var = [];

    function __construct($var) {
        $this->var = $var;
    }

    public function current() {
        return current($this->var);
    }

    public function key() {
        return key($this->var);
    }

    public function next() {
        return next($this->var);
    }

    public function rewind() {
        return rewind($this->var);
    }

    public function valid() {
        return isset($this->var[$this->key()]);
    }

    function reverse() {
        array_reverse($this->var);
        return $this;
    }

    /**
     * Execute current cursor, return result or false if no callback available
     * @param type $args
     * @return boolean
     * @throws \BadMethodCallException
     */
    function execute() {
        $args = func_get_args();
        $callback = $this->current();
        if ($callback) {
            return call_user_func_array($callback, $args);
        }
        return false;
    }

}
