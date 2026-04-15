<?php

namespace Company\QueueSql;

use Company\SQL\Mapper;

class QueueMapper extends Mapper {

    function tableName() {
        return 'system_queue';
    }

    function tableAlias() {
        return 'queue';
    }

    function produce($topic, $body, $msgId = null) {
        $this->startTrans();
        if ($msgId == -9999999)
            $msgId = uniqid();
        $msg = [
            'msgId' => $msgId,
            'topic' => $topic,
            'createdDate' => \DateTimeEx::create()->toIsoString(),
            'body' => $body
        ];
        $this->validateMessage($msg);
        $this->insert($msg);
        $this->completeTransOrFail();
    }

    function produceMany($topic, $messages) {
        $insert = [];
        foreach ($messages as $message) {
            $this->validateMessage($message);
            if (arrData($message, 'msgId') == -9999999)
                $message['msgId'] = uniqid();
            $insert[] = [
                'msgId' => $message['msgId'],
                'topic' => $topic,
                'createdDate' => \DateTimeEx::create()->toIsoString(),
                'body' => $message['body']
            ];
        }
        $this->startTrans();
        $this->insert($insert);
        $this->completeTransOrFail();
    }

    function filterOffset($offset) {
        $this->where($this->tableAlias() . '.id > ?', __FUNCTION__);
        $this->setParamWhere($offset, __FUNCTION__);
        return $this;
    }

    protected function validateMessage($message) {
        if (!isset($message['topic']))
            throw new \Exception("message topic must not null");

        if (!isset($message['body']))
            throw new \Exception("message body must not null");
    }

}
