<?php

use Company\MVC\Module;
use Company\Service\Model;

$module = new Module("company/elasticsearch");

$serviceSyncElastic = [
    "id" => "SYNC_ELASTIC",
    "name" => "Sync Elasticsearch",
    "command" => "php -d display_errors=on /var/www/html/Module/company/elasticsearch/Exec/consumerSyncElastic.php",
    "attrs" => '{"autoStart": 1}'
];

try {
    Model\ServiceMapper::makeInstance()->updateService(null, $serviceSyncElastic);
} catch (\Exception $e) {
    // existed
}

try {
    \Company\SystemMonitoring\Model\SystemMonitoringMapper::makeInstance()->updateModule(null,
        [
            "id" => "SYNC_ELASTIC",
            "name" => "Sync Elasticsearch"
        ]
    );
} catch (\Exception $e) {
    // existed
}
