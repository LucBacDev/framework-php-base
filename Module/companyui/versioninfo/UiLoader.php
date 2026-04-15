<?php

namespace CompanyUI\VersionInfo;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/versioninfo');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
