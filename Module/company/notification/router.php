<?php

/**
 * Module Notification - router.php
 * 
 * File này được framework tự động load bởi Router::getRoutes().
 * Framework quét tất cả thư mục trong Module/ và load file router.php nếu tồn tại.
 */

namespace Company\Notification;

use Company\MVC\MvcContext as MVC;
use Company\MVC\Router as R;
use Company\MVC\RouterFilter;

$ctrl = "\\Company\\Notification\\Controller\\NotificationCtrl";

// Đếm thông báo chưa đọc (đặt TRƯỚC route /:id để không bị match nhầm)
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/unread-count', 'GET', $ctrl, 'countUnread', new RouterFilter("/rest/notifications"))
);

// Đánh dấu tất cả đã đọc (đặt TRƯỚC route /:id để không bị match nhầm)
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/read-all', 'POST,PUT', $ctrl, 'markAllAsRead', new RouterFilter("/rest/notifications"))
);

// Lấy danh sách thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications', 'GET', $ctrl, 'getNotifications', new RouterFilter("/rest/notifications"))
);

// Tạo mới thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications', 'POST', $ctrl, 'createNotification', new RouterFilter("/rest/notifications"))
);

// Lấy chi tiết 1 thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/:id', 'GET', $ctrl, 'getNotification', new RouterFilter("/rest/notifications"))
);

// Cập nhật thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/:id', 'PUT', $ctrl, 'updateNotification', new RouterFilter("/rest/notifications"))
);

// Xóa thông báo
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/:id', 'DELETE', $ctrl, 'deleteNotification', new RouterFilter("/rest/notifications"))
);

// Đánh dấu đã đọc
R::getInstance()->addRoute(
    new MVC('/:siteID/rest/notifications/:id/read', 'POST,PUT', $ctrl, 'markAsRead', new RouterFilter("/rest/notifications"))
);
