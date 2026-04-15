<?php

namespace Company\MVC;

class View extends \Slim\View {

    /** @var MvcContext */
    protected $context;

    /**

     * 

     * @param \Libs\MvcContext $context

     */
    function __construct() {
        parent::__construct();
        $this->setTemplatesDirectory(BASE_DIR . '/Module');
        $this->init();
    }

    protected function init() {
        
    }

    function render($template, $data = array()) {
        $this->context->app->slim->response->setBody(parent::render($template, $data));
    }

    function getOutput($template, $data = array()) {

        $this->setData($data);

        return parent::render($template);
    }

}
