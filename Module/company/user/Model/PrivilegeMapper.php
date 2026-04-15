<?php

namespace Company\User\Model;

use Company\SQL\AnyMapper;

class PrivilegeMapper extends \Company\SQL\Mapper {

    public function tableAlias() {
        return 'upl';
    }

    public function tableName() {
        return 'user_privilege_list';
    }

    function createGroupIfNotExists($groupID, $name) {
        $groupMapper = AnyMapper::makeInstance()->from("user_privilege_group");
        if ($groupMapper->filterID($groupID)->isExists() == false) {
            $groupMapper->insert(['id' => $groupID, 'name' => $name]);
        }
    }

    function deletePrivileges($privs) {
        foreach ($privs as $priv) {
            $this->db->delete('user_privilege_list', 'privilege=?', [$priv]);
        }
    }

    function createPrivilegeIfNotExists($privilege, $groupID, $name, $desc) {
      
        if (!$privilege || !$name || !$groupID ) {
            throw new \Company\Exception\BadRequestException("Privilege require id, name, privGroupID");
        }
        if ($this->filterID($privilege)->isExists() == false) {
            $this->insert([
                'id' => $privilege,
                'name' => $name,
                'desc' => $desc,
                'privGroupID' => $groupID
            ]);
        }
    }

    function filterGroup($group): PrivilegeMapper
    {
        $this->where('privGroupID=?', __FUNCTION__)->setParamWhere($group, __FUNCTION__);
        return $this;
    }
    
    function filterDesc($desc): PrivilegeMapper
    {
        $this->where('upl.desc = ?', __FUNCTION__)
                ->setParamWhere($desc, __FUNCTION__);
        return $this;
    }

}
