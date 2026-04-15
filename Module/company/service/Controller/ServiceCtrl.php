<?php


namespace Company\Service\Controller;


use Company\Auth\Auth;
use Company\Cache\CacheDriver;
use Company\Exception\BadRequestException;
use Company\Service\Model as M;

class ServiceCtrl extends \Company\MVC\Controller
{
    protected $auth;

    function init() {
        parent::init();
        $this->auth = Auth::getInstance();
    }

    function updateService($serviceID = null) {
        $this->auth->requireAdmin();

        M\ServiceMapper::makeInstance()->updateService($serviceID, $this->input());
        $this->resp->setBody(json_encode(result(true)));
    }

    function getService($serviceID = null) {
        $this->auth->requireAdmin();

        $res =
            ($serviceID) ?
                M\ServiceMapper::makeInstance()->getService($serviceID) :
                M\ServiceMapper::makeInstance()->getAllService();

        $this->resp->setBody(json_encode($res));
    }

    function deleteService($id) {
        $this->auth->requireAdmin();

        M\ServiceMapper::makeInstance()->deleteService($id);
        $this->resp->setBody(json_encode(result(true)));
    }

    function getProcesses($serviceID = null) {
        $this->auth->requireAdmin();

        $res = [];

        $containers = CacheDriver::getInstance(CacheDriver::SHARE_CACHE)->hGetAll(M\ServiceMapper::CONTAINER_SERVICE_HASH_KEY);
        foreach ($containers as $ip => $service) {
            $service = json_decode($service, true);

            // if container not die
            if ($service["expiredTime"] > time()) {
                $data = $service["data"];
                if ($serviceID) {
                    foreach ($data as $serviceName => $serviceInfo) {
                        if ($serviceID == $serviceName) {
                            $res[$ip] = $serviceInfo;
                            break;
                        }
                    }
                } else
                    $res[$ip] = $data;
            }
        }

        $this->resp->setBody(json_encode($res));
    }

    function handleProcess() {
        $this->auth->requireAdmin();

        $method = $this->req->get("method");
        if (!in_array($this->req->get("method"), ["start", "stop", "startAll", "stopAll"]))
            throw new BadRequestException("method must be start/stop");

        $containerIP = $this->req->get("ip");
        $serviceID = $this->req->get("serviceID");
        $process = $this->req->get("process");
        $pid = $this->req->get("pid");

        if ($method == "start" or $method == "stop") {
            $status = CacheDriver::getInstance(CacheDriver::SHARE_CACHE)->hSet(M\ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $containerIP . "|" . $serviceID. "|" .$process. "|" .$pid, $method);
            $this->resp->setBody(json_encode(result($status)));
        }
        else {
            $containers = CacheDriver::getInstance(CacheDriver::SHARE_CACHE)->hGetAll(M\ServiceMapper::CONTAINER_SERVICE_HASH_KEY);

            $method = ($method == "startAll") ? "start" : "stop";

            foreach ($containers as $ip => $service){
                $services = json_decode($service,1)["data"][$serviceID];
                foreach ($services as $serviceInfo){
                    CacheDriver::getInstance(CacheDriver::SHARE_CACHE)->hSet(M\ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $ip . "|" . $serviceID. "|" .$serviceInfo["process"]. "|" .$serviceInfo["pid"], $method);
                }
            }
            $this->resp->setBody(json_encode(result(true)));
        }

    }

}