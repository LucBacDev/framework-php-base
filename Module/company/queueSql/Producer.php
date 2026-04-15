<?php

namespace Company\QueueSql;

use Company\SQL\DB;

class Producer
{
    protected $topic;

    function __construct($topic) {
        $this->topic = $topic;
    }

    static function makeInstance($topic) {
        return new static($topic);
    }

    function produce($body, $msgId = -9999999) {
        if($msgId == -9999999)
            $msgId = uniqid();

        QueueMapper::makeInstance()->produce($this->topic, $body, $msgId);
    }

    function produceMany($messages) {
        QueueMapper::makeInstance()->produceMany($this->topic, $messages);
    }
}
