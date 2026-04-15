<?php

namespace Company\License\Controller;

use Company\Auth\Auth;
use Company\License\Model\LicenseMapper;
use Company\MVC\Hardware;

class LicenseCtrl extends \Company\MVC\Controller {

    /** @var M\UserMapper */
    protected $licenseMapper;
    protected $auth;

    function init() {
        parent::init();
        $this->licenseMapper = LicenseMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    function getLicenses($siteID) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireLogin();
        $data = $this->input();
        //format loadData
        $loadData = intval(arrData($data, 'loadData', 0)) ? 1 : 0;
        $mapper = $this->licenseMapper->makeInstance()
                ->filterSiteFK($siteID);
        if ($loadData) {
            $mapper->loadData();
        }
        $licenses = $mapper->getEntities()
                ->toArray();

        $this->resp->setBody(json_encode($licenses));
    }

    function getLicense($siteID, $id) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireLogin();
        $data = $this->input();
        //format loadData
        $loadData = intval(arrData($data, 'loadData', 0)) ? 1 : 0;
        $mapper = $this->licenseMapper->makeInstance()
                ->filterID($id)
                ->filterSiteFK($siteID);
        if ($loadData) {
            $mapper->loadData();
        }
        $license = $mapper->getEntity();

        $this->resp->setBody(json_encode($license));
    }

    function register($siteID) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $data = $this->input();
        $data['siteFK'] = $siteID;
        $result = $this->licenseMapper->register($data);
        $this->resp->setBody(json_encode($result));
    }

    function uploadLicenseFile($siteID) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $data = $this->input();
        $data['siteFK'] = $siteID;
        $result = $this->licenseMapper->makeInstance()->uploadLicenseFile($data);
        $this->resp->setBody(json_encode($result));
    }

    function refreshLicense($siteID, $licenseID) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $data = $this->input();
        $data['siteFK'] = $siteID;
        $data['id'] = $licenseID;
        $result = $this->licenseMapper->makeInstance()->refreshLicense($data);
        $this->resp->setBody(json_encode($result));
    }

    function downloadHardWareID($siteID) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $hardware = new Hardware();
        $hardwareID = $hardware->hardwareSignature();
        $result = $hardwareID ? result(true, $hardwareID) : result(false, 'Don’t download hardwareID');
        $this->resp->setBody(json_encode($result));
    }

    function returnLicense($siteID, $licenseID) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $data = $this->input();
        $data['siteFK'] = $siteID;
        $data['id'] = $licenseID;
        $result = $this->licenseMapper->makeInstance()->returnLicense($data);
        $this->resp->setBody(json_encode($result));
    }

    function autoCheckLicense($siteID) {
        $this->resp->setBody(json_encode(result(true)));
//        $this->auth->setSiteID($siteID);
//        $this->auth->requireSite($siteID);
//        $result = $this->auth->requireLicense($siteID);
//        $this->resp->setBody(json_encode($result));
    }

}
