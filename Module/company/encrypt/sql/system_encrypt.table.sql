CREATE TABLE IF NOT EXISTS system_encrypt (
    id VARCHAR(255) primary key,
    algo varchar(255),
    key_algo text,
    active int,
    attrs text
    );
