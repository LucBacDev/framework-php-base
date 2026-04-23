CREATE TABLE IF NOT EXISTS `task_notification_job` (
    `id` VARCHAR(50) NOT NULL,
    `siteFK` VARCHAR(50) NOT NULL,
    `taskFK` VARCHAR(50) NOT NULL,
    `type` VARCHAR(30) NOT NULL COMMENT 'dueSoon, overdue',
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, success, failed',
    `attempts` INT NOT NULL DEFAULT 0,
    `nextRunTime` VARCHAR(30) NOT NULL,
    `createdDate` VARCHAR(30) NOT NULL,
    `updatedDate` VARCHAR(30) DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_tnj_status` (`status`, `nextRunTime`),
    KEY `idx_tnj_task` (`taskFK`),
    KEY `idx_tnj_site` (`siteFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
