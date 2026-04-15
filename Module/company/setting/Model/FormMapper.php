<?php

namespace Company\Setting\Model;

use Company\Exception as E;

class FormMapper extends \Company\SQL\Mapper {

    protected $loadField;
    protected $loadFieldData;

    public function tableAlias() {
        return 'form';
    }

    public function tableName() {
        return 'setting_form';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('form.name');
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);
        if ($this->loadField) {
            $entity->fields = [];
            if ($entity->id) {
                $fields = FieldMapper::makeInstance()
                        ->filterFormID($entity->id);
                if ($this->loadFieldData) {
                    $fields->setLoadFieldData();
                }
                $entity->fields = $fields->getEntities()->toArray();
            }
        }
        return $entity;
    }

    function setloadField($loadFieldData = true) {
        $this->loadField = true;
        $this->loadFieldData = $loadFieldData;
        return $this;
    }

    function updateForm($id, $input) {
        $isInsert = $this->makeInstance()
                        ->filterID($id)
                        ->isExists() ? false : true;

        // validate require
        $required = ['name'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }

        $tableFields = ['name'];
        foreach ($tableFields as $field) {
            $updateData[$field] = arrData($input, $field);
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

    /**
     * Tìm kiếm like
     * @param string $name
     * @return $this
     */
    function filterName($name) {
        if (strlen($name)) {
            $this->where('form.name LIKE ?', __FUNCTION__)
                    ->setParamWhere("%$name%", __FUNCTION__);
        }
        return $this;
    }

    function updateSettingIntegrate($data, $siteID) {
        $dataUpdateForm = [
            'id' => 'integrate',
            'name' => 'Cấu hình tích hợp HIS-RIS'
        ];
        $id = arrData($dataUpdateForm, 'id');
        // update form
        $resUpdateForm = $this->updateForm($id, $dataUpdateForm);
        if (arrData($resUpdateForm, 'status')) {
            $tableFields = ['id', 'label', 'defaultVal', 'desc'];
            foreach ($data as $field) {
                foreach ($tableFields as $table) {
                    $updateData[$table] = arrData($field, $table, '');
                }
                //set value to formID
                $updateData['formID'] = 'integrate';
                $updateData['dataType'] = 'text';

                // update field
                $resUpdateField = FieldMapper::makeInstance()->updateField($updateData['id'], $updateData);
                if (!arrData($resUpdateField, 'status')) {
                    return result(false);
                }
            }

            //update setting data
            foreach ($data as $setting) {
                $value = is_array($setting['value']) ? json_encode($setting['value']) : $setting['value'];
                $updateSettingData[$setting['id']] = $value;
                SettingDataMapper::makeInstance()->setSetting($siteID, $updateSettingData);
            }

            return result(true);
        }
    }

}
