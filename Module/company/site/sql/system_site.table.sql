CREATE TABLE system_site(
id VARCHAR(50) PRIMARY KEY,
attrs TEXT,
name VARCHAR(255),
shortName VARCHAR(255),
active TINYINT,
createdDate DATETIME,
willDeleteAt DATETIME
);
--
-- ALTER TABLE system_site ADD `name` VARCHAR(255) AS (JSON_VALUE(attrs, '$.name'));
-- ALTER TABLE system_site ADD `shortName` VARCHAR(255) AS (JSON_VALUE(attrs, '$.shortName'));
-- ALTER TABLE system_site ADD active TINYINT AS (JSON_VALUE(attrs, '$.active'));
-- ALTER TABLE system_site ADD createdDate DATETIME AS (JSON_VALUE(attrs, '$.createdDate'));
-- ALTER TABLE system_site ADD willDeleteAt DATETIME AS (JSON_VALUE(attrs, '$.willDeleteAt'));



