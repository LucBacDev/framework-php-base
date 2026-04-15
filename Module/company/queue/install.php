<?php

use Company\MVC\Module;
use Company\Queue\Queue;

$module = new Module("company/queue");

$module->initDatabase();

if (Queue::getDBType() == Queue::DB_TYPE_KAFKA){
    $topics = \Company\Queue\QueueKafkaProducer::getTopics();
    foreach ($topics as $topic => $topicConfigs){
        \Company\Kafka\KafkaTool::createTopic(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], \Company\Kafka\KafkaClient::getReplicationFactor() , $topicConfigs["partitions"], $topic, $topicConfigs["retention.ms"]);
    }
}
