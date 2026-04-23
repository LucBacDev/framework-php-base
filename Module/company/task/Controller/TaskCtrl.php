<?php

namespace Company\Task\Controller;

use Company\Auth\Auth;
use Company\Task\Model\TaskMapper;

class TaskCtrl extends \Company\MVC\Controller {

    /**
     * Story 5.2 - Danh sách task (My Tasks & Pending Approval)
     * GET /:siteID/rest/task/tasks
     */
    function getTasks($siteID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $input = $this->input();
            $actorID = $this->auth->getUser()->id;
            $hasManageTaskPrivilege = $this->auth->hasPrivilege('manageTask');

            // Parse Filters
            $filters = [
                'view' => (string) arrData($input, 'view', 'my_tasks'), // 'my_tasks' hoặc 'pending_approval'
                'status' => (string) arrData($input, 'status', ''),
                'priority' => (string) arrData($input, 'priority', ''),
                'isOverdue' => (string) arrData($input, 'isOverdue', 'false') === 'true',
                'isDueSoon' => (string) arrData($input, 'isDueSoon', 'false') === 'true',
                'page' => max(1, (int) arrData($input, 'page', 1)),
                'pageSize' => max(1, min(100, (int) arrData($input, 'pageSize', 20))),
                'sortBy' => (string) arrData($input, 'sortBy', 'createdDate'), // createdDate, deadline
                'sortOrder' => strtoupper((string) arrData($input, 'sortOrder', 'DESC')) === 'ASC' ? 'ASC' : 'DESC'
            ];

            $result = $this->taskMapper->getTasks($siteID, $actorID, $hasManageTaskPrivilege, $filters);
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

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

    /**
     * Story 5.1 - Lấy thông tin Dashboard
     * GET /:siteID/rest/task/dashboard
     */
    function getDashboard($siteID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $actorID = $this->auth->getUser()->id;
            $hasManageTaskPrivilege = $this->auth->hasPrivilege('manageTask');

            $result = $this->taskMapper->getDashboardAggregates($siteID, $actorID, $hasManageTaskPrivilege);
            $this->resp->setBody(json_encode($result));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

    /**
     * Story 1.2 - Giao task cho cá nhân (assignee đơn)
     * POST /:siteID/rest/task/tasks/:taskID/assign/individual
     */
    function assignIndividualTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);
            $this->auth->requirePrivilege('manageTask');

            $input = $this->input();
            $assigneeID = trim((string) arrData($input, 'assigneeID'));
            if ($assigneeID === '') {
                throw new \Company\Exception\BadRequestException('Thiếu trường bắt buộc: assigneeID');
            }

            $result = $this->taskMapper->assignIndividual($siteID, $taskID, $assigneeID, $this->auth->getUser()->id);
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

    /**
     * Story 1.3 - Giao task cho phòng ban (snapshot thành viên)
     * POST /:siteID/rest/task/tasks/:taskID/assign/department
     */
    function assignDepartmentTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);
            $this->auth->requirePrivilege('manageTask');

            $input = $this->input();
            $departmentID = trim((string) arrData($input, 'departmentID'));
            if ($departmentID === '') {
                throw new \Company\Exception\BadRequestException('Thiếu trường bắt buộc: departmentID');
            }

            $result = $this->taskMapper->assignDepartment($siteID, $taskID, $departmentID, $this->auth->getUser()->id);
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

    /**
     * Story 1.4 - Đính kèm tệp cho task
     * POST /:siteID/rest/task/tasks/:taskID/attachments
     */
    function uploadAttachmentsTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $input = $this->input();
            $attachments = arrData($input, 'attachments', []);
            if (empty($attachments) || !is_array($attachments)) {
                throw new \Company\Exception\BadRequestException('Thiếu danh sách attachments hợp lệ');
            }

            // Gọi Mapper với user đang login
            $actorID = $this->auth->getUser()->id;
            
            // Note: Privilege check 'manageTask' is not strictly required here if user is assignee/creator. 
            // The Mapper will check if actor has access to this task.
            $hasManageTaskPrivilege = $this->auth->hasPrivilege('manageTask');

            $result = $this->taskMapper->addAttachments($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $attachments);
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

    /**
     * Story 1.5 - Xóa task (soft delete)
     * DELETE /:siteID/rest/task/tasks/:taskID
     */
    function deleteTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $input = $this->input();
            $reason = trim((string) arrData($input, 'reason', ''));

            $actorID = $this->auth->getUser()->id;
            $hasManageTaskPrivilege = $this->auth->hasPrivilege('manageTask');

