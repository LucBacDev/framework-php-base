create table system_queue_log(
    topic varchar(50),
    consumerGroup varchar(50),
    queueID bigint unsigned,
    log longtext,
    primary key(topic, consumerGroup, queueID)
);
