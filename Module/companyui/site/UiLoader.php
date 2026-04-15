<?php

namespace CompanyUI\Site;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/site');
        $layout->addJS($module->getBabelURL('autoload.json') );
    }

}
