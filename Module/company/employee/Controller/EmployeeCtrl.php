<?php

namespace Company\Employee\Controller;

use Company\Auth\Auth;
use Company\Employee\Model\EmployeeMapper;

class EmployeeCtrl extends \Company\MVC\Controller {

    /** @var EmployeeMapper */
    protected $empMapper;

    /** @var Auth */
    protected $auth;

    function init() {
        parent::init();
        $this->empMapper = EmployeeMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * Tạo mới hoặc cập nhật nhân sự
     * POST/PUT /:siteID/rest/nhansu(/:id)
     */
    function updateEmployee($siteID, $id = null) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireSite($siteID);
            $this->auth->requireAdmin();

            $data = $this->input();
            $data['siteFK'] = $siteID;

            $result = $this->empMapper->updateEmployee($id, $data);
            $this->resp->setBody(json_encode($result));
        } catch (\Exception $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(['result' => false, 'message' => $e->getMessage()]));
        }
    }

    /**
     * Lấy chi tiết 1 nhân sự
     * GET /:siteID/rest/nhansu/:id
     */
    function getEmployee($siteID, $id) {
        $this->auth->requireLogin();

        $emp = $this->empMapper->makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->filterDeleted(0)
            ->setLoadDep()
            ->getEntity();

        $this->resp->setBody(json_encode($emp));
    }

    /**
     * Lấy danh sách nhân sự
     * GET /:siteID/rest/nhansu
     */
    function getEmployees($siteID) {
        $this->auth->requireLogin();

        $pageNo = $this->req->get('pageNo', 1);
        $pageSize = $this->req->get('pageSize', 20);

        // active='' từ frontend nghĩa là "tất cả", không phải "inactive"
        $activeParam = $this->req->get('active');
        if ($activeParam === '' || $activeParam === null) {
            $activeParam = null; // null → filterActive bỏ qua filter
        }

        $mapper = $this->empMapper->makeInstance()
            ->filterFullname($this->req->get('fullname'))
            ->filterCode($this->req->get('code'))
            ->filterEmail($this->req->get('email'))
            ->filterDepFK($this->req->get('depFK'))
            ->filterPosition($this->req->get('position'))
            ->filterActive($activeParam)
            ->filterDeleted(0)
            ->filterSiteFK($siteID)
            ->setPage($pageNo, $pageSize);

        if ($this->req->get('loadDep')) {
            $mapper->setLoadDep();
        }

        $total = 0;
        $mapper->count($total);
        $employees = $mapper->getEntities();

        $this->resp->setBody(json_encode([
            'data' => $employees->toArray(),
            'total' => $total,
            'pageNo' => (int) $pageNo,
            'pageSize' => (int) $pageSize
        ]));
    }

    /**
     * Xóa nhân sự (soft delete)
     * DELETE /:siteID/rest/nhansu/:id
     */
    function deleteEmployee($siteID, $id) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireSite($siteID);
            $this->auth->requireAdmin();

            $this->empMapper->deleteEmployee($siteID, $id);
            $this->resp->setBody(json_encode(result(true)));
        } catch (\Exception $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(['result' => false, 'message' => $e->getMessage()]));
        }
    }

    /**
     * Lấy danh sách nhân sự theo phòng ban
     * GET /:siteID/rest/nhansu/phongban/:depID
     */
    function getEmployeesByDep($siteID, $depID) {
        $this->auth->requireLogin();

        $employees = $this->empMapper->makeInstance()
            ->filterDepFK($depID)
            ->filterActive()
            ->filterDeleted(0)
            ->filterSiteFK($siteID)
            ->setLoadDep()
            ->getEntities();

        $this->resp->setBody(json_encode($employees->toArray()));
    }
}
