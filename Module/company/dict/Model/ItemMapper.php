<?php

namespace Company\Dict\Model;

use Company\Exception as E;

class ItemMapper extends \Company\SQL\Mapper {

    const DT_STRING = 'string';
    const DT_TEXT = 'text';
    const DT_NUMBER = 'number';
    const DT_ENUM = 'enum';

    public function tableAlias() {
        return 'item';
    }

    public function tableName() {
        return 'dict_item';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('item.value');
    }

    /**
     * 
     * @param type $colllectionID
     * @param type $itemID
     * @param type $input
     * @return type
     * @throws E\BadRequestException
     */
    function updateItem($colllectionID, $id, $input) {
        $isInsert = $id ? false : true;

        $col = CollectionMapper::makeInstance()
                ->filterID($colllectionID)
                ->getEntityOrFail();
        
        $itemFields = $col->itemFields;
        $arrFields = [];
        foreach ($itemFields as $itemField) {
            $arrFields[]=$itemField->id;
        }
     
        $attrs = arrData($input, 'attrs');

        foreach ($attrs as $key => $value) {
            if(!in_array($key, $arrFields)){
                throw new E\BadRequestException("Not exist field : " . $key);
            }
        }

        //fields to attrs
        $attrFields = [];

        // check dataType
        foreach ($itemFields as $itemField) {
            //chỉ lấy các field có trong itemFields
            if (!array_key_exists($itemField->id, $attrs)) {
                continue;
            }

            // lấy giá trị dataType
            switch ($itemField->dataType) {
                case $this::DT_STRING:
                    //@todo length string max 255
                    if (!is_string(arrData($attrs, $itemField->id))) {
                        throw new E\BadRequestException("Incorrect type : " . $itemField->id . " expected string");
                    }
                    break;

                case $this::DT_TEXT:
                    if (!is_string(arrData($attrs, $itemField->id))) {
                        throw new E\BadRequestException("Incorrect type : " . $itemField->id . " expected text");
                    }
                    break;

                case $this::DT_NUMBER:
                    if (!is_numeric(arrData($attrs, $itemField->id))) {
                        throw new E\BadRequestException("Incorrect type : " . $itemField->id . " expected number");
                    }
                    break;

                case $this::DT_ENUM:
                    // get key enum
                    $key = arrData($attrs, $itemField->id);
                    if (!array_key_exists($key, $itemField->enumVals)) {
                        throw new E\BadRequestException("Incorrect type : " . $itemField->id . " expected ");
                    }
                    break;

                default:
                    throw new Exception\BadRequestException("Incorrect type");
            }

            $attrFields[$itemField->id] = arrData($attrs, $itemField->id);
        }



        //lay data input vao theo table fields
        $tableFields = ['value', 'collectionID', 'description', 'sort'];
        foreach ($tableFields as $field) {
            $updateData[$field] = arrData($input, $field);
        }


        if ($isInsert) {
            $id = $updateData['id'] = uid();
            $attrFields += [
                'createdDate' => \DateTimeEx::create()->toIsoString()
            ];
        }
        $updateData['attrs'] = json_encode($attrFields);
   
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

    function filterCollectionID($collectionID) {
        if ($collectionID) {
            $this->where('item.collectionID=?', __FUNCTION__)
                    ->setParamWhere($collectionID, __FUNCTION__);
        }
        return $this;
    }

    /**
     * 
     * @param type $value
     * @return $this
     */
    function filterValue($value) {
        if (strlen($name)) {
            $this->where('item.value LIKE ?', __FUNCTION__)
                    ->setParamWhere("%$value%", __FUNCTION__);
        }
        return $this;
    }

    function deleteItem($id) {
        $item = $this->makeInstance()
                ->filterID($id)
                ->getEntityOrFail();

        $this->startTrans();
        $this->filterID($id)->delete();
        $this->completeTransOrFail();
    }

}
