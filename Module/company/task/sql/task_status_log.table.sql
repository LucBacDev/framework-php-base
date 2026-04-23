CREATE TABLE IF NOT EXISTS `task_status_log` (
    `id` VARCHAR(50) NOT NULL,
    `taskFK` VARCHAR(50) NOT NULL,
    `siteFK` VARCHAR(50) NOT NULL,
    `actorFK` VARCHAR(50) NOT NULL,
    `oldStatus` VARCHAR(50) NOT NULL,
    `newStatus` VARCHAR(50) NOT NULL,
    `note` TEXT,
    `createdDate` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tsl_task` (`taskFK`),
    KEY `idx_tsl_site` (`siteFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
