<?php

use Company\MVC\Module;
use Company\Service\Model\ServiceMapper;
use Company\Zone\Model\ZoneMapper;

$module = new Module("company/zone");

$module->initDatabase();

try {
    ZoneMapper::makeInstance()->count($count);
    if ($count == 0) {
        $zoneID = env("PACS_ZONE_ID", "default");
        ZoneMapper::makeInstance()->insert([
            "id" => $zoneID,
            "name" =>  "default"
        ]);
    }

} catch (\Exception $e) {
    // existed
}