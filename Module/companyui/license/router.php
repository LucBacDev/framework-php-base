<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\License\\LicenseCtrl";

//đăng ký url mới
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/license', 'GET', $ctrl, 'licenseList', new RouterFilter("", false)));


