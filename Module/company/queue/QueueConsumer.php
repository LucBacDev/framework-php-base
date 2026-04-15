<?php

namespace Company\Queue;

use Company\Cache\CacheDriver;
use Company\Kafka\KafkaConsumer;
use Company\Queue\Model\MessageQueueMapper;
use Company\Service\Model\ServiceMapper;

abstract class QueueConsumer extends Queue
{

    const QUEUE_001 = "QUEUE_001";
    const QUEUE_002 = "QUEUE_002";

    const EMPTY_SERVICE = "EMPTY_SERVICE";



    protected $consumer;

    function __construct()
    {

    }

    /**
     * @return array string
     */
    abstract function subcribers();

    /**
     * @return string
     */
    abstract function groupID();

    /**
     * @return string
     */
    abstract function serviceID();

    static function makeInstance()
    {
        return new static;
    }

    /**
     * make KafkaConsumer
     * @return KafkaConsumer
     */
    function makeKafka()
    {
        return KafkaConsumer::makeInstance()
            ->topicsName($this->subcribers())
            ->groupID($this->groupID())
            ->autoCommit(false)
            ->resetOffset("earliest")
            ->makeConsumer();
    }

    function makeSql()
    {
        return Null;
    }

    /**
     * run consumer
     */
    function run($maxRecords = 1, $retryWhenExpire = false)
    {
        if (static::getDBType() == "KAFKA") {
            $this->consumer = $this->makeKafka();
        } else {
            $this->consumer = $this->makeSql();
        }

        $status = 0;
        $lastMessageTimestamp = time();
        while (true) {
            if (time() - $lastMessageTimestamp > 30 * 60)
                die(); // restart neu khong nhan message trong 30 phut

            if (static::getDBType() == "KAFKA") {
                $arrMsg = json_decode($this->consumer->getMessages($maxRecords) ?? "", true);
                if ($arrMsg == Null){
                    var_dump($arrMsg);
                    var_dump("______________________________");
                    sleep(1);
                    if ($status < 6){
                        $status++;
                    }
                    if ($status == 5){
                        $this->pause();
                    }
                    continue;
                }
                if ($status != 0){
                    $status = 0;
                    $this->resume();
                }
                $lastMessageTimestamp = time();
                $start_time = microtime(true);
                while (true) {
                    $this->pause();
                    $resp = $this->processQueue($arrMsg);

                    if ($resp === true || arrData($resp, "status") === true) {
                        $this->consumer->commitOffset();
                        break;
                    }
                    else if (arrData($resp, "code") == self::QUEUE_002) {

                        self::shutdownService($this->serviceID());
                        var_dump("shutdown service");
                        sleep(10);

                        break;
                    }
                    else{
                        sleep(1);
                    }
                    $end_time = microtime(true);
                    $execution_time = ($end_time - $start_time);
                    if($execution_time >= 120){

                        $topic = $arrMsg[0]["Topic"];

                        if (is_array($resp)) {

                            // write message to db
                            $message_id = $arrMsg[0]["Key"];
                            $body = json_encode($arrMsg);
                            $response = json_encode($resp["data"]);
                            $statusUpdate = MessageQueueMapper::makeInstance()->insert([
                                "topic" => $topic,
                                "message_id" => $message_id,
                                "body" => $body,
                                "response" => $response,
                                "created_time" => date(DATE_NORMAL)
                            ]);


                        } else {
                            if ($retryWhenExpire) {
                                var_dump("retry message");
                                $this->retry($arrMsg);
                            } else
                                die;
                        }
                        $this->consumer->commitOffset();
                        break;
                    }
                }
            } else {

            }
        }
    }

    function retry($arrMsg)
    {
        foreach ($arrMsg as $msg) {
            QueueProducer::makeInstance()->insertQueueWithPartition($msg["Topic"], $msg["Partition"], $msg["Key"], $msg["Value"]);
        }
    }

    /**
     * @param $arrMsg
     * @return boolean|array
     */
    abstract function processQueue($arrMsg);

    function resume()
    {
        var_dump("resume");
        \Company\Cassandra\Mapper::connect();
        //\Company\SQL\DB::Connect();
    }

    function pause()
    {
        var_dump("pause");
        \Company\Cassandra\Mapper::closeDB();
        \Company\SQL\DB::Disconnect();
    }

    function shutdownService($serviceID)
    {
        $containers = CacheDriver::getInstance(CacheDriver::SHARE_CACHE)->hGetAll(ServiceMapper::CONTAINER_SERVICE_HASH_KEY);

        foreach ($containers as $ip => $service){
            $services = json_decode($service,1)["data"][$serviceID];
            foreach ($services as $serviceInfo){
                CacheDriver::getInstance(CacheDriver::SHARE_CACHE)->hSet(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $ip . "|" . $serviceID. "|" .$serviceInfo["process"]. "|" .$serviceInfo["pid"], "stop");
            }
        }
    }

}