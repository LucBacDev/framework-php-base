CREATE TABLE IF NOT EXISTS `notification` (
    `id` VARCHAR(50) NOT NULL,
    `title` VARCHAR(500) NOT NULL DEFAULT '',
    `content` TEXT,
    `type` VARCHAR(50) DEFAULT 'system' COMMENT 'Loại thông báo: system, study, report, ...',
    `siteFK` VARCHAR(50) NOT NULL DEFAULT '',
    `userID` VARCHAR(50) DEFAULT NULL COMMENT 'User nhận thông báo, NULL = tất cả',
    `isRead` TINYINT(1) NOT NULL DEFAULT 0,
    `createdDate` VARCHAR(30) DEFAULT '',
    `attrs` TEXT COMMENT 'Trường JSON mở rộng',
    PRIMARY KEY (`id`),
    KEY `idx_notification_site` (`siteFK`),
    KEY `idx_notification_user` (`userID`),
    KEY `idx_notification_type` (`type`),
    KEY `idx_notification_read` (`isRead`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
