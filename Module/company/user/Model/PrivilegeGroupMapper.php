<?php

namespace Company\User\Model;

use Company\SQL\Mapper;

class PrivilegeGroupMapper extends Mapper {
    protected $autoloadPrivs = false;


    function tableName()
    {
        return 'user_privilege_group';
    }

    function tableAlias()
    {
        return 'upg';
    }

    function autoloadPrivs(): PrivilegeGroupMapper
    {
        $this->autoloadPrivs = true;
        return $this;
    }

    function makeEntity($rawData)
    {
        $entity = parent::makeEntity($rawData);
        if($this->autoloadPrivs) {
            $entity->privs = PrivilegeMapper::makeInstance()->filterGroup($entity->id)->getEntities();
        }
        return $entity;
    }
}