<?php

namespace Company\License\Model;

use Company\Exception as E;
use Company\MVC\Cryptography;
use Company\MVC\License;

class LicenseMapper extends \Company\SQL\Mapper {

    protected $dbVersion;
    protected $loadData;
    protected $license;

    public function tableAlias() {
        return 'lic';
    }

    public function tableName() {
        return 'license_license';
    }

    function __construct() {
        parent::__construct();
        $this->license = new License();
    }

    function loadData() {
        $this->loadData = true;
        return $this;
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);
        if ($this->loadData) {
            // decrypt license
            $licenseDecrypt = Cryptography::getInstance()->decryptStr($entity->licenseData);
            $entity->licenseInfo = json_decode($licenseDecrypt);
        }

        return $entity;
    }

    function register($data) {
        //validate required
        $required = ['licenseKey', 'licenseType', 'siteFK'];
        foreach ($required as $field) {
            if (!strlen(trim($data[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }
        
        switch ($data['licenseType']) {
            case 'online':
                $result = $this->license->registerLicense($data);
                break;
            case 'offline':
                $result = $this->license->downloadLicense($data);
                break;
            case 'cloud':

                break;
        }

        return $result;
    }

    function uploadLicenseFile($data) {
        $result = $this->license->uploadLicenseFile($data);
        return $result;
    }

    function refreshLicense($data) {
        //validate required
        $required = ['id', 'productName', 'siteFK'];
        foreach ($required as $field) {
            if (!strlen(trim($data[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }
        $result = $this->license->refreshLicense($data);
        return $result;
    }

    function returnLicense($data) {
        //validate required
        $required = ['id', 'licenseType', 'siteFK'];
        foreach ($required as $field) {
            if (!strlen(trim($data[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }
        switch ($data['licenseType']) {
            case 'online':
                $result = $this->license->returnLicenseOnline($data);
                break;
            case 'offline':
                $result = $this->license->returnLicenseOffline($data);
                break;
            case 'cloud':

                break;
        }
        return $result;
    }

    function updateLicense($updateData) {
        // check license trong hệ thống
        $license = $this->makeInstance()
                ->filterProductName($updateData['productName'])
                ->filterSiteFK($updateData['siteFK'])
                ->getEntity();

        $isInsert = $license->id ? false : true;

        if ($isInsert) {
            $id = $updateData['id'] = uid();
            $this->insert($updateData);
        } else {
            $id = $license->id;
            $this->makeInstance()
                    ->filterID($id)
                    ->update($updateData);
        }
        $this->completeTransOrFail();

        return result(true, ['id' => $id]);
    }

    function deleteLicense($data) {
        $license = $this->makeInstance()
                ->filterProductName($data['productName'])
                ->filterSiteFK($data['siteFK'])
                ->getEntity();

        $this->db->delete('license_license', 'id=?', [$license->id]);
        return result(true, [
            'id' => $license->id
        ]);
    }

    function filterSiteFK($siteFK) {
        $this->where('lic.siteFK = ?', __FUNCTION__)
                ->setParamWhere($siteFK, __FUNCTION__);
        return $this;
    }

    function filterProductName($productName) {
        $this->where('lic.productName = ?', __FUNCTION__)
                ->setParamWhere($productName, __FUNCTION__);
        return $this;
    }

}
