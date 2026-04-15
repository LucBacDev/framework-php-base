<?php

namespace CompanyUI\Queue;

use Company\MVC\MvcContext;
use Company\MVC\Router;
use Company\MVC\RouterFilter;

$ctrl = "CompanyUI\\Queue\\QueueCtrl";

Router::getInstance()
    ->addRoute(new MvcContext('/master/messageQueueManager', 'GET', $ctrl, 'MessageQueueManager', new RouterFilter("", false)));
