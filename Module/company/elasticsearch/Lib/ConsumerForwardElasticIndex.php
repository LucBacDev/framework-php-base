<?php

namespace Company\ElasticSearch\Lib;

use Company\ElasticSearch\ElasticMapper;
use Company\Queue\QueueConsumer;
use Company\SystemMonitoring\Lib\SystemMonitoring;

class ConsumerForwardElasticIndex extends QueueConsumer
{
    /**
     * @var array
     * ["index_source"=>"index_dest"]
     */
    protected $mappingIndexs;

    function subcribers()
    {
        // TODO: Implement subcribers() method.
        return ["SYNC_ELASTIC"];
    }

    function groupID()
    {
        // TODO: Implement groupID() method.
        return "ConsumerForwardElasticIndex";
    }

    function serviceID()
    {
        return self::EMPTY_SERVICE;
    }

    function setConfig($mappingIndexs){
        $this->mappingIndexs = $mappingIndexs;
    }

    function processQueue($arrMsg)
    {
        // TODO: Implement processQueue() method.
        var_dump("________________Begin Sync DB________________");
        //var_dump($arrMsg);
        $messagesForward = [];

        foreach ($arrMsg as $message) {
            $data = json_decode($message["Value"], true);
            $indexSource = $data["index"];

            if(array_key_exists($indexSource, $this->mappingIndexs)){
                $data["index"] = $this->mappingIndexs[$indexSource];
                $data = json_encode($data);
                $message["Value"] = $data;
                $messagesForward[] = $message;
            }
        }

        var_dump($messagesForward);
        try {
            // xu ly queue
            $resp = ElasticMapper::makeInstance()->handleMultiMessagess($messagesForward, true);
        }catch (\Exception $ex){
            var_dump($ex->getMessage());
            $resp = result(false, $ex->getMessage());
        }

        $success = $resp === true;

        var_dump("________________END Sync DB________________");
        var_dump("________________Success $success ________________");
        return $resp;

    }
}