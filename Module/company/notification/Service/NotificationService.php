<?php

/**
 * NotificationService - Implementation tầng nghiệp vụ
 * 
 * Class này chứa TOÀN BỘ business logic của module Notification.
 * 
 * Nguyên tắc:
 * - Service KHÔNG biết về HTTP request/response (không dùng $this->req, $this->resp)
 * - Service KHÔNG xử lý authentication (auth nằm ở Controller)
 * - Service chỉ nhận data thuần (array/string/int) và trả về data thuần
 * - Service gọi Mapper để truy cập DB, KHÔNG viết SQL trực tiếp
 * - Tất cả validation nghiệp vụ nằm ở đây
 * 
 * So sánh với kiến trúc cũ:
 * - CŨ: Controller → Mapper (logic trộn lẫn ở cả 2 chỗ)
 * - MỚI: Controller → Service → Mapper (logic tập trung ở Service)
 */

namespace Company\Notification\Service;

use Company\Exception\BadRequestException;
use Company\Notification\Model\NotificationMapper;

class NotificationService implements NotificationServiceInterface {

    /** @var NotificationMapper */
    protected $mapper;

    /** Các loại thông báo hợp lệ */
    const VALID_TYPES = ['system', 'study', 'report', 'alert', 'reminder'];

    function __construct() {
        $this->mapper = NotificationMapper::makeInstance();
    }

    /**
     * Lấy danh sách thông báo có phân trang
     */
    function getNotifications($siteID, array $filters = []) {
        $pageNo = arrData($filters, 'pageNo', 1);
        $pageSize = arrData($filters, 'pageSize', 20);
        $type = arrData($filters, 'type');
        $isRead = arrData($filters, 'isRead');
        $userID = arrData($filters, 'userID');

        $mapper = NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterType($type)
            ->filterIsRead($isRead)
            ->filterUserID($userID)
            ->setPage($pageNo, $pageSize);

        $total = 0;
        $mapper->count($total);
        $notifications = $mapper->getEntities();

        return [
            'data' => $notifications->toArray(),
            'total' => $total,
            'pageNo' => (int) $pageNo,
            'pageSize' => (int) $pageSize
        ];
    }

    /**
     * Lấy chi tiết 1 thông báo
     */
    function getNotification($siteID, $id) {
        return NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->getEntityOrFail();
    }

    /**
     * Tạo mới thông báo
     * 
     * Validation rules:
     * - title: bắt buộc, không rỗng
     * - siteFK: bắt buộc
     * - type: phải nằm trong danh sách hợp lệ (nếu có)
     */
    function createNotification(array $data) {
        // Validate required fields
        $this->validateRequired($data, ['title', 'siteFK']);

        // Validate type nếu có
        $this->validateType(arrData($data, 'type'));

        // Chuẩn bị dữ liệu
        $insertData = $this->prepareData($data);
        $insertData['id'] = uid();
        $insertData['createdDate'] = \DateTimeEx::create()->toIsoString();
        $insertData['isRead'] = 0;

        // Lưu vào DB
        $this->mapper->startTrans();
        $this->mapper->insert($insertData);
        $this->mapper->completeTransOrFail();

        return result(true, ['id' => $insertData['id']]);
    }

    /**
     * Cập nhật thông báo
     */
    function updateNotification($id, array $data) {
        // Kiểm tra tồn tại
        NotificationMapper::makeInstance()
            ->filterID($id)
            ->existsOrFail(new BadRequestException("Notification not found: $id"));

        // Validate type nếu có
        if (isset($data['type'])) {
            $this->validateType($data['type']);
        }

        // Chuẩn bị và cập nhật
        $updateData = $this->prepareData($data);

        $this->mapper->startTrans();
        NotificationMapper::makeInstance()
            ->filterID($id)
            ->update($updateData);
        $this->mapper->completeTransOrFail();

        return result(true, ['id' => $id]);
    }

    /**
     * Xóa thông báo
     */
    function deleteNotification($siteID, $id) {
        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->existsOrFail(new BadRequestException("Notification not found: $id"));

        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->delete();

        return result(true);
    }

    /**
     * Đánh dấu 1 thông báo đã đọc
     */
    function markAsRead($siteID, $id) {
        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->existsOrFail(new BadRequestException("Notification not found: $id"));

        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->update(['isRead' => 1]);

        return result(true);
    }

    /**
     * Đánh dấu tất cả thông báo của user đã đọc
     */
    function markAllAsRead($siteID, $userID) {
        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterUserID($userID)
            ->filterIsRead(0)
            ->update(['isRead' => 1]);

        return result(true);
    }

    /**
     * Đếm số thông báo chưa đọc
     */
    function countUnread($siteID, $userID) {
        $total = 0;
        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterUserID($userID)
            ->filterIsRead(0)
            ->count($total);

        return (int) $total;
    }

    // ============================================================
    // PRIVATE METHODS - Logic nội bộ
    // ============================================================

    /**
     * Validate các trường bắt buộc
     * 
     * @param array $data     Dữ liệu cần kiểm tra
     * @param array $required Danh sách trường bắt buộc
     * @throws BadRequestException
     */
    private function validateRequired(array $data, array $required) {
        foreach ($required as $field) {
            if (!isset($data[$field]) || !strlen(trim($data[$field]))) {
                throw new BadRequestException("Thiếu trường bắt buộc: $field");
            }
        }
    }

    /**
     * Validate loại thông báo
     * 
     * @param string|null $type
     * @throws BadRequestException
     */
    private function validateType($type) {
        if ($type && !in_array($type, self::VALID_TYPES)) {
            throw new BadRequestException(
                "Loại thông báo không hợp lệ: $type. Cho phép: " . implode(', ', self::VALID_TYPES)
            );
        }
    }

    /**
     * Lọc và chuẩn bị dữ liệu an toàn cho DB
     * Chỉ giữ lại các trường cho phép, bỏ hết field lạ
     * 
     * @param array $data Raw input
     * @return array Clean data
     */
    private function prepareData(array $data) {
        $allowedFields = ['title', 'content', 'type', 'siteFK', 'userID'];
        $clean = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $clean[$field] = $data[$field];
            }
        }
        return $clean;
    }
}
