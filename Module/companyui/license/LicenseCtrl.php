<?php

namespace CompanyUI\License;

use Company\MVC\Layout;
use Company\MVC\Module;

class LicenseCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function licenseList($siteID) {
        $module = Module::getInstance('companyui/license');
                
        $this->layout
            ->setSiteID($siteID)    
            ->renderReact('License.List');
    }


}
