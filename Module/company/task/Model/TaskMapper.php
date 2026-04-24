<?php

namespace Company\Task\Model;

use Company\Auth\Auth;
use Company\Exception as E;
use Company\Employee\Model\EmployeeMapper;
use Company\User\Model\UserMapper;

class TaskMapper extends \Company\SQL\Mapper {

    protected $dbVersion = '1.0.0';

    public function tableAlias() {
        return 'tsk';
    }

    public function tableName() {
        return 'task';
    }

    function filterSiteFK($siteID) {
        $this->where("tsk.siteFK = ?", __FUNCTION__)
            ->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }

    function filterDeleted($deleted = 0) {
        $this->where("tsk.deleted = ?", __FUNCTION__)
            ->setParamWhere($deleted, __FUNCTION__);
        return $this;
    }

    function filterID($id) {
        $this->where("tsk.id = ?", __FUNCTION__)
            ->setParamWhere($id, __FUNCTION__);
        return $this;
    }

    /**
     * Lấy danh sách task theo phân quyền phòng ban (Bản đầy đủ)
     */
    function getTasks($siteID, $actorID, $privileges, $filters) {
        $privileges = is_array($privileges) ? $privileges : [];
        $hasManageTask = in_array('manageTask', $privileges);

        // 1. Lấy thông tin phòng ban người xem
        $myDepID = '0';
        try {
            $me = EmployeeMapper::makeInstance()->filterID($actorID)->getEntity();
            if (!$me->id) $me = UserMapper::makeInstance()->filterID($actorID)->getEntity();
            $myDepID = ($me && $me->depFK) ? $me->depFK : '0';
        } catch (\Exception $e) {}

        // 2. Các tham số lọc
        $status = (string)arrData($filters, 'status', '');
        $priority = (string)arrData($filters, 'priority', '');
        $search = (string)arrData($filters, 'search', '');
        $page = max(1, (int)arrData($filters, 'page', 1));
        $pageSize = max(1, min(100, (int)arrData($filters, 'pageSize', 20)));

        // 3. Base Query
        $select = "SELECT SQL_CALC_FOUND_ROWS t.*, 
                    (SELECT GROUP_CONCAT(e.fullname SEPARATOR ', ') FROM task_assignee ta2 JOIN employee e ON ta2.assigneeFK = e.id WHERE ta2.taskFK = t.id AND ta2.deleted = 0 AND e.deleted = 0) as assignees,
                    (SELECT GROUP_CONCAT(e.id) FROM task_assignee ta2 JOIN employee e ON ta2.assigneeFK = e.id WHERE ta2.taskFK = t.id AND ta2.deleted = 0 AND e.deleted = 0) as assigneeIDs";
        $from = " FROM task t";
        $where = " WHERE t.siteFK = ? AND t.deleted = 0";
        $params = [$siteID];

        // 4. Phân quyền truy vấn
        if ($siteID === 'master') {
            // Admin tối cao: Thấy tất cả
        } else if ($hasManageTask) {
            $where .= " AND t.depFK = ?";
            $params[] = $myDepID;
        } else {
            $from .= " LEFT JOIN task_assignee ta ON t.id = ta.taskFK AND ta.deleted = 0";
            $where .= " AND t.depFK = ? AND (t.createdBy = ? OR ta.assigneeFK = ?)";
            $params[] = $myDepID;
            $params[] = $actorID;
            $params[] = $actorID;
        }

        // 5. Áp dụng Filters
        if ($status !== '') {
            $where .= " AND t.status = ?";
            $params[] = $status;
        }
        if ($priority !== '') {
            $where .= " AND t.priority = ?";
            $params[] = $priority;
        }
        if ($search !== '') {
            $where .= " AND t.title LIKE ?";
            $params[] = "%$search%";
        }

        $offset = ($page - 1) * $pageSize;
        $sql = $select . $from . $where . " GROUP BY t.id ORDER BY t.createdDate DESC LIMIT " . (int)$offset . ", " . (int)$pageSize;
        $items = $this->db->getRows($sql, $params);
        
        if ($items === false) throw new \Exception("Lỗi SQL: " . $this->db->ErrorMsg());

        return result(true, [
            'total' => (int) $this->db->getFoundRows(),
            'page' => $page,
            'pageSize' => $pageSize,
            'items' => $items
        ]);
    }

