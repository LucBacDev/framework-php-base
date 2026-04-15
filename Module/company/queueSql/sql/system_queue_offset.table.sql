create table system_queue_offset (
    topic varchar(50),
    consumerGroup varchar(50),
    offset BIGINT UNSIGNED,
    primary key(topic, consumerGroup)
);



