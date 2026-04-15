<?php

namespace Company\Dict\Controller;

use Company\Auth\Auth;
use Company\Dict\Model as M;

class CollectionCtrl extends \Company\MVC\Controller {

    /** @var M\CollectionMapper */
    protected $colMapper;

    /**
     *
     * @var Auth 
     */
    protected $auth;

    function init() {
        parent::init();
        $this->colMapper = M\CollectionMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * 
     * @param type $id
     */
    function updateCollection($id = null) {
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageDict');

        $result = $this->colMapper->updateCollection($id, $this->input());

        $this->resp->setBody(json_encode($result));
    }

    /**
     * 
     */
    function getCollections() {
        $this->auth->requireAdmin();

        $collections = $this->colMapper->makeInstance()
                ->filterName($this->input('name'))
                ->getEntities();
        
        $this->resp->setBody(json_encode($collections->toArray()));
    }

    /**
     * 
     * @param type $id
     */
    function getCollection($id = null) {
        $this->auth->requireAdmin();

        $collection = $this->colMapper->makeInstance()
                ->filterID($id)
                ->getEntityOrFail();
     
        $this->resp->setBody(json_encode($collection));
    }

    function deleteCollection($id = null) {
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageDict');

        $this->colMapper->deleteCollection($id);
        $this->resp->setBody(json_encode(result(true)));
    }

}
