<?php
/**
 */
$ROOT_PATH = dirname(__DIR__, 4);
require_once $ROOT_PATH . '/Docroot/index.php';

\Company\Kafka\KafkaTool::resetOffsets(\Company\Kafka\KafkaClient::getConfig()['metadata.broker.list'], "SYNC_ELASTIC", "ConsumerForwardElasticIndex", "DATETIME", "PT70H");
$mappingIndex = array(
    "pacs_instance" => "pacs_instance_new"
);

$consumer = \Company\ElasticSearch\Lib\ConsumerForwardElasticIndex::makeInstance();
$consumer->setConfig($mappingIndex);
$consumer->run(20);
