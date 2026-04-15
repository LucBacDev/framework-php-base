<?php

namespace Company\Queue;

class QueueKafkaProducer extends \Company\Kafka\KafkaProducer{
    static protected $topics;

    /**
     * set topics kafka
     */
    static function topics($topics){
        static::$topics = $topics;
    }

    static function getTopics(){
        return static::$topics;
    }

    /**
     * insert queue
     * @param string $queueName
     * @param string $keyPartition
     * @param string $key
     * @param string|array[string] $data
     */
    function insertQueue($queueName, $keyPartition, $key, $data){
        if(isset(static::$topics[$queueName])){
            $this->topicName($queueName)
                ->partitionsNum(static::$topics[$queueName]["partitions"])
                ->makeProducer()
                ->insert($key, $data, $keyPartition);

            $this->close();
        }
    }

    /**
     * insert queue with partition
     * @param string $queueName
     * @param int $partition
     * @param string $key
     * @param string|array[string] $data
     */
    function insertQueueWithPartition($queueName, $partition, $key, $data){
        if(isset(static::$topics[$queueName])){
            $this->topicName($queueName)
                ->partitionsNum(static::$topics[$queueName]["partitions"])
                ->makeProducer()
                ->insertWithPartition($key, $data, $partition);

            $this->close();
        }
    }



    /**
     * insert many queue
     * @param $queueName string
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
    function insertManyQueues($queueName, $data, $fullInsert = false){

        if ($fullInsert){
            if(isset(static::$topics[$queueName])){
                $this->topicName($queueName)
                    ->partitionsNum(static::$topics[$queueName]["partitions"])
                    ->makeProducer()
                    ->insertMany($data);
            }
        }else{
            $this->topicName($queueName)
                ->partitionsNum(static::$topics[$queueName]["partitions"])
                ->makeProducer();
            foreach ($data as $item){
//                    var_dump($item["msg"]);
                    $this->insert($item["key"], $item["msg"], $item["keyPartition"]);
            }
        }
        $this->close();
    }
}