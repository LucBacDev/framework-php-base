<?php

namespace CompanyUI\Tools;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/tools');
        $js = [
            $module->getBabelURL('autoload.json')
        ];
        foreach ($js as $file) {
            $layout->addJs($file);
        }
    }

}
