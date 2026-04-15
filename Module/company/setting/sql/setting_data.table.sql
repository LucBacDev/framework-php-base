CREATE TABLE setting_data(
fieldID VARCHAR(50),
`value` TEXT,
siteFK VARCHAR(50),
PRIMARY KEY (fieldID, siteFK)
);
