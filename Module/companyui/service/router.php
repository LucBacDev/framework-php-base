<?php

namespace CompanyUI\Service;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Service\\ServiceCtrl";

Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/services', 'GET', $ctrl, 'ServiceList', new RouterFilter("", false)));

Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/services/:id/processes', 'GET', $ctrl, 'ProcessList', new RouterFilter("", false)));
