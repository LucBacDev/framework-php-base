<?php

namespace Company\User\Controller;

use Company\Auth\Auth;
use Company\User\Model as M;

class DepartmentCtrl extends \Company\MVC\Controller {

    /** @var M\DepartmentMapper */
    protected $depMapper;

    /**
     *
     * @var Auth 
     */
    protected $auth;

    function init() {
        parent::init();
        $this->depMapper = M\DepartmentMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    function updateDepartment($siteID, $id = null) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageDepartment');
        $data = $this->input();
        $data['siteFK'] = $siteID;

        $result = $this->depMapper->updateDepartment($id, $data);
        $this->resp->setBody(json_encode($result));
    }

    function getDep($siteID, $id) {
        $this->auth->requireLogin();
        $dep = $this->depMapper->makeInstance()
                ->setLoadAncestors($this->req->get('loadAncestors'))
                ->filterSiteFK($siteID)
                ->filterID($id)
                ->getEntity();

        $this->resp->setBody(json_encode($dep));
    }

    function getDeps($siteID) {
        $this->auth->requireLogin();

        $deps = $this->depMapper->makeInstance()
                ->setLoadAncestors($this->req->get('loadAncestors'))
                ->filterName($this->req->get('name'))
                ->filterCode($this->req->get('code'))
                ->filterParentID($this->req->get('parentID'))
                ->filterNotID($this->req->get('not'))
                ->filterSiteFK($siteID)
                ->limit($this->req->get('limit'))
                ->getEntities();

        $this->resp->setBody(json_encode($deps->toArray()));
    }

    function deleteDep($siteID, $id) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageDepartment');
        $this->depMapper->deleteDep($siteID, $id);
        $this->resp->setBody(json_encode(result(true)));
    }

}
