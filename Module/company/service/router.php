<?php

namespace Company\Service;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

/**
 * User router
 */
$serviceCtrl = "\\Company\\Service\\Controller\\ServiceCtrl";
R::getInstance()->addRoute(new MVC('/master/rest/service(/:serviceID)', 'POST,PUT', $serviceCtrl, "updateService", new RouterFilter("service")));
R::getInstance()->addRoute(new MVC('/master/rest/service(/:serviceID)', 'GET', $serviceCtrl, "getService", new RouterFilter("service")));
R::getInstance()->addRoute(new MVC('/master/rest/service/:serviceID', 'DELETE', $serviceCtrl, "deleteService", new RouterFilter("service")));

R::getInstance()->addRoute(new MVC('/master/rest/processes(/:serviceID)', 'GET', $serviceCtrl, "getProcesses", new RouterFilter("process")));
R::getInstance()->addRoute(new MVC('/master/rest/process/handle', 'GET', $serviceCtrl, "handleProcess", new RouterFilter("process")));
