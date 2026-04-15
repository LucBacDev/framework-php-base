<?php

namespace Company\Kafka;

class KafkaTool {

    static protected $debug;

    const KAFKA = '/libs-install/kafka/bin/';
    const KAFKA_CONFIG = '/etc/kafka/secrets/sasl-config.properties';

    static function config($debug) {
        static::$debug = $debug;
    }

    function exec($cmd) {
        $output = exec($cmd, $arrResult, $result);

        if (static::$debug) {
            echo "<pre>";
            print_r($arrResult);
            echo "</pre><br><pre>" . $result . "</pre>";
        }
        return [
            'output' => $arrResult,
            'return' => $result
        ];
    }

    /**
     * create topic
     * sh kafka-topics.sh --create --bootstrap-server 172.16.10.45:9092 --command-config /var/www/html/Docroot/sasl-config.properties --replication-factor 1 --partitions 1 --topic demo-topic2
     * @param string $server
     * @param int $replicationFactor
     * @param int $partitions
     * @param string $topicName
     * @param int $retention
     */
    static function createTopic($server, $replicationFactor, $partitions, $topicName, $retention = -1)
    {
        $cmd = static::KAFKA . "kafka-topics.sh --create --bootstrap-server $server --command-config " . static::KAFKA_CONFIG . " --replication-factor $replicationFactor --partitions $partitions --config retention.ms=$retention --topic $topicName";
        $output = shell_exec($cmd);
        if (strpos($output, 'Created topic') !== false) {
            return true;
        }
        return false;
    }

    /**
     * delete topic
     * sh kafka-topics.sh --delete --bootstrap-server 172.16.10.45:9092 --command-config /var/www/html/Docroot/sasl-config.properties --topic demo-topic2
     * @param string $server
     * @param string $topicName
     */
    static function deleteTopic($server, $topicName)
    {
        $cmd = static::KAFKA . "kafka-topics.sh --delete --bootstrap-server $server --command-config " . static::KAFKA_CONFIG . " --topic $topicName";
        $output = shell_exec($cmd);
        if ($output == NULL) {
            return true;
        }
        return false;
    }

    /**
     * kafka-consumer-groups.sh --bootstrap-server 172.16.10.45:9092 --command-config /etc/kafka/secrets/sasl-config.properties --group consumer1 --execute --reset-offsets --to-datetime 2020-01-20T11:35:40.000 --topic PACS_STOW --timeout 5000
     * @param $server string
     * @param $topic string
     * @param $consumerGroup string
     * @param $scenarioOption string DATETIME or OFFSET
     * @param $input string
     */
    static function resetOffsets($server, $topic, $consumerGroup, $scenarioOption, $input){
        if ($scenarioOption == "DATETIME"){
            $scenarioOption = "--by-duration";
        }elseif ($scenarioOption == "OFFSET"){
            $scenarioOption = "--to-offset";
        }else{
            return false;
        }

        $cmd = static::KAFKA . "kafka-consumer-groups.sh --bootstrap-server $server --command-config " . static::KAFKA_CONFIG . " --topic $topic" . " --group $consumerGroup" . " --execute --reset-offsets " . $scenarioOption . " " .$input;
        $output = shell_exec($cmd);
        return $output;
    }
    
}

