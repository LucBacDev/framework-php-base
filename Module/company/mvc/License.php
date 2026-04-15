<?php

namespace Company\MVC;

use Company\License\Model\LicenseMapper;

class License {

    protected $hardwareID;
    protected $valid;
    protected $expire;
    protected $errorCode;
    protected $id;
    protected $issueDate;
    static protected $instance;
    protected $license;

    // <Doanh>: url mặc định của license sever
    const licenseUrl = "http://172.16.10.61:1780/license";

    static function getInstance() {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    function __construct() {
        $this->valid = true;
    }

    function decodeLicense($str) {
        $license = strrev($str);
        $license = base64_decode($license);
        $license = json_decode($license, true);
        return $license;
    }

    //check requireLicense
    function requireLicense($license) {
        if (!strlen($license->licenseData)) {
            return result(false, 'License not found');
        }

        $decrypt = Cryptography::getInstance()->decryptStr($license->licenseData);
        $decrypt = Json::decode($decrypt);

        $expiryDate = arrData($decrypt, 'expiryDate');
        $expiryDate = strtotime($expiryDate);

        $todayDate = date('Y-m-d');
        $todayDate = strtotime($todayDate);
        // <Doanh>: check hardwareID
        $hardware = new Hardware();
        $hardwareID = $hardware->hardwareSignature();
        if (arrData($decrypt, 'hardwareID') != $hardwareID) {
            return result(false, 'Data license invalid');
        }
        // <Doanh>: check dateRefresh khi online
        if (arrData($decrypt, 'offline') == 0) {
            $dateRefreshLicense = arrData($decrypt, 'dateRefresh');
            if (!empty($dateRefreshLicense)) {
                $dateExpireRefreshLicense = \DateTimeEx::createFrom_Ymd($dateRefreshLicense)->addDay(5)->toIsoStringYmd(true);
                $dateExpireRefreshLicense = strtotime($dateExpireRefreshLicense);

                if ($todayDate > $dateExpireRefreshLicense) {
                    return result(false, 'Error update license');
                }
            }
        }

        if ($expiryDate < $todayDate || arrData($decrypt, 'status') != 'activated') {
            return result(false, 'License not active');
        }
        return result(true);
    }
    
     //check requireLicense module
    function requireLicenseModule($license, $module) {
        $decrypt = Cryptography::getInstance()->decryptStr($license->licenseData);
        $decrypt = Json::decode($decrypt);
        $modules = arrData($decrypt, 'modules', []);
        if(in_array($module, $modules)){
            return result(true);
        }
        return result(false);
    }

    function returnLicenseOnline($data) {
        $refreshUrl = self::licenseUrl . '/rest/license/client/return';

        //get license from db
        $license = LicenseMapper::makeInstance()
                ->filterID(arrData($data, 'id'))
                ->filterSiteFK(arrData($data, 'siteFK'))
                ->getEntity();

        if (!strlen($license->licenseData)) {
            return $this->resp->setBody(Json::encode(result(false, 'License not found')));
        }

        $decodeData = Cryptography::getInstance()->decryptStr($license->licenseData);
        $decodeData = Json::decode($decodeData);

        $arrPost = ['hardwareID' => arrData($decodeData, 'hardwareID'), 'licenseKey' => arrData($decodeData, 'licenseKey'), 'action' => 'return'];

        $res = curlHttpPost($refreshUrl, $arrPost);

        if ($res === FALSE) {
            return result(false, "Server connection failed");
        }

        $res = Json::decode($res);
        if (arrData($res, 'status')) {
            //xoa license
            $deleteData = [
                'productName' => arrData($decodeData, 'productName'),
                'siteFK' => arrData($data, 'siteFK')
            ];
            LicenseMapper::makeInstance()->deleteLicense($deleteData);

            $result = result(true, 'License return successful');
        } else {
            $result = result(false, arrData($res, 'code'));
        }

        return $result;
    }

    function returnLicenseOffline($data) {
        //get license from db
        $license = LicenseMapper::makeInstance()
                ->filterID(arrData($data, 'id'))
                ->filterSiteFK(arrData($data, 'siteFK'))
                ->getEntity();

        $result = result(false, 'License not exist');
        if ($license && strlen($license->licenseData)) {
            //xoa license
            $deleteData = [
                'productName' => $license->productName,
                'siteFK' => arrData($data, 'siteFK')
            ];
            LicenseMapper::makeInstance()->deleteLicense($deleteData);
            $result = result(true, 'License return successful');
        }
        return $result;
    }

    function refreshLicense($data) {
        $refreshUrl = self::licenseUrl . '/rest/license/client/refresh';

        //get license from db
        $license = LicenseMapper::makeInstance()
                ->filterID(arrData($data, 'id'))
                ->filterSiteFK(arrData($data, 'siteFK'))
                ->getEntity();

        if (!strlen($license->licenseData)) {
            return result(false, "License not exist");
        } else {
            // đọc du lieu license 
            $decodeData = Cryptography::getInstance()->decryptStr($license->licenseData);
            $decodeData = Json::decode($decodeData);

            if (!$decodeData) {
                return result(false, "License not exist");
            }

            $arrPost = ['hardwareID' => arrData($decodeData, 'hardwareID'), 'licenseKey' => arrData($decodeData, 'licenseKey'), 'action' => 'refresh'];

            //gui yeu cau refresh
            $licenseData = curlHttpPost($refreshUrl, $arrPost);

            if (FALSE === $licenseData) {
                return result(false, "Server connection failed");
            }

            $licenseData = Json::decode($licenseData);

            if (arrData($licenseData, 'status')) {
                // Cập nhật DB
                $updateData = [
                    'licenseData' => arrData($licenseData, 'data'),
                    'productName' => arrData($data, 'productName'),
                    'siteFK' => arrData($data, 'siteFK')
                ];

                $resUpdate = LicenseMapper::makeInstance()->updateLicense($updateData);

                $decodeData = Cryptography::getInstance()->decryptStr(arrData($licenseData, 'data'));
                $decodeData = Json::decode($decodeData);

                if (arrData($decodeData, 'status') == 'canceled') {
                    //xoa license
                    $deleteData = [
                        'productName' => arrData($decodeData, 'productName'),
                        'siteFK' => arrData($data, 'siteFK')
                    ];
                    LicenseMapper::makeInstance()->deleteLicense($deleteData);
                    $decodeData = [];
                }

                if (!empty($decodeData)) {
                    return result(true, arrData($resUpdate, 'data'));
                } else {
                    return result(false, 'License status canceled');
                }
            } else {
                if (strlen(arrData($licenseData, 'data'))) {
                    //xoa license
                    $deleteData = [
                        'productName' => arrData($data, 'productName'),
                        'siteFK' => arrData($data, 'siteFK')
                    ];
                    LicenseMapper::makeInstance()->deleteLicense($deleteData);
                }
                return result(false, 'License update db failed');
            }
        }
    }

    function uploadLicenseFile($data) {
        //ktra dinh dang file
        if (isset($_FILES['file'])) {
            $filePost = $_FILES['file']['name'];
            $licenseFileName = 'license.lic';
            $fileTmp = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];

            $productName = arrData($data, 'productName', '');

            $ext = strtolower(substr($filePost, strrpos($filePost, '.') + 1)); // lay ra duoi file upload 
            if ($ext != 'lic') {
                return result(false, 'Error file format license');
            }

            if (!$fileSize) {
                return result(false, 'Data license invalid');
            }

            $uploadDir = BASE_DIR . '/FileUpload/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir)) {
                throw new \Exception("Don't create folder $uploadDir");
            }
            $fileExist = file_exists($uploadDir . $licenseFileName);
            if ($fileExist && !unlink($uploadDir . $licenseFileName)) {
                throw new \Exception("Don't delete folder $uploadDir");
            }
            //upload license
            if (!move_uploaded_file($fileTmp, $uploadDir . $licenseFileName)) {
                throw new \Exception("Error upload license");
            }
            // đọc file license upload de ktra tinh xac thuc
            $decodeData = [];
            if (file_exists($uploadDir . $licenseFileName)) {
                $fp = fopen($uploadDir . $licenseFileName, "r");
                if (!$fp) {
                    throw new \Exception("Don't open file $licenseFileName");
                }
                $fileContent = fread($fp, 8192);
                // decrypt license
                $decodeData = Cryptography::getInstance()->decryptStr($fileContent);
                $decodeData = Json::decode($decodeData);

                // lưu dữ liệu license vào db
                $updateData = [
                    'licenseData' => $fileContent,
                    'productName' => arrData($decodeData, 'productName'),
                    'siteFK' => arrData($data, 'siteFK')
                ];

               $rest = LicenseMapper::makeInstance()->updateLicense($updateData);
            }
            // đọc file request
            $requestFileName = 'request.req';
            $requestData = file_get_contents(BASE_DIR . '/Encrypt/request/' . $requestFileName);
            $requestData = Json::decode($requestData);

            if (file_exists($requestFileName) && !unlink(BASE_DIR . '/Encrypt/request/' . $requestFileName)) {
                throw new \Exception("Don't delete file $requestFileName");
            }
            $decodeData['licenseKey'] = str_replace("-", "", arrData($decodeData, 'licenseKey'));
            $requestData['licenseKey'] = str_replace("-", "", arrData($requestData, 'licenseKey'));

            //check product name active
            $productName = trim($productName);
            $productName = strtoupper($productName);
            $productNameSever = strtoupper(arrData($decodeData, 'productName'));

            if (!empty($productName) && $productName != $productNameSever) {
                //xoa license
                $deleteData = [
                    'productName' => arrData($decodeData, 'productName'),
                    'siteFK' => arrData($data, 'siteFK')
                ];
                LicenseMapper::makeInstance()->deleteLicense($deleteData);
                return result(false, 'License product different');
            } elseif (arrData($decodeData, 'licenseKey') != arrData($requestData, 'licenseKey') ||
                    arrData($decodeData, 'hardwareID') != arrData($requestData, 'hardwareID')
            ) {
                //xoa license
                $deleteData = [
                    'productName' => arrData($decodeData, 'productName'),
                    'siteFK' => arrData($data, 'siteFK')
                ];
                LicenseMapper::makeInstance()->deleteLicense($deleteData);
                return result(false, 'License invalid');
            } else {
                return result(true, 'License registration successful', arrData($rest, 'data'));
            }
        }
    }

    function downloadLicense($data) {
        $licenseKey = arrData($data, 'licenseKey');
        $productName = arrData($data, 'productName', '');
        $fileDir = BASE_DIR . '/Encrypt/request';
        $name = '/request.req';
        //tao file json_data
        if (!is_dir($fileDir) && !mkdir($fileDir, 0777, true)) {
            throw new \Exception("Don't create folder $fileDir");
        }
        if (is_file($fileDir . $name) && !unlink($fileDir . $name)) {
            throw new \Exception("Don't delete folder $fileDir");
        }

        $hardware = new Hardware();
        $hardwareID = $hardware->hardwareSignature();
        $licenseData = ['productName' => $productName, 'licenseKey' => $licenseKey, 'hardwareID' => $hardwareID, 'action' => 'register'];
        $licenseData = json_encode($licenseData);

        if (!file_put_contents($fileDir . $name, $licenseData)) {
            throw new \Exception("Don't write data to the file $name");
        }

        $fp = fopen($fileDir . $name, "rb");

        if (!$fp) {
            throw new \Exception("Don't read file $name");
        }

        return result(true, $licenseData);
    }

    function escapeLicenseKey($licenseKey) {
        //replace+trim
        $licenseKey = str_replace("-", "", $licenseKey);
        return trim($licenseKey);
    }

    function registerLicense($data) {
        $licenseKey = arrData($data, 'licenseKey');
        // product name để active license
        $productName = arrData($data, 'productName', '');
        $hardware = new Hardware();
        $hardwareID = $hardware->hardwareSignature();
        $licenseKey = $this->escapeLicenseKey($licenseKey);
        $arrPost = ['productName' => $productName, 'hardwareID' => $hardwareID, 'licenseKey' => $licenseKey, 'action' => 'register'];
        $fileDir = BASE_DIR . '/Encrypt/request/';
        $fileName = 'request.req';
        if (!is_dir($fileDir) && !mkdir($fileDir, 0777, true)) {
            throw new \Exception("Don't create folder $fileDir");
        }
        if (is_file($fileDir . $fileName) && !unlink($fileDir . $fileName)) {
            throw new \Exception("Don't delete file in folder $fileDir");
        }

        file_put_contents($fileDir . $fileName, Json::encode($arrPost));

        $url = "/rest/license/client/register";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::licenseUrl . $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($arrPost));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = Json::decode(curl_exec($ch));
        curl_close($ch);

        if (arrData($response, 'status')) {
            if (is_file($fileDir . $fileName) && !unlink($fileDir . $fileName)) {
                throw new \Exception("Don't delete file $fileName");
            }
            // decrypt license
            $licenseDecrypt = Cryptography::getInstance()->decryptStr(arrData($response, 'data'));
            $license = Json::decode($licenseDecrypt);

            // lưu dữ liệu license vào db
            $updateData = [
                'licenseData' => arrData($response, 'data'),
                'productName' => arrData($license, 'productName'),
                'siteFK' => arrData($data, 'siteFK')
            ];
            $resUpdate = LicenseMapper::makeInstance()->updateLicense($updateData);
            // Cập nhật db thành công
            if (arrData($resUpdate, 'status')) {
                $result = result(true, 'License registration successful', arrData($resUpdate, 'data'));
            } else {
                $result = result(false, 'License update db failed');
            }
        } else {
            $result = result(false, arrData($response, 'code'));
        }

        return $result;
    }

    function getLicense() {
        $decodeData = [];
        if (strlen($this->license)) {
            $decodeData = Cryptography::getInstance()->decryptStr($this->license);
        }
        return result(true, $decodeData);
    }

}
