<?php

namespace CompanyUI\SystemMonitoring;

use Company\MVC\Layout;
use Company\MVC\Module;

class SystemMonitoringCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function SystemMonitoring() {
        $module = Module::getInstance('companyui/systemmonitoring');

        $this->layout
            ->setSiteID("master")
//            ->addCss($module->getPublicURL() . '/css/site-config.css')
            ->renderReact('SystemMonitoring');
    }

}
