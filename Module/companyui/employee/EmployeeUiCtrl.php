<?php

namespace CompanyUI\Employee;

use Company\MVC\Layout;
use Company\MVC\Module;

class EmployeeUiCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function employeeList($siteID) {
        $module = Module::getInstance('companyui/employee');
        $this->layout
            ->setSiteID($siteID)
            ->addCss($module->getPublicURL() . '/css/employee.css')
            ->renderReact('EmployeeList');
    }

}
