<?php

namespace Company\QueueSql;

use Company\SQL\AnyMapper;
use Company\SQL\DB;
use Company\SQL\Mapper;

class Consumer {

    protected $topic;
    protected $consumerGroup;
    protected $batchSize;
    protected $sleepSec;

    /**
     * 
     * @param array $topics
     * @param string $consumerGroup
     * @param int $batchSize default 1, number of records per process batch
     * @param int $sleepSec default 1, number of secs per 
     */
    function __construct($topics, $consumerGroup, $batchSize = 1, $sleepSec = 10) {
        $this->topics = $topics;
        $this->consumerGroup = $consumerGroup;
        $this->batchSize = $batchSize;
        $this->sleepSec = $sleepSec;
    }

    /**
     * 
     * @param array $topics
     * @param string $consumerGroup
     * @param int $batchSize default 1, number of records per process batch
     * @param int $sleepSec default 1, number of secs per 
     */
    static function makeInstance($topics, $consumerGroup, $batchSize = 1, $sleepSec = 10) {
        return new static($topics, $consumerGroup, $batchSize, $sleepSec);
    }

    function start($handler) {
        if (!is_callable($handler))
            throw new \Exception('$handler must callable');

        while (true) {
            foreach($this->topics as $topic) {
                //get latest offset
                $offset = (int) AnyMapper::makeInstance()
                    ->from("system_queue_offset")
                    ->where("topic=? AND consumerGroup=?")
                    ->setParamWhere($topic instanceof Mapper ? $topic->tableName() : $topic)
                    ->setParamWhere($this->consumerGroup)
                    ->select('offset')
                    ->getOne();

                $messages = []; //reset var
                $locked = DB::getInstance()->customLock("consumer/{$this->topic}/{$this->consumerGroup}", 1000);
                if (!$locked) {
                    continue;
                }
                $messages = $this->getMessages($topic, $offset)->toArray();
                if(count($messages)) 
                    $handler($topic, $messages);
            }
            if (count($messages) == 0) {
                sleep($this->sleepSec);
                continue;
            }
        }
    }

    function getMessages($topic, $offset) {
        if($topic instanceof Mapper) {
            return $topic->makeInstance()
                ->where($topic->tableName() . '.id > ?', __FUNCTION__)
                ->setParamWhere($offset, __FUNCTION__)
                ->limit($this->batchSize)
                ->getEntities();
        } else {
            return QueueMapper::makeInstance()
                ->filterOffset($offset)
                ->limit($this->batchSize)
                ->getEntities();
        }

    }

    function commit($topic, $offset) {
        $mapper = AnyMapper::makeInstance();
        $mapper->startTrans();
        $mapper->db->execute("REPLACE INTO system_queue_offset VALUES(?,?,?)",
                [$topic instanceof Mapper ? $topic->tableName() : $topic, $this->consumerGroup, (int) $offset]);
        $mapper->completeTransOrFail();
    }

}
