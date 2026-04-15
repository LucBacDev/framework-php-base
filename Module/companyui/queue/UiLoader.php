<?php

namespace CompanyUI\Queue;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/queue');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