    /**
     * Tạo task mới kèm phân quyền phòng ban và log
     */
    function createTask($siteID, $actorID, $input) {
        $title = trim((string) arrData($input, 'title'));
        $description = (string) arrData($input, 'description', '');
        $priority = trim((string) arrData($input, 'priority', 'Vừa'));
        $startTime = trim((string) arrData($input, 'start_time', ''));
        $dueTime = trim((string) arrData($input, 'due_time', ''));
        $progress = (int) arrData($input, 'progress', 0);

        if ($title === '') throw new E\BadRequestException('Tiêu đề không được rỗng');

        // 1. Lấy phòng ban của người tạo (Fallback an toàn)
        $depFK = '0';
        try {
            $creator = EmployeeMapper::makeInstance()->filterID($actorID)->getEntity();
            if (!$creator->id) $creator = UserMapper::makeInstance()->filterID($actorID)->getEntity();
            $depFK = ($creator && $creator->depFK) ? $creator->depFK : '0';
        } catch (\Exception $e) {}

        $id = uid();
        $now = \DateTimeEx::create()->toIsoString();
        
        $data = [
            'id' => $id,
            'siteFK' => $siteID,
            'depFK' => $depFK,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'startTime' => $startTime,
            'dueTime' => $dueTime,
            'status' => 'Mới',
            'attrs' => json_encode(['progress' => $progress]),
            'createdBy' => $actorID,
            'createdDate' => $now,
            'updatedDate' => $now,
            'deleted' => 0,
            'dbVersion' => $this->dbVersion
        ];

        $this->startTrans();
        $this->insert($data);

        // 2. Ghi nhật ký (Audit Log)
        $this->db->insert('task_audit_log', [
            'id' => uid(),
            'taskFK' => $id,
            'siteFK' => $siteID,
            'action' => 'task.create',
            'actorFK' => $actorID,
            'afterData' => json_encode($data),
            'createdDate' => $now
        ]);

        $this->completeTransOrFail();
        $this->syncToElastic($siteID, $id);

        return result(true, ['id' => $id]);
    }

    /**
     * Cập nhật thông tin Task kèm nhật ký và tiến độ
     */
    function updateTask($siteID, $taskID, $actorID, $hasManageTask, $input) {
        $task = $this->makeInstance()->filterID($taskID)->filterSiteFK($siteID)->getEntity();
        if (!$task->id) throw new E\BadRequestException('Công việc không tồn tại');

        $updateData = [];
        if (isset($input['title'])) $updateData['title'] = trim((string)$input['title']);
        if (isset($input['description'])) $updateData['description'] = (string)$input['description'];
        if (isset($input['priority'])) $updateData['priority'] = $input['priority'];
        
        // Xử lý tiến độ (%) trong attrs
        if (isset($input['progress'])) {
            $progress = (int) $input['progress'];
            $attrs = [];
            if (!empty($task->attrs)) {
                $decoded = json_decode($task->attrs, true);
                if (is_array($decoded)) $attrs = $decoded;
            }
            $attrs['progress'] = $progress;
            $updateData['attrs'] = json_encode($attrs);
            
            // Log tiến độ riêng
            $this->db->insert('task_progress_log', [
                'id' => uid(),
                'taskFK' => $taskID,
                'siteFK' => $siteID,
                'actorFK' => $actorID,
                'progress' => $progress,
                'note' => 'Cập nhật từ chi tiết',
                'createdDate' => \DateTimeEx::create()->toIsoString()
            ]);
        }

        if (empty($updateData)) return result(true);

        $updateData['updatedDate'] = \DateTimeEx::create()->toIsoString();

        $this->startTrans();
        $this->makeInstance()->filterID($taskID)->update($updateData);

        // Ghi Audit Log
        $this->db->insert('task_audit_log', [
            'id' => uid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.update',
            'actorFK' => $actorID,
            'beforeData' => json_encode(['title' => $task->title, 'priority' => $task->priority]),
            'afterData' => json_encode($updateData),
            'createdDate' => $updateData['updatedDate']
        ]);

        $this->completeTransOrFail();
        $this->syncToElastic($siteID, $taskID);
        return result(true, ['taskID' => $taskID]);
    }

