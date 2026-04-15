<?php

namespace CompanyUI\Setting;

use Company\MVC\Layout;
use Company\MVC\Module;

class SettingCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function settingList($siteID) {
        $module = Module::getInstance('companyui/setting');

        $this->layout
                ->setSiteID($siteID)
                ->renderReact('Setting.List');
    }

    function SettingIntegrateList($siteID){
        $this->layout
                ->setSiteID($siteID)
                ->renderReact('SettingIntegrate.List');
    }

}
