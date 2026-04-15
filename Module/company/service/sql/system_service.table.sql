CREATE TABLE IF NOT EXISTS system_service (
    id VARCHAR(255) primary key,
    name varchar(255),
    command text,
    attrs text
);
