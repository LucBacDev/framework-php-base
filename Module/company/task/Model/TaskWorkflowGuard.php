<?php
namespace Company\Task\Model;

use Company\SQL\Mapper;

class TaskWorkflowGuard extends Mapper {

    /**
     * Khai báo bảng để Mapper có thể sử dụng $this->db tiện lợi
     */
    function tableName() {
        return 'task_status_log';
    }

    function tableAlias() {
        return 'tsl';
    }

    function filterSiteFK($siteID) {
        $this->where("tsl.siteFK = ?", __FUNCTION__)
            ->setParamWhere($siteID, __FUNCTION__);
        return $this;
    }

    function filterDeleted($deleted = 0) {
        $this->where("tsl.deleted = ?", __FUNCTION__)
            ->setParamWhere($deleted, __FUNCTION__);
        return $this;
    }

    function filterID($id) {
        $this->where("tsl.id = ?", __FUNCTION__)
            ->setParamWhere($id, __FUNCTION__);
        return $this;
    }

    /**
     * Danh sách các luật chuyển trạng thái hợp lệ (Edges)
     * Key = From Status, Value = Array of To Statuses
     */
    protected $validTransitions = [
        'Mới' => ['Đang thực hiện'],
        'Đang thực hiện' => ['Chờ duyệt'],
        'Chờ duyệt' => ['Hoàn thành', 'Đang thực hiện'] // Đang thực hiện có nghĩa là Rework
    ];

    /**
     * Kiểm tra trạng thái có hợp lệ để chuyển không
     */
    function canTransition($fromStatus, $toStatus) {
        if (!isset($this->validTransitions[$fromStatus])) {
            return false;
        }

        if (!in_array($toStatus, $this->validTransitions[$fromStatus])) {
            return false;
        }

        return true;
    }

    /**
     * Thực thi việc chuyển trạng thái và ghi log an toàn
     */
    function executeTransition($siteID, $taskID, $actorID, $newStatus, $note = '') {
        $taskMapper = TaskMapper::makeInstance();
        $task = $taskMapper->filterID($taskID)
            ->filterSiteFK($siteID)
            ->filterDeleted(0)
            ->getEntity();

        if (!$task->id) {
            throw new \Company\Exception\BadRequestException('Task không tồn tại hoặc đã bị xóa');
        }

        $oldStatus = $task->status;

        // Bỏ qua nếu không đổi trạng thái
        if ($oldStatus === $newStatus) {
            return result(true, ['message' => 'Trạng thái không thay đổi']);
        }

        // Validate Guard
        if (!$this->canTransition($oldStatus, $newStatus)) {
            throw new \Company\Exception\ConflictException("Không được phép chuyển trạng thái từ '$oldStatus' sang '$newStatus'");
        }

        $nowStr = \DateTimeEx::create()->toIsoString();

        $this->startTrans();

        // 1. Cập nhật task
        $taskMapper->update([
            'status' => $newStatus,
            'updatedDate' => $nowStr
        ], 'id = ?', [$taskID]);

        // 2. Ghi Status Log
        $logID = uniqid();
        $this->db->insert('task_status_log', [
            'id' => $logID,
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'actorFK' => $actorID,
            'oldStatus' => $oldStatus,
            'newStatus' => $newStatus,
            'note' => $note,
            'createdDate' => $nowStr
        ]);

        // 3. Ghi Audit Log
        $auditData = [
            'id' => uniqid(),
            'taskFK' => $taskID,
            'siteFK' => $siteID,
            'action' => 'task.status.transition',
            'actorFK' => $actorID,
            'beforeData' => json_encode(['status' => $oldStatus]),
            'afterData' => json_encode([
                'status' => $newStatus,
                'note' => $note,
                'logID' => $logID
            ]),
            'createdDate' => $nowStr
        ];
        $this->db->insert('task_audit_log', $auditData);

        $this->completeTransOrFail();

        return result(true, [
            'taskID' => $taskID,
            'oldStatus' => $oldStatus,
            'newStatus' => $newStatus,
            'logID' => $logID
        ]);
    }
}
