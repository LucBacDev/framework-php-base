<?php

namespace CompanyUI\Task;

use Company\MVC\Layout;
use Company\MVC\Module;

class TaskUiCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function taskBoard($siteID) {
        $module = Module::getInstance('companyui/task');
        
        $this->layout
            ->setSiteID($siteID)
            ->addJs($module->getPublicURL() . '/tailwind-config.js')
            ->addJs('https://cdn.tailwindcss.com')
            ->addJs('https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js')
            ->addCss($module->getPublicURL() . '/css/task.css')
            ->renderReact('TaskKanban');
    }

}
