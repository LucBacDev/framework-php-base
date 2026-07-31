CREATE TABLE IF NOT EXISTS `department` (
    `id` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `code` VARCHAR(100) NOT NULL DEFAULT '',
    `parentID` VARCHAR(50) DEFAULT '0',
    `siteFK` VARCHAR(50) NOT NULL DEFAULT '',
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `noDelete` TINYINT(1) NOT NULL DEFAULT 0,
    `path` VARCHAR(1000) DEFAULT '',
    `createdDate` VARCHAR(30) DEFAULT '',
    `dbVersion` VARCHAR(20) DEFAULT '1.0.0',
    `attrs` TEXT,
    PRIMARY KEY (`id`),
    KEY `idx_department_site` (`siteFK`),
    KEY `idx_department_parent` (`parentID`),
    KEY `idx_department_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
