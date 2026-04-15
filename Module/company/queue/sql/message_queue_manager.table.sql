CREATE TABLE message_queue_manager(
    id int auto_increment primary key,
    message_id VARCHAR(100),
    topic VARCHAR(100),
    body LONGTEXT,
    response LONGTEXT,
    created_time DATETIME
);

create index idx_topic on message_queue_manager(topic);
create index idx_message_id on message_queue_manager(message_id);
create index idx_topic_message on message_queue_manager(topic, message_id);