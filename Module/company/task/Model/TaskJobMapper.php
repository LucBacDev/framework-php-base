<?php
namespace Company\Task\Model;

use Company\SQL\Mapper;

class TaskJobMapper extends Mapper {
    
    function tableName() {
        return 'task_notification_job';
    }

    function tableAlias() {
        return 'tnj';
    }

    /**
     * Quét các task sắp quá hạn và đã quá hạn
     */
    function scanDeadline($siteID) {
        $now = time();
        $nowStr = \DateTimeEx::create()->toIsoString();

        // Lấy tất cả task chưa hoàn thành và chưa bị xóa
        $sql = "SELECT * FROM task WHERE siteFK = ? AND status != 'Hoàn thành' AND deleted = 0 AND dueTime != ''";
        $tasks = $this->db->getRows($sql, [$siteID]);

        $queuedCount = 0;

        foreach ($tasks as $t) {
            $dueTimestamp = strtotime($t['dueTime']);
            if (!$dueTimestamp) continue;

            $attrs = [];
            if (!empty($t['attrs'])) {
                $decoded = json_decode($t['attrs'], true);
                if (is_array($decoded)) {
                    $attrs = $decoded;
                }
            }

            $isOverdue = false;
            $isDueSoon = false;

            if ($now > $dueTimestamp) {
                $isOverdue = true;
            } else {
                $diffHours = ($dueTimestamp - $now) / 3600;
                if ($diffHours <= 24) {
                    $isDueSoon = true;
                }
            }

            $needUpdate = false;
            $jobType = '';

            if ($isOverdue && empty($attrs['notifiedOverdue'])) {
                $jobType = 'overdue';
                $attrs['notifiedOverdue'] = true;
                $needUpdate = true;
            } elseif ($isDueSoon && empty($attrs['notifiedDueSoon'])) {
                $jobType = 'dueSoon';
                $attrs['notifiedDueSoon'] = true;
                $needUpdate = true;
            }

            if ($needUpdate && $jobType) {
                $this->startTrans();

                // Cập nhật attrs cho task
                $this->db->update('task', ['attrs' => json_encode($attrs)], 'id = ?', [$t['id']]);

                // Insert queue
                $jobID = uniqid();
                $jobData = [
                    'id' => $jobID,
                    'siteFK' => $siteID,
                    'taskFK' => $t['id'],
                    'type' => $jobType,
                    'status' => 'pending',
                    'attempts' => 0,
                    'nextRunTime' => $nowStr, // Có thể chạy ngay
                    'createdDate' => $nowStr
                ];
                $this->db->insert('task_notification_job', $jobData);

                $this->completeTransOrFail();
                $queuedCount++;
            }
        }

        return result(true, [
            'message' => 'Quét thành công',
            'queued' => $queuedCount
        ]);
    }

    /**
     * Gửi notification và xử lý retry
     */
    function sendNotification($siteID) {
        $now = time();
        $nowStr = \DateTimeEx::create()->toIsoString();

        // Lấy các job pending đến thời điểm chạy
        $sql = "SELECT * FROM task_notification_job WHERE siteFK = ? AND status = 'pending' AND nextRunTime <= ?";
        $jobs = $this->db->getRows($sql, [$siteID, $nowStr]);

        $successCount = 0;
        $failedCount = 0;

        foreach ($jobs as $job) {
            // Giả lập thao tác gửi thông báo (có tỷ lệ thành công là 90%)
            $sendSuccess = (rand(1, 100) <= 90); 

            $this->startTrans();

            if ($sendSuccess) {
                $this->db->update('task_notification_job', [
                    'status' => 'success',
                    'updatedDate' => $nowStr
                ], 'id = ?', [$job['id']]);
                $successCount++;
            } else {
                $attempts = (int)$job['attempts'] + 1;
                $status = 'pending';
                $nextRunTime = $nowStr;

                if ($attempts == 1) {
                    $nextRunTime = date('Y-m-d\TH:i:sP', $now + 60); // +1 min
                } elseif ($attempts == 2) {
                    $nextRunTime = date('Y-m-d\TH:i:sP', $now + 5 * 60); // +5 min
                } elseif ($attempts == 3) {
                    $nextRunTime = date('Y-m-d\TH:i:sP', $now + 15 * 60); // +15 min
                } else {
                    // Sau 3 lần retry (nghĩa là lần thứ 4 chạy thất bại)
                    $status = 'failed';
                }

                $this->db->update('task_notification_job', [
                    'status' => $status,
                    'attempts' => $attempts,
                    'nextRunTime' => $nextRunTime,
                    'updatedDate' => $nowStr
                ], 'id = ?', [$job['id']]);
                
                $failedCount++;
            }

            $this->completeTransOrFail();
        }

        return result(true, [
            'message' => 'Xử lý queue thành công',
            'processed' => count($jobs),
            'success' => $successCount,
            'failed' => $failedCount
        ]);
    }
}
