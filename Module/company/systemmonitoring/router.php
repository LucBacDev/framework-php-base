<?php

namespace Company\SystemMonitoring;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;
use Company\SystemMonitoring\Controller\SystemMonitoringCtrl;

R::getInstance()->addRoute(new MVC('/master/rest/system', 'GET', SystemMonitoringCtrl::class, "getSystemStatus", new RouterFilter("rest/system")));


