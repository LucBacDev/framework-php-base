<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$userCtrl = "CompanyUI\\User\\UserCtrl";
//đăng ký url mới
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/users', 'GET', $userCtrl, 'userList', new RouterFilter("", false)));
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/roles', 'GET', $userCtrl, 'roleList', new RouterFilter("", false)));


Router::getInstance()
    ->addRoute(new MvcContext('/auth/login', 'GET', $userCtrl, 'login', new RouterFilter("", false)));

Router::getInstance()
    ->addRoute(new MvcContext('/auth/logout', 'GET', $userCtrl, 'logout', new RouterFilter("", false)));

// ghép tài khoản
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/users/sites/merge', 'GET', $userCtrl, 'mergeSite', new RouterFilter("", false)));
