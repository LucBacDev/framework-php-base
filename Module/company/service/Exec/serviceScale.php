<?php

$ROOT_PATH = dirname(__DIR__, 4);
require_once $ROOT_PATH . '/Docroot/index.php';

use Company\Cache\CacheDriver;
use Company\Service\Lib\Process;
use Company\Service\Model\ServiceMapper;
use Company\Zone\Model\ZoneMapper;
$zoneID = ZoneMapper::getMasterZoneID();
$ipContainer = getHostName();

const SERVICE_STOPPED = 0;
const SERVICE_CRASHED = -1;
const ADD_PROCESS = 2;

while (true) {
    \Company\SQL\DB::Disconnect();
    $cacheInstance = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
    $masterNode = $cacheInstance->hGet(ServiceMapper::MASTER_NODE_HASH_KEY, $zoneID);
    var_dump("master node : " . $masterNode);
    var_dump("ip node : " . $ipContainer);
    $allContainers = $cacheInstance->hGetAll(ServiceMapper::CONTAINER_SERVICE_HASH_KEY);

    if (!empty($allContainers) && $masterNode == $ipContainer) {
        $services = ServiceMapper::makeInstance()->getAll()->toArray();
        $services = array_combine(array_column($services, "id"), array_values($services));
        foreach ($services as $service) {
            $serviceID = $service["id"];
            $serviceName = $service["name"];
            $attrs = json_decode($service["attrs"], true);
            $numProcess = arrData($attrs, "numProcess", 1);
            $typeService = arrData($attrs, "typeService");
            $autoStart = $attrs["autoStart"];
            if ($numProcess < 1)
                $numProcess = 1;

            $allContainerName = array_keys($allContainers);

            if (!$cacheInstance->fileLock("scale:lock:$serviceID", 30)) {
                var_dump("Locking for $serviceID, skip scaling ...");
                continue;
            }


            if($typeService == "SingleAllNode"){
                foreach ($allContainers as $key => $value) {
                    $servicesList = json_decode($value, true)["data"][$serviceID];
                    if(empty($servicesList)){
                        $field = $key . "|" . $serviceID . "|" . uid() . "|" . ADD_PROCESS;
                        $cacheInstance->hSet(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $field, "addProcess");
                        $cacheInstance->hSetEx(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, 60, $field);
                    }

                }
            }
            else{
                $countThreads = 0;
                foreach ($allContainers as $key => $value) {
                    $threads = json_decode($value, true)["data"][$serviceID];
                    $countThreads += count($threads);
                }


                $missThreads = $numProcess - $countThreads;
                var_dump($serviceID);
                var_dump("..............." . $missThreads);

                if($missThreads == 0){
                    var_dump("No scaling needed");
                    continue;
                }
                //$missThreads = max(-2, min(2, $missThreads));

                if ($missThreads > 0) {
                    for ($i = 0; $i < $missThreads; $i++) {
                        $loadBalancer = new \LoadBalancerPhp();

                        $iuid = iuid();

                        $partition = $loadBalancer->getPartition($iuid, count($allContainerName));

                        if($autoStart == 1 && !arrData($attrs, "cronStartTime")){
                            $field = $allContainerName[$partition] . "|" . $serviceID . "|" . uid() . "|" . ADD_PROCESS;
                            $cacheInstance->hSet(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $field, "addProcess");
                            $cacheInstance->hSetEx(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, 60, $field);
                        }
                        else{
                            $field = $allContainerName[$partition] . "|" . $serviceID . "|" . uid() . "|" . SERVICE_STOPPED;
                            $cacheInstance->hSet(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $field, "addProcess");
                            $cacheInstance->hSetEx(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, 60, $field);
                        }
                    }
                }

                if ($missThreads < 0) {
                    //$needRemove = abs($missThreads);

                    foreach ($allContainers as $ip => $value) {
                        $servicesProcess = json_decode($value, true)["data"][$serviceID] ?? [];

                        foreach ($servicesProcess as $serviceInfo) {

                            $field = $ip . "|" . $serviceID . "|" . $serviceInfo["process"] . "|" . $serviceInfo["pid"];
                            $cacheInstance->hSet(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $field, "remove");
                            $cacheInstance->hSetEx(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, 60, $field);
                        }
                    }
                }

            }


        }
    }
    else{
        var_dump("container not found");
    }
    sleep(3);
}