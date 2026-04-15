<?php

namespace Company\User\Model;

use Company\SQL\AnyMapper;

class CustomListMapper extends \Company\SQL\Mapper {

    public function tableAlias() {
        return 'ucl';
    }

    public function tableName() {
        return 'user_custom_list';
    }

    function createGroupIfNotExists($id, $name, $group) {
        $groupMapper = AnyMapper::makeInstance()->from("user_custom_group");
        if ($groupMapper->filterID($id)->isExists() == false) {
            $groupMapper->insert(['id' => $id, 'name' => $name, 'group' => $group]);
        }
    }

//    function deletePrivileges($privs) {
//        foreach ($privs as $priv) {
//            $this->db->delete('user_privilege_list', 'privilege=?', [$priv]);
//        }
//    }
//
    function createCustomListIfNotExists($customListID, $groupID, $name, $productName, $desc) {
        if (!$customListID || !$name || !$groupID || !$productName || !$desc) {
            throw new \Company\Exception\BadRequestException("CustomList require id, name, userCusGroupFK, productName, desc");
        }
        if ($this->filterID($customListID)->isExists() == false) {
            $this->insert([
                'id' => $customListID,
                'name' => $name,
                'userCusGroupFK' => $groupID,
                'productName' => $productName,
                'desc' => $desc
            ]);
        }
    }

//
//    function filterSiteFK($siteFK) {
//        $this->where('upl.siteFK = ?', __FUNCTION__)
//                ->setParamWhere($siteFK, __FUNCTION__);
//        return $this;
//    }
//    
    function filterUserCusGroupFK($userCusGroupFK) {
        $this->where('ucl.userCusGroupFK = ?', __FUNCTION__)
                ->setParamWhere($userCusGroupFK, __FUNCTION__);
        return $this;
    }
 
    function filterDesc($desc) {
        $this->where('ucl.desc = ?', __FUNCTION__)
                ->setParamWhere($desc, __FUNCTION__);
        return $this;
    }
}
