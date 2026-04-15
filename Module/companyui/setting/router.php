<?php

namespace CompanyUI\Setting;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Setting\\SettingCtrl";
//đăng ký url mới
// setting
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/setting', 'GET', $ctrl, 'SettingList', new RouterFilter("", false)));
// setting integrate
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/settingIntegrate', 'GET', $ctrl, 'SettingIntegrateList', new RouterFilter("", false)));
