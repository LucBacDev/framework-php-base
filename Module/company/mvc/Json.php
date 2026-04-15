<?php

namespace Company\MVC;

class Json {

    static function encode($val) {
		//framework, will check later
        $val = json_decode(json_encode($val), true);
        if (is_array($val)) {
            static::walk($val);
        }

        return json_encode($val);
    }

    protected static function walk(&$val) {
        if (is_array($val) && isset($val['var'])) {
            $val = $val['var'];
        }
        foreach ($val as $k => &$v) {
            if (is_array($v)) {
                static::walk($v);
            }
        }
    }

    static function decode($val) {
        return json_decode($val, true);
    }

}
