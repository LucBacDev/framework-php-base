<?php

namespace CompanyUI\Home;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$homeCtrl = "CompanyUI\\Home\\HomeCtrl";
//đăng ký url mới
Router::getInstance()
    ->addRoute(new MvcContext('/home', 'GET', $homeCtrl, 'home', new RouterFilter("", false)));


