<?php

namespace CompanyUI\Employee;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Employee\\EmployeeUiCtrl";

Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/nhansu', 'GET', $ctrl, 'employeeList', new RouterFilter("", false)));
