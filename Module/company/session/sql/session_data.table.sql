CREATE TABLE session_data(
    id varchar(50) primary key,
    `session` text,
    expire datetime
);

CREATE EVENT ev_gc_session
  ON SCHEDULE
    EVERY 1 DAY
    STARTS (TIMESTAMP(CURRENT_DATE) + INTERVAL 1 DAY + INTERVAL 1 HOUR)
  DO
    DELETE FROM session_data WHERE expire < NOW();