<?php
/**
 * SYNC_ELASTIC
 * dong bo elastic
 */
$ROOT_PATH = dirname(__DIR__, 4);
require_once $ROOT_PATH . '/Docroot/index.php';

\Company\ElasticSearch\Lib\ConsumerSyncElastic::makeInstance()->run(20, false);