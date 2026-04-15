<?php

namespace CompanyUI\VersionInfo;

use Company\MVC\Layout;

class VersionInfoCtrl extends \Company\MVC\Controller {

    protected $layout;

    /**
     * @var Module 
     */
    protected $module;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function versionInfo($siteID) {
        $this->layout
                ->setSiteID($siteID)
                ->renderReact('VersionInfo.List');
    }

}
