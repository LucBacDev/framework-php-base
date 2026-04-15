<?php

namespace Company\User\Controller;

use Company\Auth\Auth;
use Company\User\Model as M;

class RoleCtrl extends \Company\MVC\Controller {

    /**
     * @var Auth 
     */
    protected $auth;

    function __construct(\Company\MVC\MvcContext $context) {
        parent::__construct($context);
        $this->auth = Auth::getInstance();
    }

    function updateRole($siteID, $id = null) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageRole');

        $data = $this->input();
        $data['siteFK'] = $siteID;
        // có vai trò mặc định thì phải có quyền fullcontrol
        if ($data['roleDefault']) {
            $this->auth->requirePrivilege('fullcontrol');
        }

        $result = M\RoleMapper::makeInstance()->updateRole($id, $data);
        $this->resp->setBody(json_encode($result));
    }

    function getRoles($siteID) {
        $this->auth->requireLogin();
        // lấy vai trò mặc định
        $rsPublic = M\RoleMapper::makeInstance()->filterSiteFK('0')->setLoadPrivileges()->getEntities();
        // lấy vai trò của site hiện tại
        $rsPrivate = M\RoleMapper::makeInstance()->filterSiteFK($siteID)->setLoadPrivileges()->getEntities();

        $resp = [
            'rsPublic' => $rsPublic->toArray(),
            'rsPrivate' => $rsPrivate->toArray()
        ];
        $this->resp->setBody(json_encode($resp));
    }

    function getRole($siteID, $id) {
        $this->auth->requireLogin();
        $role = M\RoleMapper::makeInstance()
                ->filterSiteFK($siteID)
                ->filterID($id)
                ->getEntity();
        $this->resp->setBody(json_encode($role));
    }

    function deleteRole($siteID, $id) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireLogin();
        M\RoleMapper::makeInstance()->deleteRole($siteID, $id);

        $this->resp->setBody(json_encode(result(true)));
    }

    function getRoleUser($siteID, $roleID) {
        $this->auth->requireLogin();
        $role = M\RoleMapper::makeInstance()->filterID($roleID)->getEntity();

        //check xem là nhóm mặc định không?
        $sql = "";
        if ($role->siteFK == 0) {
            $sql = 'user_role_user rol ON uu.id=rol.userID AND rol.roleID="' . $roleID . '"';
        } else {
            $sql = 'user_role_user rol ON uu.id=rol.userID AND rol.roleID="' . $roleID . '" AND rol.siteFK="' . $siteID . '"';
        }
        $users = M\UserMapper::makeInstance()
                ->innerJoin($sql)
                ->getEntities();

        $this->resp->setBody(json_encode($users->toArray()));
    }

    function getListCustomDisplay() {
        $listCustomDisplay = M\CustomGroupMapper::makeInstance()
                ->filterGroup('customDisplay')
                ->setLoadList()
                ->getEntities();

        $this->resp->setBody(json_encode($listCustomDisplay->toArray()));
    }

    function getListDiagConfig() {
        $listDiagConfig = M\CustomGroupMapper::makeInstance()
                ->filterGroup('diagConfig')
                ->setLoadList()
                ->getEntities();

        $this->resp->setBody(json_encode($listDiagConfig->toArray()));
    }

    function setUserRoleDefault($siteID, $id) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageRole');

        $result = M\RoleMapper::makeInstance()->setUserRoleDefault($siteID, $id);
        $this->resp->setBody(json_encode($result));
    }

}
