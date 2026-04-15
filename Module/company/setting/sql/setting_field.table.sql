CREATE TABLE setting_field(
id VARCHAR(50) PRIMARY KEY,
formID VARCHAR(50),
label VARCHAR(255),
dataType VARCHAR(50),
isGlobal tinyint default 0,
defaultVal TEXT,
`desc` TEXT
);
