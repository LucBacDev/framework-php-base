<?php

namespace Company\Dict\Model;

use Company\Exception as E;

class CollectionMapper extends \Company\SQL\Mapper {

    public function tableAlias() {
        return 'col';
    }

    public function tableName() {
        return 'dict_collection';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('col.name');
    }

    /**
     * 
     * @param type $id
     * @param type $input
     * @return type
     * @throws E\BadRequestException
     */
    function updateCollection($id, $input) {
        //var_dump($id , $input);        
        $isInsert = $this->makeInstance()
                        ->filterID($id)
                        ->isExists() ? false : true;

        // validate require
        $required = ['name'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }

        // noDelete
        $input['noDelete'] = $input['noDelete'] ? $input['noDelete'] : 0;
        //fields to attrs
        $attrFields = ['noDelete'];
        $attrs = [];
        foreach ($attrFields as $field) {
            $attrs[$field] = arrData($input, $field);
        }

        $tableFields = ['name', 'id'];
        foreach ($tableFields as $field) {
            $updateData[$field] = arrData($input, $field);
        }
        $this->checkFormatItemFields(arrData($input, 'itemFields'));
        $updateData['itemFields'] = json_encode(arrData($input, 'itemFields'));

        // nodelete
        if ($isInsert) {
            $id = $updateData['id'];

            $attrs += [
                'createdDate' => \DateTimeEx::create()->toIsoString()
            ];
        }

        $updateData['attrs'] = json_encode($attrs);
        //update
        //execute sql
        if ($isInsert) {
            $this->insert($updateData);
            // <Doanh>: tạo bảng
            $tableName = $updateData['name'];
            $tableID = $updateData['id'];
            $sql = "CREATE TABLE $tableName($tableID INT(11) AUTO_INCREMENT PRIMARY KEY";
            $fields = json_decode($updateData['itemFields']);
            foreach ($fields as $field) {
                $sql .= ',`' . $field->id . '` ' . $field->dataType;
            }
            $sql .= ')';
            $this->db->Execute($sql);
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
            $this->where('col.name LIKE ?', __FUNCTION__)
                    ->setParamWhere("%$name%", __FUNCTION__);
        }
        return $this;
    }

    /**
     * 
     * @param type $id
     * @param boolean $force true bo qua noDelete
     * @throws E\BadRequestException
     */
    function deleteCollection($id, $force = false) {
        $this->checkBeforeDelete($id);
        $col = $this->makeInstance()
                ->filterID($id)
                ->getEntityOrFail();

        //không cho xóa nodelete=1 
        if (!$force && $col->noDelete) {
            throw new E\BadRequestException('this is noDelete collection');
        }

        $this->startTrans();
        $this->filterID($id)->delete();
        $this->completeTransOrFail();
    }

    /**
     * Không cho xóa nếu còn item phụ thuộc
     * @param type $id
     */
    function checkBeforeDelete($id) {
        $childItem = ItemMapper::makeInstance()->filterCollectionID($id)->getEntity();
        if ($childItem->id) {
            throw new E\BadRequestException("Item exists");
        }
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);
        $entity->itemFields = json_decode($entity->itemFields);
        return $entity;
    }

    function checkFormatItemFields($itemFields) {
        foreach ($itemFields as $itemField) {
            if (!$itemField['id'] || !$itemField['dataType']) {
                throw new E\BadRequestException("Incorrect format");
            }


            if ($itemField['enumVals'] && !is_array($itemField['enumVals'])) {
                throw new E\BadRequestException("Incorrect format enumVals");
            }
            if ($itemField['enumVals']) {
                foreach (array_keys($itemField['enumVals']) as $key) {
                    if (is_int($key)) {
                        throw new E\BadRequestException("Incorrect format enumVals");
                    }
                }
            }
        }
    }

    function createTable($tableName, $itemFields) {
        $this->db->StartTrans();
        $colums = "";
        foreach ($itemFields as $field) {
            $name = $field->id;
            $type = $field->type;
            $defaultValue = $field->defaultValue;
            $comment = $field->comment;
//            $colums = 
        }
        $sql = "CREATE TABLE $tableName(
id VARCHAR(50) PRIMARY KEY,
`value` VARCHAR(255))";
        $this->db->Execute($sql);
        $this->db->CommitTrans();
    }

}
