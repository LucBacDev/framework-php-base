<?php

namespace Company\User\Controller;

use Company\Auth\Auth;
use Company\MVC\Json;
use Company\SQL\AnyMapper;
use Company\User\Model as M;

class PrivilegeCtrl extends \Company\MVC\Controller {

    /**
     * @var Auth 
     */
    protected $auth;

    function __construct(\Company\MVC\MvcContext $context) {
        parent::__construct($context);
        $this->auth = Auth::getInstance();
    }

    function getAllPrivs() {
        $this->auth->requireLogin();
        //lấy danh sách tất cả quyền
        $privGroups = M\PrivilegeGroupMapper::makeInstance()->autoloadPrivs()->getEntities();
        $this->resp->setBody(Json::encode($privGroups));
    }

    function getPrivilegesSystem($siteID) {
        $this->auth->requireLogin();
        // lấy danh sách quyền quản trị hệ thống
        $data = M\PrivilegeMapper::makeInstance()
            ->filterDesc('privSystem')
            ->filterSiteFK('master')
            ->getEntities();
        $resp = $data->toArray();

        $this->resp->setBody(json_encode($resp));
    }



}
