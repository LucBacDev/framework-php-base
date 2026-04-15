CREATE TABLE user_role_list (
id VARCHAR(50) PRIMARY KEY,
`name` varchar(255),
siteFK VARCHAR(50),
attrs TEXT
);

ALTER TABLE user_role_list ADD noDelete TINYINT AS (JSON_VALUE(attrs, '$.noDelete'));
