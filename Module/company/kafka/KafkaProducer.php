<?php

namespace Company\Kafka;

class KafkaProducer extends KafkaClient{

    protected $kafkaProducer;
    protected $topic;
    protected $partition;

    static function makeInstance()
    {
        return new static;
    }

    /**
     * set topic name
     * @param string $topic
     */
    function topicName($topic){
        $this->topic = $topic;
        return $this;
    }

    /**
     * set partitions number
     * @param int $partition 
     */
    function partitionsNum($partition){
        $this->partition = $partition;
        return $this;
    }

    /**
     * make Producer
     */
    function makeProducer(){
        $configs = static::$config;

        $this->kafkaProducer = new \KafkaBufferedProducer();
        $this->kafkaProducer->instanceProducer($this->topic, json_encode($configs));
        return $this;
    }

    /**
     * insert many messages to topic
     * @param $data array
     *  array(1) {
        [0]=>
        array(3) {
        ["msg"]=>
        string(1) "1"
        ["key"]=>
        string(1) "1"
        ["keyPartition"]=>
        string(1) "1"
        }
        }
     */
    function insertMany($data){
        if (isset($data) && is_array($data)) {
            $messages = [];
            foreach ($data as $item){
                $message = [];
                $message["msg"] = $item["msg"];
                $message["key"] = $item["key"];
                $loadBalancer = new \LoadBalancerPhp();
                $partition = $loadBalancer->getPartition($item["keyPartition"], $this->partition);
                $message["partition"] = $partition;

                $messages[] = $message;
                $this->insertWithPartition($message["key"], $message["msg"], $message["partition"]);
            }
            //$this->kafkaProducer->sendMessages($messages);
        }
    }

    /**
     * @param $key string
     * @param $msg string
     * @param $keyPartition string
     */
    function insert($key, $msg, $keyPartition){
        $loadBalancer = new \LoadBalancerPhp();
        $partition = $loadBalancer->getPartition($keyPartition, $this->partition);

        $this->kafkaProducer->sendMessage($key, $msg, $partition);
    }

    /**
     * @param $key string
     * @param $msg string
     * @param $partition int
     */
    function insertWithPartition($key, $msg, $partition){
        $this->kafkaProducer->sendMessage($key, $msg, $partition);
    }

    function close(){
        $this->kafkaProducer->close();
    }

}