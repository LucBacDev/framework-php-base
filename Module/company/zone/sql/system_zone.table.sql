CREATE TABLE system_zone(
id VARCHAR(50) PRIMARY KEY,
`name` VARCHAR(255),
attrs text
);

ALTER TABLE system_zone
    ADD masterNode varchar(255);

ALTER TABLE system_zone
    ADD address text;