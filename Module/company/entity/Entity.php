<?php

namespace Company\Entity;

class Entity {

    //tránh lỗi notice khi gọi biến chưa khai báo
    function __get($name) {
        return null;
    }

    function __construct($rawData = null) {
        if (is_array($rawData)) {
            foreach ($rawData as $k => $v) {
                $this->{$k} = $v;
            }
        }
        $this->decodeAttrs();
    }

    protected function decodeAttrs() {
        if (!isset($this->attrs) || !is_string($this->attrs)) {
            return;
        }
        $attrs = json_decode($this->attrs, true);
        if (!$attrs) {
            return;
        }
        foreach ($attrs as $k => $v) {
            $this->{$k} = $v;
        }
        unset($this->attrs);
    }

    function toJson() {
        return json_encode($this);
    }

}
