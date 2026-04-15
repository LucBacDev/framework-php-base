<?php

namespace Company\Setting\Model;

use Company\Exception as E;

class FieldMapper extends \Company\SQL\Mapper {

    protected $loadFieldData;
    protected $siteID;

    public function tableAlias() {
        return 'field';
    }

    public function tableName() {
        return 'setting_field';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('field.id');
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);
        if ($this->loadFieldData) {
            $entity->fieldDatas = [];
            if ($entity->id) {
                $entity->fieldDatas = SettingDataMapper::makeInstance()
                                ->filterFieldID($entity->id)
                                ->filterSiteFK($this->siteID)
                                ->getEntities()->toArray();
            }
        }
        return $entity;
    }

    function filterFormID($formID) {
        if ($formID) {
            $this->where('formID = ?', __FUNCTION__)
                    ->setParamWhere($formID, __FUNCTION__);
        }

        return $this;
    }

    function filterLabel($label) {
        if ($label) {
            $this->where('field.label = ?', __FUNCTION__)
                    ->setParamWhere($label, __FUNCTION__);
        }
        return $this;
    }

    function setLoadFieldData($siteID) {
        $this->loadFieldData = true;
        $this->siteID = $siteID;
        return $this;
    }

    function updateField($id, $input) {
        $isInsert = $this->makeInstance()
                        ->filterID($id)
                        ->isExists() ? false : true;

        // validate require
        $required = ['label', 'dataType'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }

        $tableFields = ['formID', 'label', 'dataType', 'defaultVal', 'desc'];
        foreach ($tableFields as $field) {
            $updateData[$field] = arrData($input, $field, '');
        }

        if ($isInsert) {
            $id = $updateData['id'] = $id;
        }

        //update
        $this->startTrans();

        //execute sql
        if ($isInsert) {
            $this->insert($updateData);
        } else {
            $this->makeInstance()
                    ->filterID($id)
                    ->update($updateData);
        }

        $this->completeTransOrFail();

        return result(true, ['id' => $id]);
    }

    function getDataSetting($siteID, $data) {
        $field = $this->makeInstance()
                ->filterLabel($data['name'])
                ->getEntity();
        $result = "";
        if($field->id){
            $setting = SettingDataMapper::makeInstance()->filterID($field->id)->filterSiteFK($siteID)->getEntity();
            $result = $setting->fieldID ? $setting->value : "";
        }
        return $result;
    }

}
