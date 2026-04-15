<?php

use Company\Cache\CacheDriver;
use Company\Service\Model\ServiceMapper;

$ROOT_PATH = dirname(__DIR__, 4);
require_once $ROOT_PATH . '/Docroot/index.php';

$method = $argv[1] == "start" ? "start": "stop";

const SERVICE_STOPPED = 0;
const SERVICE_CRASHED = -1;

function get_numerics($str) {
    preg_match_all('/\d+/', $str, $matches);
    return $matches[0];
}

function getPID($command) {
    $output = shell_exec("ps aux | grep '$command' | grep -v grep | awk {'print $2'}");
    $output = get_numerics($output);

    if (count($output) > 0)
        return $output[0];

    return null;
}

$ip = getHostName();

$services = ServiceMapper::makeInstance()->getAll()->toArray();

$cacheInstance = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
$allContainers = $cacheInstance->hGetAll(ServiceMapper::CONTAINER_SERVICE_HASH_KEY);

$thisContainer = null;
if (array_key_exists($ip, $allContainers)) {
    $thisContainer = json_decode($allContainers[$ip], true)["data"];
    $thisContainer = array_combine(array_column($thisContainer, "serviceID"), array_values($thisContainer));
}

foreach ($services as $service) {
    $serviceID = $service["id"];
    $attrs = json_decode($service["attrs"] ?? "", true);
    if ($method === "start" && arrData($attrs, "autoStart") != 1)
        continue;
    CacheDriver::getInstance(CacheDriver::SHARE_CACHE)->hSet(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $ip . "|" . $serviceID, $method);
}