<?php

namespace Company\Site\Controller;

use Company\Auth\Auth;
use Company\Site\Model as M;
use Pacs\AccessManagement\Model\AccessManagementMapper;

class SiteCtrl extends \Company\MVC\Controller {

    /** @var M\SiteMapper */
    protected $siteMapper;
    protected $auth;

    function init() {
        parent::init();
        $this->siteMapper = M\SiteMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    function updateSite($siteID, $id = null) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireAdmin();
        $this->auth->requireSite($siteID);
        $this->auth->requirePrivilege('manageSite');
        $data = $this->input();
        $result = $this->siteMapper->updateSite($id, $data);

        if (!$id && $result["status"])
            AccessManagementMapper::makeInstance()->initSiteAccess($result["data"]["id"]);

        $this->resp->setBody(json_encode($result));
    }

    function getSite($siteID, $id) {
        $this->auth->requireLogin();
        $site = $this->siteMapper->makeInstance()
                ->filterID($id)
                ->autoloadTags()
                ->getEntityOrFail();
        $this->resp->setBody(json_encode($site));
    }

    function getSites($siteID) {
        $this->auth->requireLogin();

        $pageSize = $this->req->get('pageSize', 10);
        $pageNo = $this->req->get('pageNo', 1);
        $tags = $this->req->get('tags');
        $name = $this->req->get('name');
        $sites = $this->siteMapper->makeInstance()
                ->setPage($pageNo, $pageSize)
                ->filterName($name)
                ->filterTags($tags)
                ->filterActive($this->input('active'))
                ->autoloadTags()
                ->getPage();

        $this->resp->setBody(json_encode($sites));
    }

    function getTags() {
        $this->auth->requireLogin();
        $siteID = $this->req->get("siteID");
        $tags = $this->siteMapper->makeInstance()->getAllTags();
        $this->resp->setBody(json_encode($tags));
    }



    function deleteSite($siteID, $id) {
        $this->auth->requireAdmin();
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);

        $this->auth->requirePrivilege('manageSite');
        $this->siteMapper->deleteSite($id);
        $this->resp->setBody(json_encode(result(true)));
    }


}
