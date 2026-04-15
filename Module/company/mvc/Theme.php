<?php

namespace Company\MVC;

abstract class Theme {

    static protected $themes = [];

    abstract function getName();

    /**
     * 
     * @param type $name
     * @param \Company\MVC\Theme $theme
     */
    static function registerTheme(Theme $theme) {
        static::$themes[$theme->getName()] = $theme;
    }

    /**
     * 
     * @param type $name
     * @return Theme
     */
    static function getTheme($name) {
        return static::$themes[$name];
    }

    /**
     * Lấy thư mục chứa theme
     */
    function getThemeDir() {
        $class = explode('/', str_replace("\\", '/', get_class($this)));
        //composer có thư mục vendor và component không viết hoa
        $dir = BASE_DIR . '/Module/' . strtolower($class[0] . '/' . $class[1]);
        return $dir;
    }

}
