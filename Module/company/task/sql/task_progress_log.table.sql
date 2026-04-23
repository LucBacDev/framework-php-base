CREATE TABLE IF NOT EXISTS `task_progress_log` (
    `id` VARCHAR(50) NOT NULL,
    `taskFK` VARCHAR(50) NOT NULL,
    `siteFK` VARCHAR(50) NOT NULL,
    `actorFK` VARCHAR(50) NOT NULL,
    `progress` INT NOT NULL DEFAULT 0,
    `note` TEXT,
    `createdDate` VARCHAR(30) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tpl_task` (`taskFK`),
    KEY `idx_tpl_site` (`siteFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
