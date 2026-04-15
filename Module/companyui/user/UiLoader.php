<?php

namespace CompanyUI\User;

class UiLoader implements \Company\MVC\UiLoadable {

    public function load(\Company\MVC\Layout $layout) {
        $module = \Company\MVC\Module::getInstance('companyui/user');
        $layout->addCSS($module->getPublicURL() . '/css/user.css')
                ->addJS($module->getBabelURL('autoload.json'));
    }

}
