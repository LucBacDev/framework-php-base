<?php

namespace CompanyUI\Department;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Department\\DepartmentUiCtrl";

Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/phongban', 'GET', $ctrl, 'departmentList', new RouterFilter("", false)));
