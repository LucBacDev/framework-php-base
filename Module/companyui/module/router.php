<?php

namespace CompanyUI\Module;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Module\\ModuleCtrl";
//đăng ký url mới
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/modules', 'GET', $ctrl, 'moduleList', new RouterFilter("", false)));


