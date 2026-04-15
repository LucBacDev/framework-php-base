<?php

namespace CompanyUI\SystemMonitoring;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

Router::getInstance()
    ->addRoute(new MvcContext('/master/systemmonitoring', 'GET', SystemMonitoringCtrl::class, 'SystemMonitoring', new RouterFilter("", false)));
