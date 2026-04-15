<?php

namespace Company\Dict\Controller;

use Company\Auth\Auth;
use Company\Dict\Model as M;

class ItemCtrl extends \Company\MVC\Controller {

    /**
     *
     * @var M\ItemMapper
     */
    protected $itemMapper;

    /**
     *
     * @var Auth 
     */
    protected $auth;

    function init() {
        parent::init();
        $this->itemMapper = M\ItemMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * 
     * @param type $collectionID
     * @param type $itemID
     */
    function updateItem($collectionID = null, $itemID = null) {
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageDict');
        
        $result = $this->itemMapper->updateItem($collectionID, $itemID, $this->input());
        $this->resp->setBody(json_encode($result));
    }

    /**
     * 
     */
    function getItems($collectionID = null) {
        $this->auth->requireAdmin();

        $items = $this->itemMapper->makeInstance()
                ->filterCollectionID($collectionID)
                ->limit($this->input('limit'))
                ->getEntities();       
        $this->resp->setBody(json_encode($items->toArray()));
    }

    /**
     * 
     * @param type $id
     */
    function getItem($collectionID = null, $itemID = null) {
        $this->auth->requireAdmin();

        $item = $this->itemMapper->makeInstance()
                ->filterID($itemID)
                ->getEntityOrFail();
        $this->resp->setBody(json_encode($item));
    }
    
    function deleteItem($collectionID = null, $itemID=null){
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageDict');
        
        $this->itemMapper->deleteItem($itemID);
        $this->resp->setBody(json_encode(result(true)));
    }

}
