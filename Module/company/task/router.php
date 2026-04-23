<?php

namespace Company\Task;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$ctrl = "\\Company\\Task\\Controller\\TaskCtrl";

// Story 5.2 - Danh sách tasks
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks', 'GET', $ctrl, 'getTasks', new RouterFilter("/rest/task"))
);

// Story 1.1 - CRUD Task
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks', 'POST', $ctrl, 'createTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/dashboard', 'GET', $ctrl, 'getDashboard', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/assign/individual', 'POST', $ctrl, 'assignIndividualTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/assign/department', 'POST', $ctrl, 'assignDepartmentTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/attachments', 'POST', $ctrl, 'uploadAttachmentsTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID', 'DELETE', $ctrl, 'deleteTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/deadline', 'PATCH', $ctrl, 'updateDeadlineTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/progress', 'POST', $ctrl, 'updateProgressTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/progress', 'GET', $ctrl, 'getProgressHistory', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/start', 'POST', $ctrl, 'startTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/submit', 'POST', $ctrl, 'submitTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/approve', 'POST', $ctrl, 'approveTask', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/tasks/:taskID/rework', 'POST', $ctrl, 'reworkTask', new RouterFilter("/rest/task"))
);

// Story 2.2 - Job routes
$jobCtrl = '\Company\Task\Controller\TaskJobCtrl';
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/jobs/scan-deadline', 'POST', $jobCtrl, 'scanDeadline', new RouterFilter("/rest/task"))
);
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/task/jobs/send-notification', 'POST', $jobCtrl, 'sendNotification', new RouterFilter("/rest/task"))
);

