<?php

namespace CompanyUI\BaseComponent;
use Company\MVC\Module;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = new Module("companyui/basecomponent");
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
