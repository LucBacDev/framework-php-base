<?php

namespace Company\User\Model;

class CustomGroupMapper extends \Company\SQL\Mapper {

    protected $autoloadList;

    public function tableAlias() {
        return 'ucg';
    }

    public function tableName() {
        return 'user_custom_group';
    }

    function setLoadList() {
        $this->autoloadList = true;
        return $this;
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);
        if ($this->autoloadList) {
            $entity->list = [];
            if ($entity->id) {
                $entity->list = CustomListMapper::makeInstance()
                        ->filterUserCusGroupFK($entity->id)
                        ->getEntities()
                        ->toArray();
            }
        }

        return $entity;
    }
    
    function filterGroup($group) {
        $this->where('ucg.group = ?', __FUNCTION__)
                ->setParamWhere($group, __FUNCTION__);
        return $this;
    }

}
