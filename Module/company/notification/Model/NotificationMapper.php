<?php

/**
 * Module Notification - Mapper (Data Access Layer)
 * 
 * Mapper kế thừa từ \Company\SQL\Mapper, đóng vai trò là lớp truy cập dữ liệu.
 * 
 * Bắt buộc implement:
 * - tableName(): Tên bảng trong database
 * - tableAlias(): Alias ngắn dùng trong SQL query
 * 
 * Các method có sẵn từ Mapper base:
 * - filterID($id): Lọc theo khóa chính
 * - insert($data): Thêm bản ghi
 * - update($data): Cập nhật bản ghi (BẮT BUỘC phải có WHERE)
 * - delete(): Xóa bản ghi (BẮT BUỘC phải có WHERE)
 * - getEntity(): Lấy 1 entity
 * - getEntities(): Lấy nhiều entities (trả về Collection)
 * - getEntityOrFail(): Lấy 1 entity hoặc throw NotFoundException
 * - setPage($pageNo, $pageSize): Phân trang
 * - count(&$total): Đếm tổng bản ghi
 * - startTrans() / completeTransOrFail(): Transaction
 * - makeInstance(): Tạo instance mới (factory pattern)
 * - isExists() / existsOrFail(): Kiểm tra tồn tại
 * 
 * Quy ước đặt tên filter:
 * - filterXXX(): Thêm điều kiện WHERE, luôn return $this để hỗ trợ method chaining
 * - setLoadXXX(): Bật chế độ load quan hệ (eager loading), return $this
 * - makeEntity(): Override để tùy chỉnh cách tạo Entity từ raw data
 */

namespace Company\Notification\Model;

use Company\Exception\BadRequestException;

class NotificationMapper extends \Company\SQL\Mapper {

    public function tableName() {
        return 'notification';
    }

    public function tableAlias() {
        return 'noti';
    }

    function __construct() {
        parent::__construct();
        // Sắp xếp mặc định: mới nhất lên đầu
        $this->orderBy('noti.createdDate DESC');
    }

    /**
     * Tạo mới hoặc cập nhật thông báo
     * Pattern chuẩn: kiểm tra $id để phân biệt insert/update
     */
    function updateNotification($id, $input) {
        $isInsert = !$id;

        // Validate required fields
        $required = ['title', 'siteFK'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field] ?? ''))) {
                throw new BadRequestException("Thiếu trường bắt buộc: " . $field);
            }
        }

        // Lọc các trường cho phép cập nhật
        $fields = ['title', 'content', 'type', 'siteFK', 'userID', 'isRead'];
        $updateData = [];
        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = $input[$field];
            }
        }

        $this->startTrans();

        if ($isInsert) {
            $id = $updateData['id'] = uid();
            $updateData['createdDate'] = \DateTimeEx::create()->toIsoString();
            $updateData['isRead'] = 0;
            $this->insert($updateData);
        } else {
            $this->makeInstance()
                ->filterID($id)
                ->existsOrFail(new BadRequestException("Notification not found: $id"));

            $this->makeInstance()
                ->filterID($id)
                ->update($updateData);
        }

        $this->completeTransOrFail();

        return result(true, ['id' => $id]);
    }

    /**
     * Lọc theo site
     * @param string $siteFK
     * @return $this
     */
    function filterSiteFK($siteFK) {
        if ($siteFK) {
            $this->where('noti.siteFK = ?', __FUNCTION__)
                ->setParamWhere($siteFK, __FUNCTION__);
        }
        return $this;
    }

    /**
     * Lọc theo loại thông báo
     * @param string|null $type
     * @return $this
     */
    function filterType($type) {
        if ($type) {
            $this->where('noti.type = ?', __FUNCTION__)
                ->setParamWhere($type, __FUNCTION__);
        }
        return $this;
    }

    /**
     * Lọc theo trạng thái đọc
     * @param int|null $isRead 0=chưa đọc, 1=đã đọc
     * @return $this
     */
    function filterIsRead($isRead) {
        if ($isRead !== null && $isRead !== '') {
            $this->where('noti.isRead = ?', __FUNCTION__)
                ->setParamWhere((int)$isRead, __FUNCTION__);
        }
        return $this;
    }

    /**
     * Lọc theo user nhận thông báo
     * @param string $userID
     * @return $this
     */
    function filterUserID($userID) {
        if ($userID) {
            $this->where('noti.userID = ?', __FUNCTION__)
                ->setParamWhere($userID, __FUNCTION__);
        }
        return $this;
    }
}
