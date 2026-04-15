<?php

namespace Company\Setting\Controller;

use Company\Auth\Auth;
use Company\Setting\Model as M;

class FormCtrl extends \Company\MVC\Controller {

    /** @var M\FormMapper */
    protected $formMapper;

    /**
     *
     * @var Auth 
     */
    protected $auth;

    function init() {
        parent::init();
        $this->formMapper = M\FormMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * 
     * @param type $id
     */
    function updateForm($id = null) {
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageSetting');
         
        $result = $this->formMapper->updateForm($id, $this->input());
        $this->resp->setBody(json_encode($result));
    }
    
    /**
     * 
     */
    function getForms() {
        $this->auth->requireAdmin();     
        $pageSize = $this->input('pageSize', 20);
        $pageNo = $this->input('pageNo', 1);
        
        $res = $this->formMapper->makeInstance()
                ->setPage($pageNo, $pageSize)
                ->filterName($this->input('name'))
                ->filterNotID('integrate')
                ->getPage();

        $this->resp->setBody(json_encode($res));
    }

    /**
     * 
     * @param type $id
     */
    function getForm($id = null) {
        $this->auth->requireAdmin();

        $form = $this->formMapper->makeInstance()
                ->filterID($id)
                ->getEntityOrFail();
     
        $this->resp->setBody(json_encode($form));
    }
    
    function updateSettingIntegrate($siteID){
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageSetting');
        $data = $this->input();
        $resp = $this->formMapper->updateSettingIntegrate($data, $siteID);
        $this->resp->setBody(json_encode($resp));
    }


}