    /**
     * Story 5.1 - Thống kê Dashboard theo phòng ban
     */
    function getDashboardAggregates($siteID, $actorID, $hasManageTaskPrivilege) {
        $nowStr = \DateTimeEx::create()->toIsoString();
        $soonStr = \DateTimeEx::create()->addHour(24)->toIsoString();

        $me = EmployeeMapper::makeInstance()->filterID($actorID)->getEntity();
        if (!$me->id) $me = UserMapper::makeInstance()->filterID($actorID)->getEntity();
        $myDepID = $me->depFK ?: '0';

        // Base Filter: Nếu là Manager thì xem cả phòng ban, nếu không chỉ xem cá nhân
        $where = " WHERE t.siteFK = ? AND t.deleted = 0";
        $params = [$siteID];
        if (!$hasManageTaskPrivilege) {
            $where .= " AND ta.assigneeFK = ?";
            $params[] = $actorID;
        } else {
            $where .= " AND t.depFK = ?";
            $params[] = $myDepID;
        }

        $sql = "SELECT 
                SUM(IF(t.status IN ('Mới', 'Đang thực hiện'), 1, 0)) as totalTodo,
                SUM(IF(t.status != 'Hoàn thành' AND t.priority = 'Cao', 1, 0)) as totalHighPriority,
                SUM(IF(t.status != 'Hoàn thành' AND t.dueTime < '{$nowStr}', 1, 0)) as totalOverdue,
                SUM(IF(t.status != 'Hoàn thành' AND t.dueTime >= '{$nowStr}' AND t.dueTime <= '{$soonStr}', 1, 0)) as totalDueSoon
            FROM task t 
            LEFT JOIN task_assignee ta ON t.id = ta.taskFK AND ta.deleted = 0
            $where";
        
        $aggregates = $this->db->getRow($sql, $params);

        return result(true, [
            'todo' => (int) arrData($aggregates, 'totalTodo', 0),
            'highPriority' => (int) arrData($aggregates, 'totalHighPriority', 0),
            'dueSoon' => (int) arrData($aggregates, 'totalDueSoon', 0),
            'overdue' => (int) arrData($aggregates, 'totalOverdue', 0)
        ]);
    }

    /**
     * Story 1.5 - Xóa task kèm ràng buộc
     */
    function deleteTask($siteID, $taskID, $actorID, $privileges, $reason) {
        $task = $this->makeInstance()->filterID($taskID)->filterSiteFK($siteID)->getEntity();
        if (!$task->id) throw new E\BadRequestException('Công việc không tồn tại');

        $hasManageTask = is_array($privileges) && in_array('manageTask', $privileges);

        // KIỂM TRA RÀNG BUỘC XÓA
        if (!$hasManageTask) {
            if ($task->createdBy != $actorID) {
                throw new E\ForbiddenException('Bạn không có quyền xóa công việc của người khác');
            }
            // Nhân viên không được xóa task do Manager tạo
            $sqlPrivs = "SELECT privilegeID FROM user_user_privilege WHERE userID=?
                         UNION
                         SELECT privilegeID FROM user_role_privilege WHERE roleID IN (SELECT roleID FROM user_role_user WHERE userID=?)";
            $creatorPrivs = $this->db->getCol($sqlPrivs, [$task->createdBy, $task->createdBy]);
            if (in_array('manageTask', $creatorPrivs)) {
                throw new E\ForbiddenException('Không được phép xóa công việc do Quản lý tạo');
            }
        }

        $now = \DateTimeEx::create()->toIsoString();
        $this->startTrans();
        $this->makeInstance()->filterID($taskID)->update(['deleted' => 1, 'updatedDate' => $now]);

        // Audit Log
        $this->db->insert('task_audit_log', [
            'id' => uid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.delete',
            'actorFK' => $actorID,
            'afterData' => json_encode(['reason' => $reason]),
            'createdDate' => $now
        ]);

        $this->completeTransOrFail();
        $this->syncToElastic($siteID, $taskID);
        return result(true);
    }

