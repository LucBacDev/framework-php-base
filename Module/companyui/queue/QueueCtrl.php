<?php

namespace CompanyUI\Queue;

use Company\MVC\Layout;
use Company\MVC\Module;

class QueueCtrl extends \Company\MVC\Controller {

    protected $layout;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
        $module = \Company\MVC\Module::getInstance('pacsui/queue');
        $this->layout->addJS($module->getBabelURL('autoload.json'));
    }

    function MessageQueueManager() {
        $this->layout
            ->setSiteID("master")
//            ->addCss($module->getPublicURL() . '/css/site-config.css')
            ->renderReact('MessageQueueManager');
    }
}
