<?php

$ROOT_PATH = dirname(__DIR__, 4);
require_once $ROOT_PATH . '/Docroot/index.php';

use Company\Cache\CacheDriver;
use Company\Service\Lib\Process;
use Company\Service\Model\ServiceMapper;
use Company\Zone\Model\ZoneMapper;

const SERVICE_STOPPED = 0;
const SERVICE_CRASHED = -1;
const ADD_PROCESS = 2;
const RESET_PROCESS = -2;

$sleepTime = 3;

$cacheInstance = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
$ip = getHostName();
var_dump("hostname: $ip");

$zoneID = ZoneMapper::getMasterZoneID();

function get_numerics($str)
{
    preg_match_all('/\d+/', $str ?? '', $matches);
    return $matches[0];
}

function getPID($command)
{
    $output = shell_exec("ps aux | grep '$command' | grep -v grep | awk {'print $2'}");
    $output = get_numerics($output);

    if (count($output) > 0)
        return $output[0];

    return null;
}

function getPidMultithreads($command)
{
    $output = shell_exec("ps aux | grep '$command' | grep -v grep | awk {'print $2'}");

    $output = get_numerics($output);

    if (count($output) > 0)
        return $output;

    return null;
}

function runCommand($command, $callback = null)
{
    $p = new Process($command);
    if (is_callable($callback))
        call_user_func($callback, $p);
    return [$p->getPid(), $p->status()];
}

$expiredSecond = 60;

