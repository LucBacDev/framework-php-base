<?php

namespace Company\VersionInfo;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$versionInfoCtrl = "\\Company\\VersionInfo\\Controller\\VersionInfoCtrl";

// route versionInfo
R::getInstance()->addRoute(new MVC('/:siteID/rest/versionInfo', 'GET', $versionInfoCtrl, "getVersionInfo", new RouterFilter("versionInfo")));
