<?php

namespace Company\License;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

/**
 * license router
 */
$licenseCtrl = "\\Company\\License\\Controller\\LicenseCtrl";

// route license
R::getInstance()->addRoute(new MVC('/:siteID/rest/licenses', 'GET', $licenseCtrl, "getLicenses", new RouterFilter("license")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/license(/:id)', 'GET', $licenseCtrl, "getLicense", new RouterFilter("license")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/licenses/register', 'POST', $licenseCtrl, "register", new RouterFilter("license")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/licenses/upload', 'POST,PUT', $licenseCtrl, "uploadLicenseFile", new RouterFilter("license")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/license/:id', 'POST,PUT', $licenseCtrl, "refreshLicense", new RouterFilter("license")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/license/downloadHardWareID', 'POST,PUT', $licenseCtrl, "downloadHardWareID", new RouterFilter("license")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/license/:id', 'DELETE', $licenseCtrl, "returnLicense", new RouterFilter("license")));
R::getInstance()->addRoute(new MVC('/:siteID/rest/licenses/autoCheckLicense', 'GET', $licenseCtrl, "autoCheckLicense", new RouterFilter("license")));
