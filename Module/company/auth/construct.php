<?php

namespace Company\Auth;

$app = \Company\MVC\Bootstrap::getInstance();
if (isset($app->config['session']) && isset($app->config['session']['updateInterval'])) {
    Auth::config($app->config['session']['updateInterval']);
}

Auth::registerAuthMethod(new LocalDBAuth);
