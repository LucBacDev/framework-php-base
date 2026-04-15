CREATE TABLE dict_item(
id VARCHAR(50) PRIMARY KEY,
`value` VARCHAR(255),
collectionID VARCHAR(50),
description TEXT,
attrs TEXT,
sort int,
FOREIGN KEY (collectionID) REFERENCES dict_collection(id) ON DELETE CASCADE
);

