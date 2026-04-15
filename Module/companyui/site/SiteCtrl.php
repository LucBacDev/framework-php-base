<?php

namespace CompanyUI\Site;

use Company\MVC\Layout;
use Company\MVC\Module;

class SiteCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function siteList($siteID) {
        $module = Module::getInstance('companyui/site');
                
        $this->layout
            ->setSiteID($siteID)    
            ->addCss($module->getPublicURL() . '/css/site-config.css')
            ->renderReact('SiteList');
    }
    
    function userSiteList($siteID) {
        $this->layout
                ->setSiteID($siteID)
                ->renderReact('SystemSite');
    }
    



}
