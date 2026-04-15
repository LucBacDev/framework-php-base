<?php

namespace CompanyUI\Service;

use Company\MVC\Layout;
use Company\MVC\Module;

class ServiceCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function ServiceList($siteID) {
        $module = Module::getInstance('companyui/service');

        $this->layout
            ->setSiteID($siteID)
//            ->addCss($module->getPublicURL() . '/css/site-config.css')
            ->renderReact('ServiceList');
    }

    function ProcessList($siteID) {
        $module = Module::getInstance('companyui/service');

        $this->layout
            ->setSiteID($siteID)
//            ->addCss($module->getPublicURL() . '/css/site-config.css')
            ->renderReact('ProcessList');
    }
}
