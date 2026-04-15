CREATE TABLE system_queue(
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    msgId VARCHAR(100),
    topic VARCHAR(50),
    body LONGTEXT,
    createdDate DATETIME
);

create index idx_topic on system_queue(topic);
create index idx_created_date on system_queue(createdDate);
create index idx_mgsId on system_queue(msgId, topic);