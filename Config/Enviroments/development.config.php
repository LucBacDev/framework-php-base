<?php

use Company\Cache\PhpFileCache;
use Company\Cache\Redis;
use Company\Cassandra\Mapper;
use Company\Kafka\KafkaClient;
use Company\Queue\Queue;
use Company\Zone\Model\ZoneMapper;

use Company\Cache;
use Company\Log\Logger;

/**
 * 0 = tắt
 * 1 = PHP, Database trừ trên service
 * 10 = tất cả
 */
putenv("OPJ_NUM_THREADS=4");
putenv("TCP_NODELAY=1");
putenv("TCP_BUFFER_LENGTH=131072");

const ENV_DIR = "/var/www/html/Config/Enviroments";
//file_put_contents(ENV_DIR . "/config.json", json_encode(buildEnvArray()));
$_ENV = json_decode(file_get_contents(ENV_DIR . "/config.json"), true);

if (php_sapi_name() != "cli")
    $exports['debugMode'] = 0; // debug web
else
    $exports['debugMode'] = 0; // debug cli

$exports['production'] = 0;
const CEPH_DIR = BASE_DIR . "/Config/Ceph";

//kết nối database
$exports['db'] = [
    'default' => [
        'type' => 'mysqli',
        'hosts' => explode(",", env("PACS_MYSQL_HOST", 'mysql:3306')), //multiple ip for galera cluster
        'name' => env("PACS_DATABASE_NAME", "pacs"),
        'user' => env("PACS_MYSQL_USER", "root"),
        'pass' => env("PACS_MYSQL_PASS", "vietrad123")
    ]
];

$elasticHosts = explode(",", env("PACS_ELASTIC_HOSTS", "http://elastic:MyElasticPassword_678@elasticsearch:9200"));
$elasticReplicas = count($elasticHosts) >= 3 ? 2 : 1;
$exports['elastic'] = [
    'hosts' => $elasticHosts,
    'retries' => 2,
    'sslVerification' => false
];

$exports['elasticSettings'] = [
    'number_of_shards' => 3,
    'number_of_replicas' => $elasticReplicas
];

\Company\ElasticSearch\DB::config($exports["elastic"]);
\Company\ElasticSearch\DB::setSettings($exports["elasticSettings"]);

//$exports['cassandra'] = [
//    'host' => [
//        '172.16.10.245'
//    ],
//    'user' => null,
//    'password' => null,
//    'keyspace' => 'pacs'
//];



$exports['redis'] = [
    "host" => env("PACS_REDIS_HOST", "redis"),
    "port" => (int) env("PACS_REDIS_PORT", 6379),
    "timeout" => 1, // float, value in seconds (optional, default is 0 meaning unlimited)
    "reserved" => null, // should be NULL if retry_interval is specified
    "retry_interval" => 100, // int, value in milliseconds (optional), 100ms delay between reconnection attempts.
    "read_timeout" => 0, // float, value in seconds (optional, default is 0 meaning unlimited),
    "auth" => env("PACS_REDIS_PASS", "Redis_92932382832"), // password
//    "auth" => ['user' => 'phpredis', 'pass' => 'phpredis']
];

// config Cache
Redis::setConfig($exports['redis']);
(new Redis())->ping(); // test redis

PhpFileCache::setCachePath(sys_get_temp_dir());

\Company\Cache\CacheDriver::setInstance(\Company\Cache\CacheDriver::SHARE_CACHE, new Redis());
//\Company\Cache\CacheDriver::setInstance(\Company\Cache\CacheDriver::SHARE_CACHE, new \Company\Cache\APCU());
\Company\Cache\CacheDriver::setInstance(\Company\Cache\CacheDriver::PRIVATE_MEMORY_CACHE, new \Company\Cache\APCU());
\Company\Cache\CacheDriver::setInstance(\Company\Cache\CacheDriver::PRIVATE_CACHE, new PhpFileCache());
// init vrdcm

// init master zone
ZoneMapper::setMasterZoneID(env("PACS_ZONE_ID"));

$kafkaBrokerList = env("PACS_KAFKA_BROKER_LIST", "kafka:9092");
$kafkaReplicas = count(explode(",", $kafkaBrokerList)) >= 3 ? 2 : 1;

$exports['kafka'] = [
    "configs" => [
        'metadata.broker.list' => $kafkaBrokerList,
        'security.protocol' => 'SASL_PLAINTEXT',
        'sasl.mechanism' => 'PLAIN',
        'sasl.username' => env("PACS_KAFKA_USER", "admin"),
        'sasl.password' => env("PACS_KAFKA_PASS", "Kafka_password123498")
    ],
    "topics" => [
        // "PACS_STOW" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "SYNC_ELASTIC" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "REBALANCE_STORAGE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "PROCESS" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "MOVE_NEARLINE_STORAGE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "AUTO_COPY_FILE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "UPLOAD_AI" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "REMOVE_FILE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "SYNC_DICOM_INFO" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "COPY_INSTANCE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "PACS_MEDIA" => [
        //     "partitions" => 10,
        //     "retention.ms" => -1
        // ],
        // "THUMBNAIL_CACHE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "MODIFY_FILE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "MERGE_STUDY" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "SYNC_NUMBER_OF_SERIES_INSTANCES" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "OFFLINE_TO_NEARLINE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "FORWARD_MESSAGE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "ADVANCE_FORWARD" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "CACHE_VIEWER" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "MOVE_TO_TRASH" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "RESTORE_STUDY" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "UPDATE_DB_REMOVE_ONLINE_FILE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "UPDATE_DB_MOVE_NEARLINE" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ],
        // "CACHE_FRAME_OFFSET" => [
        //     "partitions" => 10,
        //     "retention.ms" => 604800000
        // ]
    ],
    "replicationFactor" => $kafkaReplicas
];

$exports['tmp'] = [
    'local' => '/tmp/local/',
    'shared' => '/tmp/share/'
];
if (!file_exists($exports['tmp']['local'])) {
    mkdir($exports['tmp']['local'], 0777, true);
}

$exports['cryptSecret'] = 'abM)(*2312';

//date_default_timezone_set(env("PACS_TIME_ZONE", "Asia/Bangkok"));

// Cassandra config
if (isset($exports['cassandra'])) {
    Mapper::setConfig(
        $exports['cassandra']['host'],
        $exports['cassandra']['user'],
        $exports['cassandra']['password'],
        $exports['cassandra']['keyspace'],
        $exports['debugMode']
    );
    if (class_exists("\\Cassandra"))
        Mapper::connect();
}

// kafka config
if (isset($exports['kafka'])) {
    KafkaClient::config(
        $exports['kafka']['configs']
    );
    KafkaClient::setReplicationFactor($exports['kafka']['replicationFactor']);
    Queue::enableKafka($exports['kafka']['topics']);
}

// logger
$exports['logger'] = [
    'fileLogger' => [
        'path' => "/var/log/pacs.log",
        'level' => \Monolog\Logger::INFO,
        'maxSize' => 5242880,   // rotate log if exceeds 100Kb
        'maxFiles' => 20
    ],
    'elasticLogger' => [
        'index' => 'pacs_log',
        'level' => \Monolog\Logger::INFO,
    ],
    'fluentdLogger' => [
        'host' => '127.0.0.1',
        'port' => 24224,
        'level' => \Monolog\Logger::INFO,
    ]
];

Logger::setConfig($exports['logger']);