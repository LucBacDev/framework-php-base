<?php

namespace CompanyUI\Task;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Task\\TaskUiCtrl";

Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/task', 'GET', $ctrl, 'taskBoard', new RouterFilter("", false)));
