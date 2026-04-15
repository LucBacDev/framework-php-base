<?php

namespace CompanyUI\Setting;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/setting');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
