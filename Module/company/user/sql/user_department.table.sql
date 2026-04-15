CREATE TABLE user_department(
id VARCHAR(50) primary key,
attrs TEXT,
parentID varchar(50),
code varchar(50),
`path` varchar(500),
name varchar(255),
createdDate DATETIME,
dbVersion varchar(50),
active tinyint,
siteFK varchar(50),
noDelete tinyint default 0
);

CREATE INDEX idx_name ON user_department(`name`);
CREATE INDEX idx_parentID ON user_department(`parentID`);
CREATE INDEX idx_code ON user_department(`code`);
CREATE INDEX idx_createdDate ON user_department(`createdDate`);
CREATE INDEX idx_dbVersion ON user_department(`dbVersion`);
CREATE INDEX idx_path ON user_department(`path`);
CREATE INDEX idx_active ON user_department(`active`);