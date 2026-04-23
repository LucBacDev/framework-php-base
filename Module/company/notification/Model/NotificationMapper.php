<?php

/**
 * Module Notification - Mapper (Data Access Layer THUẦN)
 * 
 * Mapper CHỈ làm nhiệm vụ truy cập database:
 * - Định nghĩa bảng và alias
 * - Cung cấp các filter methods cho query
 * - CRUD operations cơ bản
 * 
 * KHÔNG chứa:
 * - Business logic (validation, rules)
 * - Gọi service/mapper khác
 * - Xử lý phức tạp
 * 
 * Tất cả logic nghiệp vụ nằm trong Service layer.
 */

namespace Company\Notification\Model;

class NotificationMapper extends \Company\SQL\Mapper {

    public function tableName() {
        return 'notification';
    }

    public function tableAlias() {
        return 'noti';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('noti.createdDate DESC');
    }

    // ============================================================
    // FILTER METHODS - Chỉ thêm điều kiện WHERE
    // Quy tắc: luôn return $this, dùng __FUNCTION__ làm key
    // ============================================================

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

    /**
     * Tìm kiếm theo tiêu đề (LIKE)
     * @param string|null $title
     * @return $this
     */
    function filterTitle($title) {
        if ($title) {
            $this->where('noti.title LIKE ?', __FUNCTION__)
                ->setParamWhere("%$title%", __FUNCTION__);
        }
        return $this;
    }
}
