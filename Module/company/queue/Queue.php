<?php

namespace Company\Queue;

// Class xu ly queue
class Queue
{
    static protected $enableKafka = false;

    const DB_TYPE_SQL = "SQL";
    const DB_TYPE_KAFKA = "KAFKA";

    static function getDBType()
    {
        return static::$enableKafka ? static::DB_TYPE_KAFKA : static::DB_TYPE_SQL;
    }

    /**
     * enable kafka storage queue
     * @param array $topics
     */
    static function enableKafka($topics)
    {
        QueueKafkaProducer::topics($topics);
        static::$enableKafka = true;
    }

    /**
     * @return static
     */
    static function makeInstance()
    {
        return new static();
    }

}