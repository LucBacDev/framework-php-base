<?php

namespace Company\Kafka;

class KafkaConsumer extends KafkaClient
{

    protected $kafkaConsumer;
    protected $topics;
    protected $groupID;
    protected $autoCommit;
    protected $resetOffset;

    static function makeInstance()
    {
        return new static;
    }

    /**
     * set array topics subscriber
     * @param array[string] $topics
     */
    function topicsName($topics){
        $this->topics = $topics;
        return $this;
    }

    /**
     * set groupID for consumer
     * @param string $groupID
     */
    function groupID($groupID){
        $this->groupID = $groupID;
        return $this;
    }

    /**
     * set auto commit (true/false)
     * @param boolean $autoCommit 
     */
    function autoCommit($autoCommit = true){
        $this->autoCommit = $autoCommit;
        return $this;
    }

    /**
     * @param $option string "latest", "earliest"
     */
    function resetOffset($option = "latest"){
        $this->resetOffset = $option;
        return $this;
    }

    /**
     * make Consumer
     */
    function makeConsumer(){
        $configs = static::$config;
        $configs["group.id"] = $this->groupID;
        $configs["enable.auto.commit"] = $this->autoCommit;
        $configs["auto.offset.reset"] = $this->resetOffset;
        $this->kafkaConsumer = new \KafkaConsumer();
        $this->kafkaConsumer->instanceConsumer($this->topics, json_encode($configs));
        return $this;
    }

    /**
     * get message poll
     */
    function getMessage(){
        $msg = $this->kafkaConsumer->pollMessage();
        return $msg;
    }

    /**
     * @param int $timeout miliseconds
     */
    function getMessageWithTimeout($timeout){
        $msg = $this->kafkaConsumer->pollMessageWithTimeout($timeout);
        return $msg;
    }

    /**
     * @param int $maxRecords
     * @return mixed
     */
    function getMessages($maxRecords){
        $arrMsg = $this->kafkaConsumer->pollMessages($maxRecords);
        return $arrMsg;
    }

    /**
     * @param int $maxRecords
     * @param int $timeout miliseconds
     * @return mixed
     */
    function getMessagesWithTimeout($maxRecords, $timeout){
        $arrMsg = $this->kafkaConsumer->pollMessagesWithTimeout($maxRecords, $timeout);
        return $arrMsg;
    }

    /**
     * commit offset
     */
    function commitOffset(){
        $this->kafkaConsumer->commitOffset();
    }
}
