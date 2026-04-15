<?php

namespace Company\Auth;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$router = R::getInstance();
$ctrl = "\\Company\\Auth\\AuthCtrl";
$router->addRoute(new MVC("/rest/auth", 'POST', $ctrl, "auth", new RouterFilter("auth")));
$router->addRoute(new MVC("/rest/auth", 'GET', $ctrl, "renewSession", new RouterFilter("auth")));
$router->addRoute(new MVC("/rest/auth", 'DELETE', $ctrl, "logout", new RouterFilter("auth")));


//test
$testCtrl = "\\Company\\Auth\\TestCtrl";
$router->addRoute(new MVC("/rest/auth/test", 'GET', $testCtrl, "test", new RouterFilter("auth")));
$router->addRoute(new MVC("/rest/auth/test2", 'GET', $testCtrl, "test2", new RouterFilter("auth")));