    /**
     * Story 1.4 - Đính kèm tệp
     */
    function addAttachments($siteID, $taskID, $actorID, $hasManageTask, $attachments) {
        $now = \DateTimeEx::create()->toIsoString();
        $this->startTrans();
        foreach ($attachments as $fileID) {
            $this->db->insert('task_attachment', [
                'id' => uid(),
                'taskFK' => $taskID,
                'fileFK' => $fileID,
                'siteFK' => $siteID,
                'createdDate' => $now
            ]);
        }
        $this->completeTransOrFail();
        return result(true);
    }

    /**
     * Giao việc cho nhân sự (Hỗ trợ nhiều người - Jira style)
     */
    function assignIndividual($siteID, $taskID, $assigneeIDs, $actorID, $privileges = null) {
        if (!is_array($assigneeIDs)) {
            $assigneeIDs = explode(',', (string)$assigneeIDs);
        }
        $assigneeIDs = array_filter(array_map('trim', $assigneeIDs));

        // 1. Kiểm tra Task
        $task = $this->makeInstance()->filterID($taskID)->filterSiteFK($siteID)->getEntity();
        if (!$task->id) throw new E\BadRequestException('Công việc không tồn tại');

        // 2. KIỂM TRA QUYỀN TRỰC TIẾP
        $auth = Auth::getInstance();
        $auth->setSiteID($siteID);
        $hasManageTask = $auth->hasPrivilege('manageTask');
        $isFullControl = $auth->hasPrivilege('fullcontrol');
        
        $canAssign = false;
        if ($isFullControl) {
            $canAssign = true;
        } else if ($hasManageTask) {
            // Trưởng phòng được giao trong phòng ban mình
            $me = EmployeeMapper::makeInstance()->filterID($actorID)->getEntity();
            if (!$me->id) $me = UserMapper::makeInstance()->filterID($actorID)->getEntity();
            
            $myDepFK = $me->depFK ?: '0';
            $taskDepFK = $task->depFK ?: '0';
            if ($taskDepFK === $myDepFK || $taskDepFK === '0') {
                $canAssign = true;
            }
        } else if ($task->createdBy == $actorID) {
            $canAssign = true; // Người tạo luôn được phép
        }

        if (!$canAssign) {
            throw new E\ForbiddenException('Bạn không có quyền giao việc cho công việc này');
        }

        $now = \DateTimeEx::create()->toIsoString();
        $this->startTrans();

        // 3. Xóa cũ, thêm mới
        $this->db->delete('task_assignee', "taskFK = ? AND siteFK = ?", [$taskID, $siteID]);
        foreach ($assigneeIDs as $eid) {
            if (empty($eid)) continue;
            $this->db->insert('task_assignee', [
                'id' => uid(),
                'taskFK' => $taskID,
                'assigneeFK' => $eid,
                'siteFK' => $siteID,
                'createdDate' => $now,
                'deleted' => 0
            ]);
        }

        // 4. Audit Log
        $this->db->insert('task_audit_log', [
            'id' => uid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.assign.multi',
            'actorFK' => $actorID,
            'afterData' => json_encode(['assignees' => $assigneeIDs]),
            'createdDate' => $now
        ]);

        $this->completeTransOrFail();
        $this->syncToElastic($siteID, $taskID);
        return result(true);
    }

    /**
     * Cập nhật hạn chót
     */
    function updateDeadline($siteID, $taskID, $actorID, $startTime, $dueTime, $privs = null) {
        $this->makeInstance()->filterID($taskID)->update([
            'startTime' => $startTime,
            'dueTime' => $dueTime,
            'updatedDate' => \DateTimeEx::create()->toIsoString()
        ]);
        $this->syncToElastic($siteID, $taskID);
        return result(true);
    }

