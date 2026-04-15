<?php

namespace CompanyUI\Module;

use Company\MVC\Layout;

class ModuleCtrl extends \Company\MVC\Controller {

    protected $layout;

    /**
     * @var Module 
     */
    protected $module;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function moduleList($siteID) {
        $this->layout
                ->setSiteID($siteID)
                ->renderReact('ModuleList');
    }


}
