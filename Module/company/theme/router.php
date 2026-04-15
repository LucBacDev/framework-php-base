<?php

namespace Company\Theme;

//sử dụng class của namespace khác phải dùng "use"
use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

Router::getInstance()
    ->addRoute(new MvcContext('/modules/:vendor/:module/public/babelLoader', 'GET', "Company\\Theme\\ThemeCtrl", 'babelLoader', new RouterFilter("modules")));
Router::getInstance()
    ->addRoute(new MvcContext('/modules/:vendor/:module/public/:filepath+', 'GET', "Company\\Theme\\ThemeCtrl", 'moduleContent', new RouterFilter("modules")));
Router::getInstance()
    ->addRoute(new MvcContext('/modules/:vendor/:module/langs/:lang', 'GET', "Company\\Theme\\ThemeCtrl", 'lang', new RouterFilter("modules")));
