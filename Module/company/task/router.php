<?php

namespace Company\Task;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$ctrl = "\\Company\\Task\\Controller\\TaskCtrl";

R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks', 'POST', $ctrl, 'createTask', new RouterFilter("/rest/task"))
);
