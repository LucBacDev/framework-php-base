<?php

/**
 * Module Notification - Controller (Thin Controller Pattern)
 * 
 * Controller CHỈ làm 3 việc:
 * 1. Authentication/Authorization (kiểm tra quyền)
 * 2. Đọc input từ request
 * 3. Gọi Service → trả response
 * 
 * KHÔNG chứa business logic, validation, hay truy cập DB trực tiếp.
 * Tất cả logic nằm trong Service layer.
 */

namespace Company\Notification\Controller;

use Company\Auth\Auth;
use Company\Notification\Service\NotificationServiceInterface;
use Company\Notification\Service\NotificationService;

class NotificationCtrl extends \Company\MVC\Controller {

    /** @var NotificationServiceInterface */
    protected $service;

    /** @var Auth */
    protected $auth;

    function init() {
        parent::init();
        // Inject service thông qua interface
        // Trong tương lai có thể dùng DI container thay vì new trực tiếp
        $this->service = new NotificationService();
        $this->auth = Auth::getInstance();
    }

    /**
     * Lấy danh sách thông báo
     * GET /:siteID/rest/notifications
     */
    function getNotifications($siteID) {
        $this->auth->requireLogin();

        // Controller chỉ đọc input rồi chuyển cho service
        $filters = [
            'pageNo'  => $this->req->get('pageNo', 1),
            'pageSize' => $this->req->get('pageSize', 20),
            'type'    => $this->req->get('type'),
            'isRead'  => $this->req->get('isRead'),
            'userID'  => $this->req->get('userID'),
        ];

        $result = $this->service->getNotifications($siteID, $filters);
        $this->outputJSON($result);
    }

    /**
     * Lấy chi tiết 1 thông báo
     * GET /:siteID/rest/notifications/:id
     */
    function getNotification($siteID, $id) {
        $this->auth->requireLogin();

        $notification = $this->service->getNotification($siteID, $id);
        $this->outputJSON($notification);
    }

    /**
     * Tạo mới thông báo
     * POST /:siteID/rest/notifications
     */
    function createNotification($siteID) {
        $this->auth->requireLogin();
        $this->auth->requirePrivilege('manageNotification');

        $data = $this->input();
        $data['siteFK'] = $siteID;

        $result = $this->service->createNotification($data);
        $this->outputJSON($result);
    }

    /**
     * Cập nhật thông báo
     * PUT /:siteID/rest/notifications/:id
     */
    function updateNotification($siteID, $id) {
        $this->auth->requireLogin();
        $this->auth->requirePrivilege('manageNotification');

        $data = $this->input();
        $data['siteFK'] = $siteID;

        $result = $this->service->updateNotification($id, $data);
        $this->outputJSON($result);
    }

    /**
     * Xóa thông báo
     * DELETE /:siteID/rest/notifications/:id
     */
    function deleteNotification($siteID, $id) {
        $this->auth->requireLogin();
        $this->auth->requirePrivilege('manageNotification');

        $result = $this->service->deleteNotification($siteID, $id);
        $this->outputJSON($result);
    }

    /**
     * Đánh dấu thông báo đã đọc
     * POST/PUT /:siteID/rest/notifications/:id/read
     */
    function markAsRead($siteID, $id) {
        $this->auth->requireLogin();

        $result = $this->service->markAsRead($siteID, $id);
        $this->outputJSON($result);
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     * POST/PUT /:siteID/rest/notifications/read-all
     */
    function markAllAsRead($siteID) {
        $this->auth->requireLogin();

        $user = $this->auth->getUser();
        $result = $this->service->markAllAsRead($siteID, $user->id);
        $this->outputJSON($result);
    }

    /**
     * Đếm thông báo chưa đọc
     * GET /:siteID/rest/notifications/unread-count
     */
    function countUnread($siteID) {
        $this->auth->requireLogin();

        $user = $this->auth->getUser();
        $count = $this->service->countUnread($siteID, $user->id);
        $this->outputJSON(['count' => $count]);
    }
}
