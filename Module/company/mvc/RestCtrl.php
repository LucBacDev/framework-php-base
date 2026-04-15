<?php

namespace Company\MVC;

class RestCtrl extends Controller {

    function __construct(MvcContext $context) {
        parent::__construct($context);
        $this->resp->header('Content-type', 'application/json');
    }

}
