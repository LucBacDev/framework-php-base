<?php

namespace Company\Task\Controller;

use Company\Auth\Auth;
use Company\Task\Model\TaskMapper;

class TaskCtrl extends \Company\MVC\Controller {

    /** @var TaskMapper */
    protected $taskMapper;

    /** @var Auth */
    protected $auth;

    function init() {
        parent::init();
        $this->taskMapper = TaskMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * Story 1.1 - Tạo task cơ bản.
     * POST /:siteID/rest/task/tasks
     */
    function createTask($siteID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);
            $this->auth->requirePrivilege('manageTask');

            $result = $this->taskMapper->createTask($siteID, $this->auth->getUser()->id, $this->input());
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\BadRequestException $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 400)));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }
}
