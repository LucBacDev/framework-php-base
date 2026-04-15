CREATE TABLE user_login(
userID VARCHAR(50),
`type` VARCHAR(50),
account VARCHAR(255),
passwd VARCHAR(255)
);

ALTER TABLE user_login ADD UNIQUE INDEX idx_account(account, `type`);