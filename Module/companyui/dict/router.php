<?php

namespace CompanyUI\User;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Dict\\DictCtrl";
//đăng ký url mới
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/dict/collection', 'GET', $ctrl, 'DictCollectionList', new RouterFilter("", false)));
Router::getInstance()
    ->addRoute(new MvcContext('/:siteID/dict/item', 'GET', $ctrl, 'DictItemList', new RouterFilter("", true)));

