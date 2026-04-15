<?php

namespace Company\File\Model;

use Company\Exception\BadRequestException;
use Company\SQL\Mapper;

class FileMapper extends Mapper {

    function tableName()
    {
        return 'system_file';
    }

    function tableAlias()
    {
        return 'sfile';
    }

    function makeEntity($rawData)
    {
        return new FileEntity($rawData);
    }

    function updateFile($id, $input) {
        if(isset($input['b64']))
            $input['b64Size'] = strlen($input['b64']);
        if(!$id) {
            //insert
            $requiredFields = ['b64', 'siteID', 'context', 'mime', 'name'];
            foreach($requiredFields as $field) {
                if(!arrData($input, $field))
                    throw new BadRequestException("missing required field: $field");
            }
            $allowedFields = array_merge($requiredFields, ['b64Size']);
            $insert = ['id' => uid(), 'createdDate' => \DateTimeEx::create()->toIsoString()];
            foreach($allowedFields as $field) {
                $insert[$field] = $input[$field];
            }
            $this->insert($insert);
        } else {
            //update
            $allowedFields = ['b64', 'siteID', 'context', 'mime', 'b64Size', 'name'];
            $update = [];
            foreach($allowedFields as $field) {
                if(isset($input[$field]))
                    $update[$field] = $input[$field];
            }
            if(empty($update))
                throw new BadRequestException("input parametaer must not be empty");
            $this->filterID($id)->update($update);
        }
    }

    function filterContext($context) {
        $this->where('context=?', __FUNCTION__)->setParamWhere($context, __FUNCTION__);
        return $this;
    }

    function selectForList() {
        $this->select('id, createdDate, siteID, b64Size, context, mime, `name`');
        return $this;
    }

}