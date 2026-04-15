<?php

namespace Company\Queue;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;
use Company\Queue\Controller\QueueCtrl;

R::getInstance()->addRoute(new MVC('/master/rest/topic', 'GET', QueueCtrl::class, "getTopics", new RouterFilter("rest")));
R::getInstance()->addRoute(new MVC('/master/rest/queue/messages', 'GET', QueueCtrl::class, "getQueueManager", new RouterFilter("rest")));
R::getInstance()->addRoute(new MVC('/master/rest/queue/messages/:id/update', 'POST', QueueCtrl::class, "updateMessage", new RouterFilter("rest")));
R::getInstance()->addRoute(new MVC('/master/rest/queue/messages/retry/:ids', 'GET', QueueCtrl::class, "retry", new RouterFilter("rest")));
