<?php

/**
 * Module Notification - install.php
 * 
 * File này được gọi khi chạy cài đặt hệ thống (php install/install.php).
 * Dùng để:
 * - Tạo bảng database (initDatabase)
 * - Tạo bản ghi module trong system_module (checkExistsOrCreateModuleRecord)
 * - Seed dữ liệu mặc định (privileges, settings, ...)
 */

use Company\MVC\Module;
use Company\SQL\DB;

$module = new Module("company/notification");

// Tạo bảng từ các file *.table.sql trong thư mục sql/
$module->initDatabase();

// Đăng ký module vào bảng system_module
$module->checkExistsOrCreateModuleRecord();
