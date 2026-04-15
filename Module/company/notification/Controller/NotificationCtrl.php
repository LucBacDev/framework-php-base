<?php

/**
 * Module Notification - Controller
 * 
 * Controller kế thừa từ \Company\MVC\Controller.
 * 
 * Các thuộc tính/method có sẵn từ Controller base:
 * - $this->req: Slim\Http\Request - đọc query params, headers
 * - $this->resp: Slim\Http\Response - ghi response body, headers
 * - $this->context: MvcContext - thông tin route hiện tại
 * - $this->input($key, $default): Đọc JSON body từ request
 * - $this->isRest(): Kiểm tra request có phải REST không
 * - $this->outputJSON($data): Trả về JSON response
 * 
 * Quy ước:
 * - Mỗi action tương ứng với 1 route trong router.php
 * - Tham số action trùng với tham số động trong route path
 *   VD: route '/:siteID/rest/notifications/:id' → action getNotification($siteID, $id)
 * - Gọi Auth để kiểm tra quyền trước khi xử lý logic
 */

namespace Company\Notification\Controller;

use Company\Auth\Auth;
use Company\Exception\BadRequestException;
use Company\Notification\Model\NotificationMapper;

class NotificationCtrl extends \Company\MVC\Controller {

    /** @var NotificationMapper */
    protected $mapper;

    /** @var Auth */
    protected $auth;

    /**
     * init() được gọi sau __construct, dùng để khởi tạo các dependency.
     * Override method này thay vì __construct.
     */
    function init() {
        parent::init();
        $this->mapper = NotificationMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * Lấy danh sách thông báo
     * GET /:siteID/rest/notifications
     */
    function getNotifications($siteID) {
        $this->auth->requireLogin();

        $pageNo = $this->req->get('pageNo', 1);
        $pageSize = $this->req->get('pageSize', 20);
        $type = $this->req->get('type');
        $isRead = $this->req->get('isRead');

        $mapper = NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterType($type)
            ->filterIsRead($isRead)
            ->setPage($pageNo, $pageSize);

        $total = 0;
        $mapper->count($total);
        $notifications = $mapper->getEntities();

        $this->outputJSON([
            'data' => $notifications->toArray(),
            'total' => $total,
            'pageNo' => $pageNo,
            'pageSize' => $pageSize
        ]);
    }

    /**
     * Lấy chi tiết 1 thông báo
     * GET /:siteID/rest/notifications/:id
     */
    function getNotification($siteID, $id) {
        $this->auth->requireLogin();

        $notification = NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->getEntityOrFail();

        $this->outputJSON($notification);
    }

    /**
     * Tạo mới hoặc cập nhật thông báo
     * POST/PUT /:siteID/rest/notifications(/:id)
     */
    function updateNotification($siteID, $id = null) {
        $this->auth->requireLogin();
        $this->auth->requirePrivilege('manageNotification');

        $data = $this->input();
        $data['siteFK'] = $siteID;

        $result = $this->mapper->updateNotification($id, $data);
        $this->outputJSON($result);
    }

    /**
     * Xóa thông báo
     * DELETE /:siteID/rest/notifications/:id
     */
    function deleteNotification($siteID, $id) {
        $this->auth->requireLogin();
        $this->auth->requirePrivilege('manageNotification');

        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->existsOrFail(new BadRequestException("Notification not found: $id"));

        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->delete();

        $this->outputJSON(result(true));
    }

    /**
     * Đánh dấu thông báo đã đọc
     * POST/PUT /:siteID/rest/notifications/:id/read
     */
    function markAsRead($siteID, $id) {
        $this->auth->requireLogin();

        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->update(['isRead' => 1]);

        $this->outputJSON(result(true));
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     * POST/PUT /:siteID/rest/notifications/read-all
     */
    function markAllAsRead($siteID) {
        $this->auth->requireLogin();

        $user = $this->auth->getUser();
        NotificationMapper::makeInstance()
            ->filterSiteFK($siteID)
            ->filterUserID($user->id)
            ->filterIsRead(0)
            ->update(['isRead' => 1]);

        $this->outputJSON(result(true));
    }
}
