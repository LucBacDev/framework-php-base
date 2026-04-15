<?php

namespace Company\Queue\Model;

class MessageQueueMapper extends \Company\SQL\Mapper
{

    function tableName()
    {
        return "message_queue_manager";
    }

    function tableAlias()
    {
        return "message_queue_manager";
    }

    function getPkField() {
        return 'id';
    }

    function filterTopic($topic) {
        $this->where('topic=?', __FUNCTION__)->setParamWhere($topic, __FUNCTION__);
        return $this;
    }

    function filterMessages($messages) {
        $this->filterArray("message_id", $messages);
        return $this;
    }

}