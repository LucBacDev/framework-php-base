<?php

namespace Company\MVC;

//only declare if class not defined
if (!class_exists('Company\MVC\Router')) {
    class Router extends RouterFilter
    {
    
        protected $routes = [];
        static protected $instance;
    
        /**
         * @return Router
         */
        static function getInstance()
        {
            if (!static::$instance) {
                static::$instance = new static;
            }
    
            return static::$instance;
        }
    
    
        /**
         * Đăng ký route
         * @param \Company\MVC\MvcContext $route
         * @param string $filterUriHasText vd: truyền license thì chỉ các đường dẫn có chữ license mới được thêm
         * @return $this
         */
        function addRoute(MvcContext $route)
        {
            if (php_sapi_name() === 'cli') return $this; //ignore routes on cli
    
    //        //filter wrong method
    //        if ($route->method != '*' && strpos($route->method, $_SERVER['REQUEST_METHOD']) === false) {
    //            return $this;
    //        }
    
            $this->routes[$route->getId()] = $route;
            return $this;
        }
    
        function requestUriHas($str)
        {
            if (php_sapi_name() === 'cli') return false;
    
            if (!strlen($str ?? ""))
                return true;
    
            return strpos($_SERVER['REQUEST_URI'], $str) !== false;
        }
    
        function hasMethod($method)
        {
            if (php_sapi_name() === 'cli') return false;
    
            if ($method === '*')
                return true;
    
            return str_contains($method, $_SERVER['REQUEST_METHOD']);
        }
    
        function requestIsRest($bool)
        {
            if (php_sapi_name() === 'cli') return false;
    
            if (!is_bool($bool))
                return true;
    
            return app()->isRest() === $bool;
        }
    
        function getRoute($name)
        {
            return arrData($this->routes, $name, false);
        }
    
        function getRoutes()
        {
            $config = Bootstrap::getInstance()->config;
            $routers = [];
            if (intval(arrData($config, 'production', 0))) {
                $routers = unserialize(apcu_fetch("Router.getRoutes"));
            }
    
            if (is_array($routers) && !count($routers) || empty($routers)) {
                foreach (scandir(BASE_DIR . '/Module') as $vendor) {
                    if ($vendor[0] == '.')
                        continue; //skip directory
    
                    $vendor = BASE_DIR . '/Module/' . $vendor;
                    if (!is_dir($vendor)) {
                        continue;
                    }
                    foreach (scandir($vendor) as $comp) {
                        if ($comp[0] == '.')
                            continue; //skip directory
                        $dir = $vendor . '/' . $comp;
                        if (!file_exists($dir . '/router.php')) {
                            continue;
                        }
    
                        require $dir . '/router.php';
                    }
                }
    
                $routers = $this->routes;
                apcu_store("Router.getRoutes", serialize($routers), 60);
            }
    
            $ret = [];
            foreach ($routers as $router) {
                if ($router->filter && $this->requestUriHas($router->filter->filterUri) && $this->hasMethod($router->method) && $this->requestIsRest($router->filter->filterIsRest)) {
                    $ret[] = $router;
                }
            }
    
            return $ret;
        }
    }
    
}
