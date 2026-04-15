CREATE TABLE user_privilege_list(
privGroupID VARCHAR(50),
id VARCHAR(50) PRIMARY KEY,
`name` VARCHAR(255),
`desc` TEXT
);

CREATE INDEX idx_pri ON user_privilege_list(privGroupID);