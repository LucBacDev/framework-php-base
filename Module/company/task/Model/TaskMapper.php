<?php

namespace Company\Task\Model;

use Company\Exception as E;

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

    function filterActive($active = 1) {
        $this->where("tsk.active = ?", __FUNCTION__)
            ->setParamWhere($active, __FUNCTION__);
        return $this;
    }

    function filterDepFK($depFK) {
        $this->where("tsk.depFK = ?", __FUNCTION__)
            ->setParamWhere($depFK, __FUNCTION__);
        return $this;
    }

    /**
     * Story 1.1 - tạo task cơ bản + audit log.
     */
    function createTask($siteID, $actorID, $input) {
        $title = trim((string) arrData($input, 'title'));
        $description = trim((string) arrData($input, 'description'));
        $priority = trim((string) arrData($input, 'priority', 'Vừa'));
        $startTime = trim((string) arrData($input, 'start_time'));
        $dueTime = trim((string) arrData($input, 'due_time'));

        if ($title === '') {
            throw new E\BadRequestException('Thiếu trường bắt buộc: title');
        }
        if (mb_strlen($title) > 200) {
            throw new E\BadRequestException('title vượt quá 200 ký tự');
        }

        $allowedPriority = ['Thấp', 'Vừa', 'Cao'];
        if (!in_array($priority, $allowedPriority, true)) {
            throw new E\BadRequestException('priority không hợp lệ');
        }

        if ($startTime !== '' && $dueTime !== '' && strtotime($startTime) > strtotime($dueTime)) {
            throw new E\BadRequestException('start_time phải nhỏ hơn hoặc bằng due_time');
        }

        $id = uniqid();
        $now = \DateTimeEx::create()->toIsoString();

        $taskData = [
            'id' => $id,
            'siteFK' => $siteID,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'startTime' => $startTime,
            'dueTime' => $dueTime,
            'status' => 'Mới',
            'createdBy' => $actorID,
            'createdDate' => $now,
            'updatedDate' => $now,
            'deleted' => 0,
            'dbVersion' => $this->dbVersion
        ];

        $auditData = [
            'id' => uniqid(),
            'taskFK' => $id,
            'siteFK' => $siteID,
            'action' => 'task.create',
            'actorFK' => $actorID,
            'beforeData' => null,
            'afterData' => json_encode($taskData),
            'createdDate' => $now
        ];

        $this->startTrans();
        $this->insert($taskData);
        $this->db->insert('task_audit_log', $auditData);
        $this->completeTransOrFail();

        return result(true, [
            'id' => $id,
            'status' => 'Mới',
            'title' => $title,
            'priority' => $priority,
            'start_time' => $startTime,
            'due_time' => $dueTime
        ]);
    }

    /**
     * Story 5.2 - Lấy danh sách task (My Tasks & Pending Approval)
     */
    function getTasks($siteID, $actorID, $hasManageTaskPrivilege, $filters) {
        $view = $filters['view'];
        $status = $filters['status'];
        $priority = $filters['priority'];
        $isOverdue = $filters['isOverdue'];
        $isDueSoon = $filters['isDueSoon'];
        $search = arrData($filters, 'search', '');
        $page = $filters['page'];
        $pageSize = $filters['pageSize'];
        $sortBy = $filters['sortBy'];
        $sortOrder = $filters['sortOrder'];

        // Base Query
        $select = "SELECT SQL_CALC_FOUND_ROWS t.*";
        $from = " FROM task t";
        $where = " WHERE t.siteFK = ? AND t.deleted = 0";
        $params = [$siteID];

        // Phân luồng View
        if ($view === 'pending_approval') {
            if (!$hasManageTaskPrivilege) {
                throw new E\ForbiddenException('Chỉ Manager mới được xem hàng chờ duyệt toàn phòng ban');
            }
            $where .= " AND t.status = 'Chờ duyệt'";
        } else {
            // view = my_tasks
            $from .= " JOIN task_assignee ta ON t.id = ta.taskFK";
            $where .= " AND ta.assigneeFK = ? AND ta.deleted = 0";
            $params[] = $actorID;
        }

        // Filters
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

        $nowStr = \DateTimeEx::create()->toIsoString();
        $soonStr = \DateTimeEx::create()->addHour(24)->toIsoString();

        if ($isOverdue) {
            $where .= " AND t.status != 'Hoàn thành' AND t.dueTime < ?";
            $params[] = $nowStr;
        } else if ($isDueSoon) {
            $where .= " AND t.status != 'Hoàn thành' AND t.dueTime >= ? AND t.dueTime <= ?";
            $params[] = $nowStr;
            $params[] = $soonStr;
        }

        // Sorting
        $allowedSortColumns = ['createdDate', 'dueTime', 'priority'];
        $sortColumn = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'createdDate';
        $orderBy = " ORDER BY t.{$sortColumn} {$sortOrder}";

        // Paging
        $offset = ($page - 1) * $pageSize;
        $limit = " LIMIT " . (int)$offset . ", " . (int)$pageSize;

        $sql = $select . $from . $where . $orderBy . $limit;

        // Cần đảm bảo getRows support mảng tham số chuẩn (đặc biệt là integer)
        // Nếu DB wrapper của công ty tự handle thì tốt, nếu không thì dùng PDO bindValue
        // Ở đây giả sử $this->db->getRows handle được.
        $items = $this->db->getRows($sql, $params);
        if ($items === false) {
            $items = [];
        }
        $total = (int) $this->db->getFoundRows();

        return result(true, [
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'items' => $items
        ]);
    }

    /**
     * Story 5.1 - Thống kê Dashboard
     */
    function getDashboardAggregates($siteID, $actorID, $hasManageTaskPrivilege) {
        $nowStr = \DateTimeEx::create()->toIsoString();
        $soonStr = \DateTimeEx::create()->addHour(24)->toIsoString();

        // 1. Lấy thông số Todo, HighPriority, Overdue, DueSoon cho cá nhân
        // Lưu ý: Chỉ tính các task mà actorID được assign.
        $sql = "
            SELECT 
                SUM(IF(t.status IN ('Mới', 'Đang thực hiện'), 1, 0)) as totalTodo,
                SUM(IF(t.status != 'Hoàn thành' AND t.priority = 'Cao', 1, 0)) as totalHighPriority,
                SUM(IF(t.status != 'Hoàn thành' AND t.dueTime < ?, 1, 0)) as totalOverdue,
                SUM(IF(t.status != 'Hoàn thành' AND t.dueTime >= ? AND t.dueTime <= ?, 1, 0)) as totalDueSoon
            FROM task t
            JOIN task_assignee ta ON t.id = ta.taskFK
            WHERE t.siteFK = ? AND ta.assigneeFK = ? AND t.deleted = 0 AND ta.deleted = 0
        ";
        $aggregates = $this->db->getRow($sql, [$nowStr, $nowStr, $soonStr, $siteID, $actorID]);


        // 2. Lấy bộ đếm gom nhóm theo Status
        $sqlByStatus = "
            SELECT t.status, COUNT(t.id) as total
            FROM task t
            JOIN task_assignee ta ON t.id = ta.taskFK
            WHERE t.siteFK = ? AND ta.assigneeFK = ? AND t.deleted = 0 AND ta.deleted = 0
            GROUP BY t.status
        ";
        $statusCounts = $this->db->getAssoc($sqlByStatus, [$siteID, $actorID]);

        // 3. Lấy bộ đếm gom nhóm theo Priority
        $sqlByPriority = "
            SELECT t.priority, COUNT(t.id) as total
            FROM task t
            JOIN task_assignee ta ON t.id = ta.taskFK
            WHERE t.siteFK = ? AND ta.assigneeFK = ? AND t.deleted = 0 AND ta.deleted = 0
            GROUP BY t.priority
        ";
        $priorityCounts = $this->db->getAssoc($sqlByPriority, [$siteID, $actorID]);

        // 4. Nếu là Manager thì tính thêm pendingApproval toàn site
        $pendingApproval = 0;
        if ($hasManageTaskPrivilege) {
            $sqlPending = "SELECT COUNT(id) as total FROM task WHERE siteFK = ? AND status = 'Chờ duyệt' AND deleted = 0";
            $rowPending = $this->db->getRow($sqlPending, [$siteID]);
            $pendingApproval = (int) arrData($rowPending, 'total', 0);
        }

        return result(true, [
            'todo' => (int) arrData($aggregates, 'totalTodo', 0),
            'highPriority' => (int) arrData($aggregates, 'totalHighPriority', 0),
            'dueSoon' => (int) arrData($aggregates, 'totalDueSoon', 0),
            'overdue' => (int) arrData($aggregates, 'totalOverdue', 0),
            'pendingApproval' => $pendingApproval,
            'byStatus' => $statusCounts,
            'byPriority' => $priorityCounts
        ]);
    }

    /**
     * Story 1.2 - Giao task cho cá nhân
     */
    function assignIndividual($siteID, $taskID, $assigneeID, $actorID) {
        // Validate Task
        $task = $this->makeInstance()
            ->filterID($taskID)
            ->filterSiteFK($siteID)
            ->filterDeleted(0)
            ->getEntity();

        if (!$task->id) {
            throw new E\BadRequestException('Task không tồn tại');
        }

        // Validate Assignee (Employee)
        $employee = \Company\Employee\Model\EmployeeMapper::makeInstance()
            ->filterID($assigneeID)
            ->filterSiteFK($siteID)
            ->filterActive(1)
            ->filterDeleted(0)
            ->getEntity();

        if (!$employee->id) {
            throw new E\BadRequestException('Nhân sự không tồn tại hoặc đã nghỉ việc');
        }

        $now = \DateTimeEx::create()->toIsoString();

        $this->startTrans();

        // Xóa assignee cũ nếu có (bởi vì đây là assign đơn - 1 người)
        // Lưu ý: Nếu hệ thống hỗ trợ nhiều assignee thì chỉ cần check tồn tại, nhưng yêu cầu là "assignee đơn"
        // Nên sẽ xóa toàn bộ assign cũ của task này, sau đó insert mới.
        $this->db->delete('task_assignee', "taskFK = ? AND siteFK = ?", [$taskID, $siteID]);

        // Insert assignee mới
        $assigneeData = [
            'id' => uniqid(),
            'taskFK' => $taskID,
            'assigneeFK' => $assigneeID,
            'siteFK' => $siteID,
            'createdDate' => $now,
            'deleted' => 0
        ];
        $this->db->insert('task_assignee', $assigneeData);

        // Audit log
        $auditData = [
            'id' => uniqid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.assign.individual',
            'actorFK' => $actorID,
            'beforeData' => null,
            'afterData' => json_encode(['assigneeFK' => $assigneeID]),
            'createdDate' => $now
        ];
        $this->db->insert('task_audit_log', $auditData);

        $this->completeTransOrFail();

        return result(true, [
            'taskID' => $taskID,
            'assigneeID' => $assigneeID
        ]);
    }

    /**
     * Story 1.3 - Giao task cho phòng ban (snapshot thành viên)
     */
    function assignDepartment($siteID, $taskID, $departmentID, $actorID) {
        // Validate Task
        $task = $this->makeInstance()
            ->filterID($taskID)
            ->filterSiteFK($siteID)
            ->filterDeleted(0)
            ->getEntity();

        if (!$task->id) {
            throw new E\BadRequestException('Task không tồn tại');
        }

        // Query active members of the department
        $employees = \Company\Employee\Model\EmployeeMapper::makeInstance()
            ->filterDepFK($departmentID)
            ->filterSiteFK($siteID)
            ->filterActive(1)
            ->filterDeleted(0)
            ->getArray();

        if (empty($employees)) {
            throw new E\BadRequestException('Không có thành viên hợp lệ nào trong phòng ban này');
        }

        $now = \DateTimeEx::create()->toIsoString();
        $assigneeIDs = [];

        $this->startTrans();

        // Xóa assignee cũ của task (do được giao mới theo department)
        $this->db->delete('task_assignee', "taskFK = ? AND siteFK = ?", [$taskID, $siteID]);

        // Insert snapshot assignees
        foreach ($employees as $emp) {
            $assigneeData = [
                'id' => uniqid(),
                'taskFK' => $taskID,
                'assigneeFK' => $emp['id'],
                'siteFK' => $siteID,
                'createdDate' => $now,
                'deleted' => 0
            ];
            $this->db->insert('task_assignee', $assigneeData);
            $assigneeIDs[] = $emp['id'];
        }

        // Audit log
        $auditData = [
            'id' => uniqid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.assign.department',
            'actorFK' => $actorID,
            'beforeData' => null,
            'afterData' => json_encode([
                'departmentID' => $departmentID,
                'assigneeIDs' => $assigneeIDs
            ]),
            'createdDate' => $now
        ];
        $this->db->insert('task_audit_log', $auditData);

        $this->completeTransOrFail();

        return result(true, [
            'taskID' => $taskID,
            'departmentID' => $departmentID,
            'assigneeCount' => count($assigneeIDs)
        ]);
    }

    /**
     * Story 1.4 - Đính kèm tệp cho task
     */
    function addAttachments($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $attachments) {
        // Validate Task
        $task = $this->makeInstance()
            ->filterID($taskID)
            ->filterSiteFK($siteID)
            ->filterDeleted(0)
            ->getEntity();

        if (!$task->id) {
            throw new E\BadRequestException('Task không tồn tại');
        }

        if ($task->status === 'Hoàn thành') {
            throw new E\ConflictException('Không thể đính kèm tệp vào task đã Hoàn thành (quy tắc khóa dữ liệu)');
        }

        // Validate Access: 
        // 1. User is creator
        // 2. User has manageTask privilege
        // 3. User is an assignee
        $hasAccess = false;
        if ($task->createdBy == $actorID || $hasManageTaskPrivilege) {
            $hasAccess = true;
        } else {
            // Check if actor is an assignee
            $isAssignee = $this->db->getRow("SELECT id FROM task_assignee WHERE taskFK = ? AND assigneeFK = ? AND siteFK = ? AND deleted = 0 LIMIT 1", [$taskID, $actorID, $siteID]);
            if ($isAssignee) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            throw new E\ForbiddenException('Bạn không có quyền đính kèm tệp vào task này');
        }

        // Calculate total size and validate limit (50MB)
        $totalSize = 0;
        $maxSize = 50 * 1024 * 1024; // 50MB
        foreach ($attachments as $att) {
            if (isset($att['b64'])) {
                $totalSize += strlen($att['b64']);
            }
        }

        if ($totalSize > $maxSize) {
            throw new E\BadRequestException('Tổng dung lượng các tệp vượt quá giới hạn 50MB');
        }

        $now = \DateTimeEx::create()->toIsoString();
        $savedAttachments = [];

        $this->startTrans();

        foreach ($attachments as $att) {
            $b64 = arrData($att, 'b64', '');
            $name = arrData($att, 'name', 'unknown');
            $mime = arrData($att, 'mime', 'application/octet-stream');

            if ($b64 === '') continue;

            // 1. Insert directly into system_file
            $fileID = uniqid();
            $fileData = [
                'id' => $fileID,
                'createdDate' => $now,
                'siteID' => $siteID,
                'context' => 'task_attachment',
                'mime' => $mime,
                'name' => $name,
                'b64' => $b64,
                'b64Size' => strlen($b64)
            ];
            $this->db->insert('system_file', $fileData);

            // 2. Insert metadata into task_attachment
            $taskAttachmentID = uniqid();
            $taskAttachmentData = [
                'id' => $taskAttachmentID,
                'taskFK' => $taskID,
                'fileFK' => $fileID,
                'siteFK' => $siteID,
                'createdDate' => $now,
                'deleted' => 0
            ];
            $this->db->insert('task_attachment', $taskAttachmentData);

            $savedAttachments[] = [
                'id' => $taskAttachmentID,
                'fileID' => $fileID,
                'name' => $name,
                'size' => $fileData['b64Size']
            ];
        }

        // 3. Audit log
        if (!empty($savedAttachments)) {
            $auditData = [
                'id' => uniqid(),
                'taskFK' => $taskID,
                'siteFK' => $siteID,
                'action' => 'task.attachment.add',
                'actorFK' => $actorID,
                'beforeData' => null,
                'afterData' => json_encode(['attachments' => $savedAttachments]),
                'createdDate' => $now
            ];
            $this->db->insert('task_audit_log', $auditData);
        }

        $this->completeTransOrFail();

        return result(true, [
            'taskID' => $taskID,
            'attachments' => $savedAttachments
        ]);
    }

    /**
     * Story 1.5 - Xóa task (soft delete)
     */
    function deleteTask($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $reason) {
        $task = $this->makeInstance()
            ->filterID($taskID)
            ->filterSiteFK($siteID)
            ->filterDeleted(0)
            ->getEntity();

        if (!$task->id) {
            throw new E\BadRequestException('Task không tồn tại hoặc đã bị xóa');
        }

        // Rule Matrix Validation
        if (!$hasManageTaskPrivilege) {
            // Staff logic
            if ($task->createdBy != $actorID) {
                throw new E\ForbiddenException('Bạn không có quyền xóa task của người khác');
            }
            if ($task->status !== 'Mới') {
                // If it's already in progress or completed, staff cannot delete
                throw new E\ConflictException('Không thể xóa task đã phát sinh xử lý (trạng thái khác Mới)');
            }
        } else {
            // Manager logic
            // Managers can soft delete regardless of state, but we log the action and reason.
            // Requirement: "Manager không xóa cứng task đã phát sinh xử lý. Admin chỉ xóa theo policy (ưu tiên soft delete)"
            // -> All deletes are soft deletes.
        }

        $now = \DateTimeEx::create()->toIsoString();

        $this->startTrans();

        // Perform Soft Delete
        $this->makeInstance()
            ->filterID($taskID)
            ->update([
                'deleted' => 1,
                'updatedDate' => $now
            ]);

        // Ghi Audit log
        $auditData = [
            'id' => uniqid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.delete',
            'actorFK' => $actorID,
            'beforeData' => json_encode([
                'status' => $task->status,
                'priority' => $task->priority,
                'title' => $task->title
            ]),
            'afterData' => json_encode([
                'deleted' => 1,
                'reason' => $reason
            ]),
            'createdDate' => $now
        ];
        $this->db->insert('task_audit_log', $auditData);

        $this->completeTransOrFail();

        return result(true, ['taskID' => $taskID]);
    }

    /**
     * Story 2.1 - Cập nhật deadline task
     */
    function updateDeadline($siteID, $taskID, $actorID, $inputStartTime, $inputDueTime) {
        $task = $this->makeInstance()
            ->filterID($taskID)
            ->filterSiteFK($siteID)
            ->filterDeleted(0)
            ->getEntity();

        if (!$task->id) {
            throw new E\BadRequestException('Task không tồn tại hoặc đã bị xóa');
        }

        if ($task->status === 'Hoàn thành') {
            throw new E\ConflictException('Không thể đổi deadline của task đã Hoàn thành');
        }

        $newStartTime = $inputStartTime !== '' ? $inputStartTime : $task->startTime;
        $newDueTime = $inputDueTime;

        if ($newStartTime !== '' && $newDueTime !== '' && strtotime($newStartTime) > strtotime($newDueTime)) {
            throw new E\BadRequestException('start_time phải nhỏ hơn hoặc bằng due_time');
        }

        $nowStr = \DateTimeEx::create()->toIsoString();
        $nowTimestamp = time();
        $dueTimestamp = strtotime($newDueTime);

        $isOverdue = false;
        $isDueSoon = false;

        if ($dueTimestamp) {
            if ($nowTimestamp > $dueTimestamp) {
                $isOverdue = true;
            } else {
                // Hardcode mốc dueSoon là 24 giờ
                $diffHours = ($dueTimestamp - $nowTimestamp) / 3600;
                if ($diffHours <= 24) {
                    $isDueSoon = true;
                }
            }
        }

        $this->startTrans();

        $this->makeInstance()
            ->filterID($taskID)
            ->update([
                'startTime' => $newStartTime,
                'dueTime' => $newDueTime,
                'updatedDate' => $nowStr
            ]);

        $auditData = [
            'id' => uniqid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.update_deadline',
            'actorFK' => $actorID,
            'beforeData' => json_encode([
                'startTime' => $task->startTime,
                'dueTime' => $task->dueTime
            ]),
            'afterData' => json_encode([
                'startTime' => $newStartTime,
                'dueTime' => $newDueTime
            ]),
            'createdDate' => $nowStr
        ];
        $this->db->insert('task_audit_log', $auditData);

        $this->completeTransOrFail();

        return result(true, [
            'taskID' => $taskID,
            'startTime' => $newStartTime,
            'dueTime' => $newDueTime,
            'isOverdue' => $isOverdue,
            'isDueSoon' => $isDueSoon
        ]);
    }

    /**
     * Story 3.1 - Cập nhật tiến độ task
     */
    function updateProgress($siteID, $taskID, $actorID, $progress, $note) {
        $task = $this->makeInstance()
            ->filterID($taskID)
            ->filterSiteFK($siteID)
            ->filterDeleted(0)
            ->getEntity();

        if (!$task->id) {
            throw new E\BadRequestException('Task không tồn tại hoặc đã bị xóa');
        }

        if ($task->status === 'Hoàn thành') {
            throw new E\ConflictException('Không thể cập nhật tiến độ của task đã Hoàn thành');
        }

        if ($progress < 0 || $progress > 100) {
            throw new E\BadRequestException('Giá trị progress phải nằm trong khoảng từ 0 đến 100');
        }

        // Validate Access: Chỉ assignee hợp lệ mới update progress
        $isAssignee = $this->db->getRow("SELECT id FROM task_assignee WHERE taskFK = ? AND assigneeFK = ? AND siteFK = ? AND deleted = 0 LIMIT 1", [$taskID, $actorID, $siteID]);
        if (!$isAssignee) {
            throw new E\ForbiddenException('Chỉ người được giao việc (assignee) mới có quyền cập nhật tiến độ');
        }

        $nowStr = \DateTimeEx::create()->toIsoString();

        // Xử lý attrs JSON để lưu progress
        $attrs = [];
        if (!empty($task->attrs)) {
            $decoded = json_decode($task->attrs, true);
            if (is_array($decoded)) {
                $attrs = $decoded;
            }
        }
        $oldProgress = isset($attrs['progress']) ? (int) $attrs['progress'] : 0;
        $attrs['progress'] = $progress;

        $this->startTrans();

        // 1. Lưu log vào bảng task_progress_log
        $logID = uniqid();
        $this->db->insert('task_progress_log', [
            'id' => $logID,
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'actorFK' => $actorID,
            'progress' => $progress,
            'note' => $note,
            'createdDate' => $nowStr
        ]);

        // 2. Cập nhật attrs chứa progress mới nhất vào task
        $this->makeInstance()
            ->filterID($taskID)
            ->update([
                'attrs' => json_encode($attrs),
                'updatedDate' => $nowStr
            ]);

        // 3. Ghi Audit Log
        $auditData = [
            'id' => uniqid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.progress.update',
            'actorFK' => $actorID,
            'beforeData' => json_encode([
                'progress' => $oldProgress
            ]),
            'afterData' => json_encode([
                'progress' => $progress,
                'note' => $note,
                'logID' => $logID
            ]),
            'createdDate' => $nowStr
        ];
        $this->db->insert('task_audit_log', $auditData);

        $this->completeTransOrFail();

        return result(true, [
            'taskID' => $taskID,
            'progress' => $progress,
            'logID' => $logID
        ]);
    }

    /**
     * Story 3.2 - Truy vấn lịch sử cập nhật tiến độ (Pagination)
     */
    function getProgressHistory($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $page, $pageSize) {
        $task = $this->makeInstance()
            ->filterID($taskID)
            ->filterSiteFK($siteID)
            ->filterDeleted(0)
            ->getEntity();

        if (!$task->id) {
            throw new E\BadRequestException('Task không tồn tại hoặc đã bị xóa');
        }

        // Validate Access: 1. ManageTask Privilege, 2. Creator, 3. Assignee
        $hasAccess = false;
        if ($hasManageTaskPrivilege || $task->createdBy == $actorID) {
            $hasAccess = true;
        } else {
            $isAssignee = $this->db->getRow("SELECT id FROM task_assignee WHERE taskFK = ? AND assigneeFK = ? AND siteFK = ? AND deleted = 0 LIMIT 1", [$taskID, $actorID, $siteID]);
            if ($isAssignee) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            throw new E\ForbiddenException('Bạn không có quyền xem lịch sử tiến độ của task này');
        }

        $offset = ($page - 1) * $pageSize;

        // Pagination query
        $sql = "SELECT SQL_CALC_FOUND_ROWS id, actorFK, progress, note, createdDate 
                FROM task_progress_log 
                WHERE taskFK = ? AND siteFK = ? 
                ORDER BY createdDate DESC 
                LIMIT " . (int)$offset . ", " . (int)$pageSize;
        
        $items = $this->db->getRows($sql, [$taskID, $siteID]);
        
        // Lấy tổng số bản ghi
        $totalRow = $this->db->getRow("SELECT FOUND_ROWS() AS total");
        $total = $totalRow ? (int)$totalRow['total'] : 0;

        return result(true, [
            'taskID' => $taskID,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'items' => $items
        ]);
    }

    /**
     * Story 4.2 - Staff bắt đầu làm task
     */
    function startTask($siteID, $taskID, $actorID, $note) {
        // Validate Access: Chỉ assignee mới được bắt đầu
        $isAssignee = $this->db->getRow("SELECT id FROM task_assignee WHERE taskFK = ? AND assigneeFK = ? AND siteFK = ? AND deleted = 0 LIMIT 1", [$taskID, $actorID, $siteID]);
        if (!$isAssignee) {
            throw new E\ForbiddenException('Chỉ người được giao việc (assignee) mới có quyền bắt đầu task này');
        }

        // Chuyển trạng thái qua Guard
        $guard = TaskWorkflowGuard::makeInstance();
        return $guard->executeTransition($siteID, $taskID, $actorID, 'Đang thực hiện', $note);
    }

    /**
     * Story 4.3 - Staff gửi duyệt kết quả
     */
    function submitTask($siteID, $taskID, $actorID, $note) {
        // Validate Access: Chỉ assignee mới được nộp bài
        $isAssignee = $this->db->getRow("SELECT id FROM task_assignee WHERE taskFK = ? AND assigneeFK = ? AND siteFK = ? AND deleted = 0 LIMIT 1", [$taskID, $actorID, $siteID]);
        if (!$isAssignee) {
            throw new E\ForbiddenException('Chỉ người được giao việc (assignee) mới có quyền gửi duyệt task này');
        }

        // Chuyển trạng thái qua Guard (từ Đang thực hiện -> Chờ duyệt)
        $guard = TaskWorkflowGuard::makeInstance();
        return $guard->executeTransition($siteID, $taskID, $actorID, 'Chờ duyệt', $note);
    }

    /**
     * Story 4.4 - Manager phê duyệt task
     */
    function approveTask($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $note) {
        if (!$hasManageTaskPrivilege) {
            throw new E\ForbiddenException('Chỉ có Manager (người có quyền manageTask) mới được phê duyệt task');
        }

        // Chuyển trạng thái qua Guard (từ Chờ duyệt -> Hoàn thành)
        $guard = TaskWorkflowGuard::makeInstance();
        return $guard->executeTransition($siteID, $taskID, $actorID, 'Hoàn thành', $note);
    }

    /**
     * Story 4.5 - Manager yêu cầu làm lại task
     */
    function reworkTask($siteID, $taskID, $actorID, $hasManageTaskPrivilege, $note) {
        if (!$hasManageTaskPrivilege) {
            throw new E\ForbiddenException('Chỉ có Manager (người có quyền manageTask) mới được yêu cầu làm lại task');
        }

        // Chuyển trạng thái qua Guard (từ Chờ duyệt -> Đang thực hiện)
        $guard = TaskWorkflowGuard::makeInstance();
        return $guard->executeTransition($siteID, $taskID, $actorID, 'Đang thực hiện', $note);
    }
}
