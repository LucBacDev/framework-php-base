CREATE TABLE user_role_privilege(
roleID VARCHAR(50),
privilegeID VARCHAR(50),
PRIMARY KEY (roleID, privilegeID)
);

CREATE INDEX idx_role_priv ON user_role_privilege(roleID);