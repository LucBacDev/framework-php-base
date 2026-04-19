<?php

namespace CompanyUI\Department;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/department');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
