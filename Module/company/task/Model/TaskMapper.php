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
}
