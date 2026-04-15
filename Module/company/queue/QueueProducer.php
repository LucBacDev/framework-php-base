<?php
namespace Company\Queue;

class QueueProducer extends Queue {
    /**
     * make QueueKafkaProducer
     * @return QueueKafkaProducer
     */
    function makeKafka()
    {
        return QueueKafkaProducer::makeInstance();
    }

    function makeSql(){
        return QueueSqlProducer::makeInstance();
    }

    /**
     * insert queue
     * @param $queueName
     * @param $keyPartition
     * @param $id
     * @param $data
     */
    function insertQueue($queueName, $keyPartition, $id, $data){
        if(static::getDBType()=="KAFKA"){
            $this->makeKafka()
                ->insertQueue($queueName, $keyPartition, $id, $data);
        }else{

        }
    }

    /**
     * insert queue with partition
     * @param $queueName
     * @param $partition
     * @param $id
     * @param $data
     */
    function insertQueueWithPartition($queueName, $partition, $id, $data){
        if(static::getDBType()=="KAFKA"){
            $this->makeKafka()
                ->insertQueueWithPartition($queueName, $partition, $id, $data);
        }else{

        }
    }

    /**
     * @param $queueName string
     * @param $data array
     * array(1) {
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
    function insertManyQueues($queueName, $data){
        if(static::getDBType()=="KAFKA"){
            $this->makeKafka()
                ->insertManyQueues($queueName, $data, true);
        }else{

        }
    }
}