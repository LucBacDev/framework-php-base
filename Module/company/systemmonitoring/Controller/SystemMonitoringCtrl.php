<?php

namespace Company\SystemMonitoring\Controller;

use Company\Auth\Auth;
use Company\Cache\CacheDriver;
use Company\SystemMonitoring\Model\SystemMonitoringMapper;

class SystemMonitoringCtrl extends \Company\MVC\Controller
{
    protected $auth;

    function init() {
        parent::init();
        $this->auth = Auth::getInstance();
    }

    function getSystemStatus() {
        $this->auth->requireAdmin();

        $modules = SystemMonitoringMapper::makeInstance()->getAllModule();
        $cache = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);

        foreach ($modules as &$module) {
            $val = $cache->get("systemmonitoring/" . $module["id"]);
            $module["status"] = is_bool($val) ? $val : (int) $val;
        }
        $this->outputJSON($modules);
    }
}