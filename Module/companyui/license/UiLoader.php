<?php

namespace CompanyUI\License;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/license');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
