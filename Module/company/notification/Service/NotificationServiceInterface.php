<?php

/**
 * NotificationServiceInterface - Giao diện tầng nghiệp vụ
 * 
 * Interface định nghĩa tất cả operations nghiệp vụ của module Notification.
 * 
 * Lợi ích:
 * - Controller chỉ phụ thuộc vào interface, không phụ thuộc vào implementation
 * - Dễ dàng thay đổi implementation (VD: từ MySQL sang Elasticsearch)
 * - Dễ viết unit test (mock interface)
 * - Rõ ràng: nhìn vào interface biết module làm được gì
 */

namespace Company\Notification\Service;

interface NotificationServiceInterface {

    /**
     * Lấy danh sách thông báo có phân trang
     * 
     * @param string $siteID   Site hiện tại
     * @param array  $filters  Bộ lọc: type, isRead, userID, pageNo, pageSize
     * @return array ['data' => [], 'total' => int, 'pageNo' => int, 'pageSize' => int]
     */
    function getNotifications($siteID, array $filters = []);

    /**
     * Lấy chi tiết 1 thông báo
     * 
     * @param string $siteID  Site hiện tại
     * @param string $id      ID thông báo
     * @return \Company\Entity\Entity
     * @throws \Company\Exception\NotFoundException Nếu không tìm thấy
     */
    function getNotification($siteID, $id);

    /**
     * Tạo mới thông báo
     * 
     * @param array $data  Dữ liệu: title (bắt buộc), content, type, siteFK, userID
     * @return array ['status' => bool, 'data' => ['id' => string]]
     * @throws \Company\Exception\BadRequestException Nếu thiếu field bắt buộc
     */
    function createNotification(array $data);

    /**
     * Cập nhật thông báo
     * 
     * @param string $id    ID thông báo cần cập nhật
     * @param array  $data  Dữ liệu cập nhật
     * @return array ['status' => bool, 'data' => ['id' => string]]
     * @throws \Company\Exception\BadRequestException Nếu không tìm thấy
     */
    function updateNotification($id, array $data);

    /**
     * Xóa thông báo
     * 
     * @param string $siteID  Site hiện tại
     * @param string $id      ID thông báo cần xóa
     * @return array ['status' => bool]
     * @throws \Company\Exception\BadRequestException Nếu không tìm thấy
     */
    function deleteNotification($siteID, $id);

    /**
     * Đánh dấu 1 thông báo đã đọc
     * 
     * @param string $siteID  Site hiện tại
     * @param string $id      ID thông báo
     * @return array ['status' => bool]
     */
    function markAsRead($siteID, $id);

    /**
     * Đánh dấu tất cả thông báo của user đã đọc
     * 
     * @param string $siteID  Site hiện tại
     * @param string $userID  ID user
     * @return array ['status' => bool]
     */
    function markAllAsRead($siteID, $userID);

    /**
     * Đếm số thông báo chưa đọc của user
     * 
     * @param string $siteID  Site hiện tại
     * @param string $userID  ID user
     * @return int
     */
    function countUnread($siteID, $userID);
}
