CREATE TABLE IF NOT EXISTS `task_attachment` (
    `id` VARCHAR(50) NOT NULL,
    `taskFK` VARCHAR(50) NOT NULL,
    `fileFK` VARCHAR(50) NOT NULL,
    `siteFK` VARCHAR(50) NOT NULL,
    `createdDate` VARCHAR(30) DEFAULT '',
    `deleted` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_task_attachment_task` (`taskFK`),
    KEY `idx_task_attachment_file` (`fileFK`),
    KEY `idx_task_attachment_site` (`siteFK`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
