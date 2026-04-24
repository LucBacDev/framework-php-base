<?php
$ROOT_PATH = __DIR__;
require_once $ROOT_PATH . '/Docroot/index.php';

use Company\Task\Model\TaskElasticMapper;

try {
    $mapper = TaskElasticMapper::makeInstance();
    $res = $mapper->update("test_123", ["id" => "test_123", "title" => "Test task", "status" => "Mới"]);
    var_dump($res);
} catch (\Exception $e) {
    echo $e->getMessage() . "\n";
}
