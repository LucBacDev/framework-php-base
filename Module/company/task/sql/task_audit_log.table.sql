CREATE TABLE IF NOT EXISTS `task_audit_log` (
    `id` VARCHAR(50) NOT NULL,
    `taskFK` VARCHAR(50) NOT NULL DEFAULT '',
    `siteFK` VARCHAR(50) NOT NULL DEFAULT '',
    `action` VARCHAR(100) NOT NULL DEFAULT '',
    `actorFK` VARCHAR(50) DEFAULT '',
    `beforeData` LONGTEXT,
    `afterData` LONGTEXT,
    `createdDate` VARCHAR(30) DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_task_audit_task` (`taskFK`),
    KEY `idx_task_audit_site` (`siteFK`),
    KEY `idx_task_audit_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
