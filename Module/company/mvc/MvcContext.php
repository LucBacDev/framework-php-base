<?php

namespace Company\MVC;

class MvcContext
{

    public $config;

    /** @var Bootstrap */
    public $app;
    public $path;
    public $method;
    public $controller;
    public $action;
    public $rewriteBase;
    public $id;
    public $cacheLifetime = false;
    public RouterFilter $filter;

    /**
     * @param $path
     * @param $method
     * @param $controller
     * @param $action
     * @param RouterFilter|null $filter
     */
    function __construct($path, $method, $controller, $action, RouterFilter $filter = null)
    {
        $this->path = $path;
        $this->method = $method;
        if ($controller[0] !== "\\")
            $controller = "\\" . $controller;
        $this->controller = $controller;
        $this->action = $action;
        $this->filter = is_null($filter) ? new RouterFilter() : $filter;
    }

    function getId($withMethod = true)
    {
        return $withMethod ? "{$this->method}:$this->path" : "$this->path";
    }

}
