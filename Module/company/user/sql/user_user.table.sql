CREATE TABLE user_user(
id VARCHAR(50) PRIMARY KEY,
attrs TEXT,
deleted TINYINT DEFAULT '0',
fullname varchar(255),
depFK varchar(50),
active tinyint,
createdDate DATETIME,
dbVersion varchar(50),
siteFK VARCHAR(50),
userLinkID varchar(50),
noDelete tinyint
);

CREATE INDEX idx_fullname ON user_user(fullname);
CREATE INDEX idx_depFK ON user_user(depFK);
CREATE INDEX idx_createdDate ON user_user(createdDate);
CREATE INDEX idx_dbVersion ON user_user(dbVersion);
