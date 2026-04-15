<?php

namespace Company\Site;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

/**
 * User router
 */
$siteCtrl = "\\Company\\Site\\Controller\\SiteCtrl";
R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites(/:id)', 'POST,PUT', $siteCtrl, "updateSite", new RouterFilter("rest/sites")));
R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites/:id', 'GET', $siteCtrl, "getSite", new RouterFilter("rest/sites")));
R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites', 'GET', $siteCtrl, "getSites", new RouterFilter("rest/sites")));
R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites/:id', 'DELETE', $siteCtrl, "deleteSite", new RouterFilter("rest/sites")));
R::getInstance()->addRoute(new MVC('/:siteID/master/rest/sites/tags', 'GET', $siteCtrl, "getTags", new RouterFilter("rest/sites")));


