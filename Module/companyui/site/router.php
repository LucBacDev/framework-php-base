<?php

namespace CompanyUI\Site;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Site\\SiteCtrl";
//đăng ký url mới
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/sites', 'GET', $ctrl, 'siteList', new RouterFilter("", false)));

// lấy danh sách các site được phân quyền
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/users/sites', 'GET', $ctrl, 'userSiteList', new RouterFilter("", false)));

