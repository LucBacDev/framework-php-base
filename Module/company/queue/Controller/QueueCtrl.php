<?php

namespace Company\Queue\Controller;

use Company\Auth\Auth;
use Company\ElasticSearch\ElasticMapper;
use Company\Queue\Model\MessageQueueMapper;
use Company\Queue\QueueProducer;

class QueueCtrl extends \Company\MVC\Controller
{
    protected $auth;

    function init() {
        parent::init();
        $this->auth = Auth::getInstance();
    }

    function getTopics() {
        $res = [];

        $this->outputJSON($res);
    }

    function getQueueManager() {
        $pageNo = $this->req->get("pageNo", 1);
        $pageSize = $this->req->get("pageSize", 50);

        $mapper = \Company\Queue\Model\MessageQueueMapper::makeInstance()
            ->setPage($pageNo, $pageSize)
            ->orderBy("created_time asc");

        $message_id = $this->req->get("message_id");
        if ($message_id) {
            $mapper->filterMessages(explode(",", $message_id));
        }

        if ($topic = $this->req->get("topic")) {
            $mapper->filterTopic($topic);
        }

        $results = $mapper
            ->getPage();

        $this->outputJSON($results);
    }

    function updateMessage($message_id) {
        $body = $this->req->getBody();
        if ($body) {
            $status = MessageQueueMapper::makeInstance()
                ->filterMessages($message_id)
                ->update(["body" => $body]);

            $this->outputJSON(result($status > 0));
        } else {
            $this->outputJSON(result(false, "Body must not empty"));
        }

    }

    function retry($ids) {
        $message_ids = explode(",", $ids);

        $results = MessageQueueMapper::makeInstance()->filterMessages($message_ids)->getAll()->toArray();

        foreach ($results as $row) {
            $arrMsg = json_decode($row["body"], true);

            if ($row["topic"] == "SYNC_ELASTIC") {
                $response = ElasticMapper::makeInstance()->handleMultiMessagess($arrMsg);

                if (is_array($response)) {
                    $this->outputJSON($response);
                    return;
                }
            } else {
                foreach ($arrMsg as $msg) {
                    QueueProducer::makeInstance()->insertQueueWithPartition($msg["Topic"], $msg["Partition"], $msg["Key"], $msg["Value"]);
                }
            }

            MessageQueueMapper::makeInstance()->filterMessages($row["message_id"])->delete();
        }

        $this->outputJSON(result());
    }
}