<?php

namespace Company\Session;

use Company\MVC\Bootstrap;
use Company\MVC\Trigger;

$app = Bootstrap::getInstance();

//cấu hình session
SessionAdapter::getInstance()->config($app->config['session']['expire']);

Trigger::register('Bootstrap/shutdown', function() {
    //lưu sesison khi tắt phần mềm
    SessionAdapter::getInstance()->saveSession();
});