            $result = $this->taskMapper->deleteTask($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $reason);
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\BadRequestException $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 400)));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Company\Exception\ConflictException $e) {
            $this->resp->setStatus(409);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 409)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

    /**
     * Story 2.1 - Cập nhật deadline
     * PATCH /:siteID/rest/task/tasks/:taskID/deadline
     */
    function updateDeadlineTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);
            $this->auth->requirePrivilege('manageTask');

            $input = $this->input();
            $dueTime = trim((string) arrData($input, 'dueTime', ''));
            if ($dueTime === '') {
                throw new \Company\Exception\BadRequestException('Thiếu trường bắt buộc: dueTime');
            }
            $startTime = trim((string) arrData($input, 'startTime', ''));

            $actorID = $this->auth->getUser()->id;

            $result = $this->taskMapper->updateDeadline($siteID, $taskID, $actorID, $startTime, $dueTime);
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\BadRequestException $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 400)));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Company\Exception\ConflictException $e) {
            $this->resp->setStatus(409);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 409)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

    /**
     * Story 3.1 - Cập nhật tiến độ
     * POST /:siteID/rest/task/tasks/:taskID/progress
     */
    function updateProgressTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $input = $this->input();
            if (!isset($input['progress'])) {
                throw new \Company\Exception\BadRequestException('Thiếu trường bắt buộc: progress');
            }
            $progress = (int) $input['progress'];
            $note = trim((string) arrData($input, 'note', ''));

            $actorID = $this->auth->getUser()->id;

            $result = $this->taskMapper->updateProgress($siteID, $taskID, $actorID, $progress, $note);
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\BadRequestException $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 400)));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Company\Exception\ConflictException $e) {
            $this->resp->setStatus(409);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 409)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

    /**
     * Story 3.2 - Truy vấn lịch sử cập nhật tiến độ
     * GET /:siteID/rest/task/tasks/:taskID/progress
     */
    function getProgressHistory($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $pageSize = isset($_GET['pageSize']) ? (int) $_GET['pageSize'] : 20;

            if ($page < 1) $page = 1;
            if ($pageSize < 1) $pageSize = 20;

            $actorID = $this->auth->getUser()->id;
            $hasManageTaskPrivilege = $this->auth->hasPrivilege('manageTask');

            $result = $this->taskMapper->getProgressHistory($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $page, $pageSize);
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

    /**
     * Story 4.2 - Staff bắt đầu làm task
     * POST /:siteID/rest/task/tasks/:taskID/start
     */
    function startTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $input = $this->input();
            $note = trim((string) arrData($input, 'note', ''));

            $actorID = $this->auth->getUser()->id;

            $result = $this->taskMapper->startTask($siteID, $taskID, $actorID, $note);
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\BadRequestException $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 400)));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Company\Exception\ConflictException $e) {
            $this->resp->setStatus(409);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 409)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

    /**
     * Story 4.3 - Staff gửi duyệt kết quả
     * POST /:siteID/rest/task/tasks/:taskID/submit
     */
    function submitTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $input = $this->input();
            $note = trim((string) arrData($input, 'note', ''));
            
            // Validate bắt buộc phải có nội dung tóm tắt
            if ($note === '') {
                throw new \Company\Exception\BadRequestException('Bắt buộc phải nhập báo cáo tóm tắt (note) khi gửi duyệt');
            }

            $actorID = $this->auth->getUser()->id;

            $result = $this->taskMapper->submitTask($siteID, $taskID, $actorID, $note);
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\BadRequestException $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 400)));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Company\Exception\ConflictException $e) {
            $this->resp->setStatus(409);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 409)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

    /**
     * Story 4.4 - Manager phê duyệt task
     * POST /:siteID/rest/task/tasks/:taskID/approve
     */
    function approveTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $input = $this->input();
            $note = trim((string) arrData($input, 'note', ''));
            
            $actorID = $this->auth->getUser()->id;
            $hasManageTaskPrivilege = $this->auth->hasPrivilege('manageTask');

            $result = $this->taskMapper->approveTask($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $note);
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\BadRequestException $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 400)));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Company\Exception\ConflictException $e) {
            $this->resp->setStatus(409);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 409)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }

    /**
     * Story 4.5 - Manager yêu cầu làm lại task
     * POST /:siteID/rest/task/tasks/:taskID/rework
     */
    function reworkTask($siteID, $taskID) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireLogin();
            $this->auth->requireSite($siteID);

            $input = $this->input();
            $note = trim((string) arrData($input, 'note', ''));
            
            // Validate bắt buộc phải có lý do làm lại
            if ($note === '') {
                throw new \Company\Exception\BadRequestException('Bắt buộc phải nhập lý do (note) khi yêu cầu làm lại task');
            }

            $actorID = $this->auth->getUser()->id;
            $hasManageTaskPrivilege = $this->auth->hasPrivilege('manageTask');

            $result = $this->taskMapper->reworkTask($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $note);
            $this->resp->setBody(json_encode($result));
        } catch (\Company\Exception\BadRequestException $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 400)));
        } catch (\Company\Exception\ForbiddenException $e) {
            $this->resp->setStatus(403);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 403)));
        } catch (\Company\Exception\ConflictException $e) {
            $this->resp->setStatus(409);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 409)));
        } catch (\Exception $e) {
            $this->resp->setStatus(500);
            $this->resp->setBody(json_encode(result(false, $e->getMessage(), 500)));
        }
    }
}
