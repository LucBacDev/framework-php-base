<?php

/**
 * Module Notification - construct.php
 * 
 * File này được framework tự động load khi khởi động (Bootstrap::registerComponentHook).
 * Dùng để đăng ký các hook/trigger cho module.
 * 
 * Lưu ý: File này PHẢI có nội dung (không được rỗng) thì mới được framework load.
 * Nếu module không cần hook nào, vẫn phải khai báo namespace.
 */

namespace Company\Notification;

use Company\MVC\Trigger;

// Ví dụ: Đăng ký hook khi Bootstrap hoàn tất
// Trigger::register('Bootstrap/completed', function($config) {
//     // Khởi tạo các cấu hình cho module notification
// });
