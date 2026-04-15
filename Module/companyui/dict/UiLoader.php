<?php

namespace CompanyUI\Dict;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/dict');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