while (true) {
    \Company\SQL\DB::Disconnect();
    $stopServicesList = [];

    $allContainers = $cacheInstance->hGetAll(ServiceMapper::CONTAINER_SERVICE_HASH_KEY);

    $masterNode = $cacheInstance->hGet(ServiceMapper::MASTER_NODE_HASH_KEY, $zoneID);
    if (!$masterNode || !array_key_exists($masterNode, $allContainers)) {
        $cacheInstance->hSet(ServiceMapper::MASTER_NODE_HASH_KEY, $zoneID, $ip);
        $masterNode = $cacheInstance->hGet(ServiceMapper::MASTER_NODE_HASH_KEY, $zoneID);
    }

    // remove trash ips
    foreach ($allContainers as $key => $value) {
        $expiredTime = json_decode($value, true)["expiredTime"];
        if ($expiredTime <= time()) {
            $cacheInstance->hDel(ServiceMapper::CONTAINER_SERVICE_HASH_KEY, $key);
            unset($allContainers[$key]);
            if ($masterNode == $key)
                $cacheInstance->hDel(ServiceMapper::MASTER_NODE_HASH_KEY, $zoneID);
        }
    }
    // check request start/stop
    $services = ServiceMapper::makeInstance()->getAll()->toArray();
    $services = array_combine(array_column($services, "id"), array_values($services));



    $thisContainer = null;
    if (array_key_exists($ip, $allContainers)) {
        var_dump("Loading container $ip");
        $thisContainer = json_decode($allContainers[$ip], true)["data"];
    }
    //print_r($thisContainer);

    foreach ($cacheInstance->hGetAll(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY) as $key => $value) {
        list($containerIP, $serviceID, $processNumber, $pid) = explode("|", $key);
        var_dump($key);
        if ($ip == $containerIP) {
            $command = arrData($services, [$serviceID, "command"]);
            if ($command) {
                $allPid = getPidMultithreads($command) ?? [];
                if ($value == "start") {
                    // run command if process not exists
                    $currentProcess = array_column($thisContainer[$serviceID], "process");
                    if (in_array($processNumber, $currentProcess) && ($pid == SERVICE_STOPPED || $pid == SERVICE_CRASHED)) {

                        list($resPID, $status) = runCommand($command, function ($p) use ($serviceID) {
                            echo "$serviceID retrying with pid " . $p->getPid() . "\n";
                        });
                        if (!$status) {
                            echo "Failed!\n";
                            $pidStart = SERVICE_CRASHED;
                        } else
                            $pidStart = $resPID;

                        foreach ($thisContainer[$serviceID] as $index => $pidInfo) {
                            if ($processNumber == $pidInfo["process"]) {
                                $thisContainer[$serviceID][$index]["pid"] = $pidStart;
                            }
                        }
                    }
                } else if ($value == "stop") {
                    // stop process if exists
                    if (in_array($pid, $allPid)) {
                        $p = new Process();
                        $p->setPid($pid);
                        $p->stop();
                        echo "$serviceID stopping with pid " . $pid . "\n";
                    }
                    foreach ($thisContainer[$serviceID] as $index => $pidInfo) {
                        if ($processNumber == $pidInfo["process"]) {
                            $thisContainer[$serviceID][$index]["pid"] = SERVICE_STOPPED;
                        }
                    }
                    $stopServicesList[] = $serviceID;
                } else if ($value == "remove") {
                    // stop process if exists
                    if (in_array($pid, $allPid)) {
                        $p = new Process();
                        $p->setPid($pid);
                        $p->stop();
                        echo "$serviceID stopping with pid " . $pid . "\n";
                    }
                    foreach ($thisContainer[$serviceID] as $index => $pidInfo) {
                        if ($processNumber == $pidInfo["process"]) {
                            unset($thisContainer[$serviceID][$index]);
                        }
                    }
                    $stopServicesList[] = $serviceID;
                } else if ($value == "addProcess") {
                    $exists = false;
                    foreach ($thisContainer[$serviceID] ?? [] as $p) {
                        if ($p["process"] == $processNumber) {
                            $exists = true;
                            break;
                        }
                    }

                    if (!$exists) {
                        $thisContainer[$serviceID][] = [
                            "serviceID" => $serviceID,
                            "process" => $processNumber,
                            "pid" => $pid
                        ];
                    }
                }

                $cacheInstance->hDel(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $key);
            }
        }
    }

    // Update service status, restart if service crashed
    $res = [];

    foreach ($services as $service) {
        $serviceID = $service["id"];
        $serviceName = $service["name"];
        $command = $service["command"];
        $attrs = json_decode($service["attrs"], true);
        $numProcessService = arrData($attrs, "numProcess", 1);
        $pidMultithreads = getPidMultithreads($command);
        $autoStart = $attrs["autoStart"];
        $cronStartTime = arrData($attrs, "cronStartTime");
        $retry = arrData($attrs, "retry");
        if (empty($pidMultithreads)) {
            if (!is_null($thisContainer)) {
                if (empty($thisContainer[$serviceID])) {
                    $res[$serviceID] = [];
                } else {
                    foreach ($thisContainer[$serviceID] as $pidInfo) {
                        $pid = SERVICE_STOPPED;
                        $currentPID = $pidInfo["pid"];
                        if ($cronStartTime && $autoStart === 1) {
                            var_dump($serviceID);
                            $now = (new DateTime())->format("Y-m-d H:i");
                            $cronStart = new Cron\CronExpression($cronStartTime);
                            try {
                                $nextCronStartTime = $cronStart->getNextRunDate("now", 0, true)->format('Y-m-d H:i');
                                if ($now == $nextCronStartTime || (arrData($attrs, "cronEndTime") and $currentPID !== SERVICE_STOPPED)) {
                                    list($resPID, $status) = runCommand($command, function ($p) use ($serviceID) {
                                        echo "$serviceID running with pid " . $p->getPid() . "\n";
                                    });
                                    $pid = $resPID;
                                }
                            } catch (Exception $e) {
                                // failed to parse cron expression
                            }
                        } else {
                            // if crashed or add process, retry service
                            if ($autoStart === 1 and $currentPID !== SERVICE_STOPPED) {
                                setSystemStatus($serviceID, false, 5 * 60);
                                list($resPID, $status) = runCommand($command, function ($p) use ($serviceID) {
                                    echo "$serviceID retrying with pid " . $p->getPid() . "\n";
                                });
                                if (!$status) {
                                    $pid = SERVICE_CRASHED;
                                    echo "Retry failed!\n";
                                } else {
                                    echo "Done!\n";
                                    $pid = $resPID;
                                }
                            }
                        }
                        $res[$serviceID][] = [
                            "serviceID" => $serviceID,
                            "serviceName" => $serviceName,
                            "process" => $pidInfo["process"],
                            "pid" => $pid
                        ];
                    }
                }
            } else {
                // init slot cho TẤT CẢ node
                /* init service */

                $res[$serviceID][] = [
                    "serviceID" => $serviceID,
                    "serviceName" => $serviceName,
                    "process" => uid(),
                    "pid" => ADD_PROCESS
                ];
            }
        } else {
            if (!is_null($thisContainer)) {
                if (empty($thisContainer[$serviceID])) {
                    $res[$serviceID] = [];
                } else {
                    $stopServices = 0;
                    foreach ($thisContainer[$serviceID] as $pidInfo) {
                        $pid = SERVICE_STOPPED;
                        $currentPID = $pidInfo["pid"];
                        if (in_array($currentPID, $pidMultithreads)) {
                            $pid = $currentPID;
                        } else {
                            if ($cronStartTime && $autoStart === 1) {
                                $now = (new DateTime())->format("Y-m-d H:i");
                                $cronStart = new Cron\CronExpression($cronStartTime);
                                try {
                                    $nextCronStartTime = $cronStart->getNextRunDate("now", 0, true)->format('Y-m-d H:i');
                                    if ($now == $nextCronStartTime || (arrData($attrs, "cronEndTime") and $currentPID !== SERVICE_STOPPED)) {
                                        list($resPID, $status) = runCommand($command, function ($p) use ($serviceID) {
                                            echo "$serviceID running with pid " . $p->getPid() . "\n";
                                        });
                                        $pid = $resPID;
                                    }

                                } catch (Exception $e) {
                                    // failed to parse cron expression
                                }
                            } else {
                                // if crashed or add process, retry service
                                if ($autoStart === 1 and $currentPID !== SERVICE_STOPPED) {
                                    setSystemStatus($serviceID, false, 5 * 60);
                                    list($resPID, $status) = runCommand($command, function ($p) use ($serviceID) {
                                        echo "$serviceID retrying with pid " . $p->getPid() . "\n";
                                    });
                                    if (!$status) {
                                        $pid = SERVICE_CRASHED;
                                        echo "Retry failed!\n";
                                    } else {
                                        echo "Done!\n";
                                        $pid = $resPID;
                                    }
                                }
                            }
                        }

                        $res[$serviceID][] = [
                            "serviceID" => $serviceID,
                            "serviceName" => $serviceName,
                            "process" => $pidInfo["process"],
                            "pid" => $pid
                        ];

                        $cronEndTime = arrData($attrs, "cronEndTime");
                        if ($cronEndTime) {
                            $now = (new DateTime())->format("Y-m-d H:i");
                            $cronEnd = new Cron\CronExpression($cronEndTime);

                            try {
                                $nextCronEndTime = $cronEnd->getNextRunDate("now", 0, true)->format('Y-m-d H:i');
                                if ($now == $nextCronEndTime) {
                                    $cacheInstance->hSet(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $ip . "|" . $serviceID . "|" . $pidInfo["process"] . "|" . $pidInfo["pid"], "stop");
                                }
                            } catch (Exception $e) {

                            }
                        }

                        if($pidInfo == SERVICE_STOPPED){
                            $stopServices++;
                        }

                    }
                    if($stopServices < $numProcessService){
                        setSystemStatus($serviceID, true, $expiredSecond);
                    }
                }
            } else {
                foreach ($pidMultithreads as $pid) {
                    var_dump("stop pid");
                    $p = new Process();
                    $p->setPid($pid);
                    $p->stop();
                    echo "$serviceID stopping with pid " . $pid . "\n";
                }
                $res[$serviceID] = [];
            }
        }
    }

    //print_r($res);

    $now = time();
    echo "Running at $now\n";
    try {
        $cacheInstance->hSet(ServiceMapper::CONTAINER_SERVICE_HASH_KEY, $ip, json_encode([
            "expiredTime" => $now + $expiredSecond,
            "data" => $res,
            "zone" => $zoneID
        ]));
        var_dump("$ip .... Updated service.");

    } catch (RedisException $exception) {
        echo "Lost connection\n";
        while (true) {
            try {
                echo "Reconnecting ...\n";
                $cacheInstance = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
                $cacheInstance->ping();
                echo "Connected!\n";
                break;
            } catch (RedisException $exception) {
                sleep(5);
            }
        }
    }

    sleep($sleepTime);
}

function setSystemStatus($serviceID, $status = true, $expiredSecond = 30) {
    $systemServices = ["DICOMNET", "SYNC_ELASTIC", "UPLOAD_AI", "HL7SERVER_PROCESS", "SYNC_DICOM_INFO"];
    if (in_array($serviceID, $systemServices)) {
        \Company\SystemMonitoring\Lib\SystemMonitoring::setStatus($serviceID, $status, $expiredSecond);
    }

}