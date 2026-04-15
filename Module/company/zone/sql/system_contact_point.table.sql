CREATE TABLE system_contact_point(
id VARCHAR(50) PRIMARY KEY,
lanAddress VARCHAR(255),
internetAddress VARCHAR(255),
zoneID VARCHAR(50)
);

CREATE INDEX idx_zone ON system_contact_point(zoneID);