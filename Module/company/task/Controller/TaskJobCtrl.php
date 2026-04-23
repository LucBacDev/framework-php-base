<?php
namespace Company\Task\Controller;

use Company\MVC\Controller;
use Company\Task\Model\TaskJobMapper;

class TaskJobCtrl extends Controller {
    
    /** @var TaskJobMapper */
    protected $jobMapper;

    function __construct() {
        parent::__construct();
        $this->jobMapper = TaskJobMapper::makeInstance();
    }

    /**
     * Quét deadline và đẩy vào queue
     * POST /:siteID/rest/task/jobs/scan-deadline
     */
    function scanDeadline($siteID) {
        try {
            // Require system or manageTask privilege to trigger jobs
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);
            
            // To prevent normal users from triggering this
            if (!$this->auth->hasPrivilege('manageTask')) {
                throw new \Company\Exception\ForbiddenException('Không có quyền gọi job');
            }

            $result = $this->jobMapper->scanDeadline($siteID);
            $this->resp->setBody(json_encode($result));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

    /**
     * Gửi notification và xử lý retry
     * POST /:siteID/rest/task/jobs/send-notification
     */
    function sendNotification($siteID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);
            
            if (!$this->auth->hasPrivilege('manageTask')) {
                throw new \Company\Exception\ForbiddenException('Không có quyền gọi job');
            }

            $result = $this->jobMapper->sendNotification($siteID);
            $this->resp->setBody(json_encode($result));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }
}