    /**
     * Chuyển trạng thái an toàn kèm kiểm tra quyền
     */
    private function moveStatus($siteID, $taskID, $actorID, $hasManageTask, $newStatus, $note) {
        $task = $this->makeInstance()->filterID($taskID)->filterSiteFK($siteID)->getEntity();
        if (!$task->id) throw new E\BadRequestException('Công việc không tồn tại');

        // KIỂM TRÀ QUYỀN
        // 1. Kéo vào Hoàn thành: Cần manageTask
        if ($newStatus === 'Hoàn thành') {
            if (!$hasManageTask) {
                throw new E\ForbiddenException('Chỉ người có quyền Quản lý công việc mới được phép Duyệt (Hoàn thành) công việc');
            }
        }
        
        // 2. Kéo ra khỏi Hoàn thành: Cần manageTask
        if ($task->status === 'Hoàn thành') {
            if (!$hasManageTask) {
                throw new E\ForbiddenException('Chỉ người có quyền Quản lý công việc mới được phép kéo công việc ra khỏi trạng thái Hoàn thành');
            }
        }

        // 3. Các trạng thái khác: Cho phép Người tạo hoặc Admin
        if (!$hasManageTask && $task->createdBy != $actorID) {
            throw new E\ForbiddenException('Bạn không có quyền thay đổi trạng thái của công việc này');
        }

        $guard = TaskWorkflowGuard::makeInstance();
        return $guard->executeTransition($siteID, $taskID, $actorID, $newStatus, $note);
    }

    // Khôi phục các hàm gọi luồng trạng thái chuẩn
    function approveTask($s, $tid, $a, $p, $n) { return $this->moveStatus($s, $tid, $a, $p, 'Hoàn thành', $n); }
    function startTask($s, $tid, $a, $p, $n) { return $this->moveStatus($s, $tid, $a, $p, 'Đang thực hiện', $n); }
    function submitTask($s, $tid, $a, $p, $n) { return $this->moveStatus($s, $tid, $a, $p, 'Chờ duyệt', $n); }
    function reworkTask($s, $tid, $a, $p, $n) { return $this->moveStatus($s, $tid, $a, $p, 'Đang thực hiện', $n); }
    function resetTask($s, $tid, $a, $p, $n) { return $this->moveStatus($s, $tid, $a, $p, 'Mới', $n); }

    /**
     * Đồng bộ Task sang Elasticsearch
     */
    function syncToElastic($siteID, $taskID) {
        $sql = "SELECT t.*, 
                    (SELECT GROUP_CONCAT(e.fullname SEPARATOR ', ') FROM task_assignee ta2 JOIN employee e ON ta2.assigneeFK = e.id WHERE ta2.taskFK = t.id AND ta2.deleted = 0 AND e.deleted = 0) as assignees,
                    (SELECT GROUP_CONCAT(e.id) FROM task_assignee ta2 JOIN employee e ON ta2.assigneeFK = e.id WHERE ta2.taskFK = t.id AND ta2.deleted = 0 AND e.deleted = 0) as assigneeIDs
                FROM task t
                WHERE t.id = ? AND t.siteFK = ?";
        $task = $this->db->getRow($sql, [$taskID, $siteID]);
        if ($task) {
            if (!empty($task['assigneeIDs'])) {
                $task['assigneeIDs'] = explode(',', $task['assigneeIDs']);
            } else {
                $task['assigneeIDs'] = [];
            }
            if (!empty($task['attrs'])) {
                $decoded = json_decode($task['attrs'], true);
                if (is_array($decoded)) {
                    $task['attrs'] = $decoded;
                }
            }
            $elasticMsg = TaskElasticMapper::makeInstance()->buildElasticMessage(
                TaskElasticMapper::METHOD_UPDATE,
                $task,
                $taskID,
                null,
                ['updatedDate']
            );
            TaskElasticMapper::makeInstance()->addQueue($elasticMsg);
        }
    }
}
