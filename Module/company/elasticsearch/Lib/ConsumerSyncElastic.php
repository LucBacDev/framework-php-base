<?php


namespace Company\ElasticSearch\Lib;


use Company\ElasticSearch\ElasticMapper;
use Company\Queue\QueueConsumer;
use Company\SystemMonitoring\Lib\SystemMonitoring;

class ConsumerSyncElastic extends QueueConsumer
{

    function subcribers()
    {
        // TODO: Implement subcribers() method.
        return ["SYNC_ELASTIC"];
    }

    function groupID()
    {
        // TODO: Implement groupID() method.
        return "ConsumerSyncElastic";
    }

    function serviceID()
    {
        return "SYNC_ELASTIC";
    }

    function processQueue($arrMsg)
    {
        // TODO: Implement processQueue() method.
        var_dump("________________Begin Sync DB________________");
//        var_dump($arrMsg);

        $timestamp = $arrMsg[0]["Timestamp"];
        $now = floor(microtime(true) * 1000);

        // stop move_nearline + move_offline neu elastic treo qua 30 phut
        if ($timestamp < $now - 30 * 60 * 1000) // 30 minutes
        {
            var_dump("ERROR ____");
            var_dump(SystemMonitoring::getStatus("SYNC_ELASTIC"));
            if (SystemMonitoring::getStatus("SYNC_ELASTIC") == 0)
                SystemMonitoring::setStatus("SYNC_ELASTIC", false, 5 * 60);
        }

        try {
            // xu ly queue
            $resp = ElasticMapper::makeInstance()->handleMultiMessagess($arrMsg);
        }catch (\Exception $ex){
            var_dump($ex->getMessage());
            $resp = result(false, $ex->getMessage());
        }

        if (!($resp === true)) {
            SystemMonitoring::setStatus("SYNC_ELASTIC", false, 5 * 60);
        }
        $success = $resp === true;
        var_dump("________________END Sync DB________________");
        var_dump("________________Success $success ________________");
        return $resp;
    }
}