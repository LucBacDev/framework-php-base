CREATE TABLE user_role_user(
roleID VARCHAR(50),
userID VARCHAR(50),
`default` TINYINT(4) DEFAULT 0,
siteFK VARCHAR(50),
PRIMARY KEY (userID, roleID)
);

CREATE INDEX idx_role_user ON user_role_user(roleID)