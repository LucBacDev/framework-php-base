<?php

namespace CompanyUI\Module;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/module');
        $layout->addJS($module->getBabelUrl('autoload.json'));
    }

}
