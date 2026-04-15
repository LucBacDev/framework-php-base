CREATE TABLE user_user_privilege(
userID VARCHAR(50),
privilegeID VARCHAR(50),
PRIMARY KEY (userID, privilegeID)
);

CREATE INDEX idx_user_priv ON user_user_privilege(privilegeID);