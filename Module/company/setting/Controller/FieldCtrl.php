<?php

namespace Company\Setting\Controller;

use Company\Auth\Auth;
use Company\Setting\Model as M;

class FieldCtrl extends \Company\MVC\Controller {

    /** @var M\FieldMapper */
    protected $fieldMapper;
    protected $settingDataMapper;

    /**
     *
     * @var Auth 
     */
    protected $auth;

    function init() {
        parent::init();
        $this->fieldMapper = M\FieldMapper::makeInstance();
        $this->settingDataMapper = M\SettingDataMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * 
     * @param type $id
     * 
     * response
     * {
      "id": "id trường",
      "formID": "id form",
      "label": "tiêu đề trường",
      "dataType": "string",
      "defaultVal": "gía trị mặc định",
      "desc": "Hướng dẫn nhập bằng HTML",
      "value": "Giá trị của trường NSD nhập"
      }
     */
    function getFieldsFormId($siteID, $id = null) {
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageSetting');

        $datas = json_decode($this->settingDataMapper->getSetting($siteID), true);

        $item = [
            "id" => null,
            "formID" => null,
            "label" => null,
            "dataType" => null,
            "defaultVal" => null,
            "desc" => null,
            "value" => null
        ];
        $result = [];

        $fields = $this->fieldMapper->makeInstance()
                ->filterFormID($id)
                ->getEntities();

        foreach ($fields as $field) {
            if ($siteID != "master" && $field->isGlobal == "1")
                continue;

            $item['id'] = $field->id;
            $item['formID'] = $field->formID;
            $item['label'] = $field->label;
            $item['dataType'] = $field->dataType;
            $item['defaultVal'] = $field->defaultVal;
            $item['desc'] = $field->desc;
            foreach ($datas as $key => $value) {
                if ($key == $field->id) {
                    $item['value'] = $value;
                    break;
                }
            }

            $result[] = $item;
        }

        $this->resp->setBody(json_encode($result));
    }

    function updateValueFields($siteID) {
        $this->auth->setSiteID($siteID);
        $this->auth->requireSite($siteID);
        $this->auth->requireAdmin();
        $this->auth->requirePrivilege('manageSetting');

        foreach ($this->input() as $key => $data) {
            if ($key !== 'version') {
                $updateData[$data['id']] = $data['value'];
                $this->settingDataMapper->setSetting($siteID, $updateData);
            }
        }

        $this->resp->setBody(json_encode(result(true)));
    }

    function getDataSetting($siteID) {
        $this->auth->requireAdmin();
        $data = $this->input();
        $res = $this->fieldMapper->getDataSetting($siteID, $data);
        $result = $res ? result(true, $res) :  result(false);
        $this->resp->setBody(json_encode($result));
    }

}
