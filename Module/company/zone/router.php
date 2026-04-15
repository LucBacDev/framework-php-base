<?php

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use \Company\MVC\RouterFilter;

$ctrl = "\\Company\\Zone\\Controller\\ZoneCtrl";
R::getInstance()->addRoute(new MVC('/master/rest/zone/:id', 'POST,PUT', $ctrl, "updateZone", new RouterFilter("rest/zone")));
R::getInstance()->addRoute(new MVC('/master/rest/zone', 'GET', $ctrl, "getZones", new RouterFilter("rest/zone")));
R::getInstance()->addRoute(new MVC('/master/rest/zone/:id', 'GET', $ctrl, "getZone", new RouterFilter("rest/zone")));
R::getInstance()->addRoute(new MVC('/master/rest/zone/:id', 'DELETE', $ctrl, "deleteZone", new RouterFilter("rest/zone")));

R::getInstance()->addRoute(new MVC('/master/rest/contactPoint(/:id)', 'POST,PUT', $ctrl, "insertContactPoint", new RouterFilter("rest/contactPoint")));
R::getInstance()->addRoute(new MVC('/master/rest/contactPoint', 'GET', $ctrl, "getContactPoints", new RouterFilter("rest/contactPoint")));
R::getInstance()->addRoute(new MVC('/master/rest/contactPoint/:id', 'GET', $ctrl, "getContactPoint", new RouterFilter("rest/contactPoint")));
R::getInstance()->addRoute(new MVC('/master/rest/contactPoint/:id', 'DELETE', $ctrl, "deleteContactPoint", new RouterFilter("rest/contactPoint")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/contactPoint/detect', 'GET', $ctrl, "detectContactPoint", new RouterFilter("rest/contactPoint")));

R::getInstance()->addRoute(new MVC('/rest/hostname', 'GET', $ctrl, "getHostName"));