<?php

namespace Company\Encrypt;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

/**
 * User router
 */
$encryptCtrl = "\\Company\\Encrypt\\Controller\\EncryptCtrl";
R::getInstance()->addRoute(new MVC('/master/rest/encrypt/:algo/key', 'GET', $encryptCtrl, "getKeyEncrypt", new RouterFilter("encrypt")));
R::getInstance()->addRoute(new MVC('/master/rest/encrypt', 'POST,PUT', $encryptCtrl, "updateEncrypt", new RouterFilter("encrypt")));
R::getInstance()->addRoute(new MVC('/master/rest/encrypt(/:encryptID)', 'GET', $encryptCtrl, "getEncrypt", new RouterFilter("encrypt")));
R::getInstance()->addRoute(new MVC('/master/rest/encrypt/:encryptID', 'DELETE', $encryptCtrl, "deleteEncrypt", new RouterFilter("encrypt")));
