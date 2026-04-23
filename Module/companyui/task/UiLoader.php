<?php

namespace CompanyUI\Task;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/task');
        $layout->addJS($module->getBabelURL('autoload.json'));
    }

}
