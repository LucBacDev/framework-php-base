<?php

namespace CompanyUI\Employee;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/employee');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
