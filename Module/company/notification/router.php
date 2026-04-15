<?php

/**
 * Module Notification - router.php
 * 
 * File này được framework tự động load bởi Router::getRoutes().
 * Framework quét tất cả thư mục trong Module/ và load file router.php nếu tồn tại.
 * 
 * Cách đăng ký route:
 * - Sử dụng MvcContext(path, method, controller, action, filter)
 * - path: Đường dẫn URL, hỗ trợ tham số động dạng :param và optional (/:param)
 * - method: HTTP methods (GET, POST, PUT, DELETE hoặc * cho tất cả)
 * - controller: Tên class controller đầy đủ namespace
 * - action: Tên method trong controller sẽ xử lý request
 * - filter: RouterFilter để lọc route theo URI hoặc loại request (REST/non-REST)
 */

namespace Company\Notification;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$ctrl = "\\Company\\Notification\\Controller\\NotificationCtrl";

// Lấy danh sách thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications', 'GET', $ctrl, 'getNotifications', new RouterFilter("/rest/notifications"))
);

// Lấy chi tiết 1 thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/:id', 'GET', $ctrl, 'getNotification', new RouterFilter("/rest/notifications"))
);

// Tạo mới / cập nhật thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications(/:id)', 'POST,PUT', $ctrl, 'updateNotification', new RouterFilter("/rest/notifications"))
);

// Xóa thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/:id', 'DELETE', $ctrl, 'deleteNotification', new RouterFilter("/rest/notifications"))
);

// Đánh dấu đã đọc
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/:id/read', 'POST,PUT', $ctrl, 'markAsRead', new RouterFilter("/rest/notifications"))
);

// Đánh dấu tất cả đã đọc
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/read-all', 'POST,PUT', $ctrl, 'markAllAsRead', new RouterFilter("/rest/notifications"))
);
