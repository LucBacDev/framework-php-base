<?php

namespace CompanyUI\Department;

use Company\MVC\Layout;
use Company\MVC\Module;

class DepartmentUiCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function departmentList($siteID) {
        $module = Module::getInstance('companyui/department');
        $this->layout
            ->setSiteID($siteID)
            ->addCss($module->getPublicURL() . '/css/department.css')
            ->renderReact('DepartmentList');
    }

}
