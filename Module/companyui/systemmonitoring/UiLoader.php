<?php

namespace CompanyUI\SystemMonitoring;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/systemmonitoring');
        $layout->addJS($module->getBabelURL('autoload.json'));
        $layout->addCSS($module->getPublicURL() . '/style.css');
    }

}
