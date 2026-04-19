<?php

namespace Company\Department\Controller;

use Company\Auth\Auth;
use Company\Department\Model\DepartmentMapper;

class DepartmentCtrl extends \Company\MVC\Controller {

    /** @var DepartmentMapper */
    protected $depMapper;

    /** @var Auth */
    protected $auth;

    function init() {
        parent::init();
        $this->depMapper = DepartmentMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * Tạo mới hoặc cập nhật phòng ban
     * POST/PUT /:siteID/rest/phongban(/:id)
     */
    function updateDepartment($siteID, $id = null) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireSite($siteID);
            $this->auth->requireAdmin();

            $data = $this->input();
            $data['siteFK'] = $siteID;

            $result = $this->depMapper->updateDepartment($id, $data);
            $this->resp->setBody(json_encode($result));
        } catch (\Exception $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(['result' => false, 'message' => $e->getMessage()]));
        }
    }

    /**
     * Lấy chi tiết 1 phòng ban
     * GET /:siteID/rest/phongban/:id
     */
    function getDepartment($siteID, $id) {
        $this->auth->requireLogin();

        $dep = $this->depMapper->makeInstance()
            ->setLoadAncestors($this->req->get('loadAncestors'))
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->getEntity();

        $this->resp->setBody(json_encode($dep));
    }

    /**
     * Lấy danh sách phòng ban
     * GET /:siteID/rest/phongban
     */
    function getDepartments($siteID) {
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

    /**
     * Xóa phòng ban
     * DELETE /:siteID/rest/phongban/:id
     */
    function deleteDepartment($siteID, $id) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireSite($siteID);
            $this->auth->requireAdmin();

            $this->depMapper->deleteDepartment($siteID, $id);
            $this->resp->setBody(json_encode(result(true)));
        } catch (\Exception $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(['result' => false, 'message' => $e->getMessage()]));
        }
    }

    /**
     * Cập nhật trạng thái active/inactive
     * POST/PUT /:siteID/rest/phongban/:id/active
     */
    function updateStatusActive($siteID, $id) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireSite($siteID);
            $this->auth->requireAdmin();

            $result = $this->depMapper->updateStatusActive($siteID, $id);
            $this->resp->setBody(json_encode($result));
        } catch (\Exception $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(['result' => false, 'message' => $e->getMessage()]));
        }
    }
}
