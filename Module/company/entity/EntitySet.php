<?php

namespace Company\Entity;

class EntitySet implements \Iterator {

    public $var = array();

    public function __construct($array) {
        $this->var = $array;
    }

    public function rewind() {
        reset($this->var);
    }

    public function current() {
        $var = current($this->var);
        return $var;
    }

    public function key() {
        $var = key($this->var);
        return $var;
    }

    public function next() {
        $var = next($this->var);
        return $var;
    }

    function end() {
        return end($this->var);
    }

    function count() {
        return count($this->var);
    }

    public function valid() {
        $key = key($this->var);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    function toArray() {
        return $this->var;
    }

}
