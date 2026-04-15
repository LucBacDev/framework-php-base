<?php

namespace Company\User\Model;

use Company\Exception as E;
use Company\SQL\AnyMapper;

class RoleMapper extends \Company\SQL\Mapper {

    protected $autoloadPrivs = [];

    public function tableAlias() {
        return 'role';
    }

    public function tableName() {
        return 'user_role_list';
    }

    function updateRole($id, $input) {
        $isInsert = $this->makeInstance()->filterID($id)->isExists() ? false : true;
        $attrs = arrData($input, 'attrs', []);

        //validate required
        $required = ['name', 'siteFK', 'id'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }

        $tableFields = ['name', 'siteFK'];
        $updateData['id'] = $input['id'];
        foreach ($tableFields as $field) {
            $updateData[$field] = arrData($input, $field);
        }

        // roleDefaul sẽ lưu lại siteFK
        if ($input['roleDefault']) {
            $updateData['siteFK'] = 0;
        }

        //update
        $this->startTrans();

        //update attrs
        $attrs['noDelete'] = $id == 'admin' ? 1 : 0;

        //execute sql
        if ($isInsert) {
            $id = $updateData['id'];
            $this->insert($updateData);
        } else {
            $role = $this->makeInstance()->filterID($id)->getEntity();
            if ($role->siteFK != 0) {
                // không là vai trò mặc định sẽ check tồn tại của role trên site đó
                $roleCheck = $this->makeInstance()->filterID($id)->filterSiteFK($input['siteFK'])->getEntity();
                $id = $roleCheck->id;
                if (!$id) {
                    throw new E\BadRequestException("Role not found");
                }
            }

            $this->makeInstance()
                    ->filterID($id)
                    ->update($updateData);
        }



        $this->makeInstance()
                ->filterID($id)
                ->updateJson('attrs', $attrs);

        //update users in role
        if (isset($input['users'])) {
            $this->updateRoleUser($id, $input['users']);
        }
        //update piv in role
        if (isset($input['privileges'])) {
            $this->updateRolePrivilege($id, $input['privileges']);
        }



        $this->completeTransOrFail();

        return result(true, ['id' => $id]);
    }

    //<Doanh>: cập nhật nhóm
    function updateRoleUser($roleID, $userIDs) {
        $roles = AnyMapper::makeInstance()
                        ->from('user_role_user')
                        ->where('`roleID`=?', __FUNCTION__)
                        ->setParamWhere($roleID, __FUNCTION__ . 1)
                        ->getEntities()->toArray();

        // danh sách userID có trong db
        $idUsers = [];

        foreach ($roles as $role) {
            $idUsers[] = $role->userID;
            // thành viên bị loại
            if (!in_array($role->userID, $userIDs)) {
                $userRole = $this->filterUserRoleDefault($role->userID, $role->siteFK);
                // thành viên bị loại có nhóm này là mặc định
                if ($userRole->roleID) {
                    // cập nhật nhóm khác là mặc định cho thành viên bị loại này
                    $users = AnyMapper::makeInstance()
                                    ->from('user_role_user')
                                    ->where('`userID`=? AND roleID != ?', __FUNCTION__)
                                    ->setParamWhere($userRole->userID, __FUNCTION__ . 1)
                                    ->setParamWhere($userRole->roleID, __FUNCTION__ . 2)
                                    ->getEntities()->toArray();
                    if (!count($users)) {
                        throw new E\BadRequestException("Don't user in role");
                    }
                    // cập nhật
                    $this->db->Execute("UPDATE `user_role_user` SET `default` = 1 WHERE `roleID` =  ? AND `userID` = ?", [$users[0]->roleID, $users[0]->userID]);
                }
                // xóa bản ghi
                $this->db->delete('user_role_user', 'roleID=? AND userID=?', [$role->roleID, $role->userID]);
            }
        }

        // tạo thành viên mới
        foreach ($userIDs as $userID) {
            // không có trong mảng ID từ db => là thành viên mới
            if (!in_array($userID, $idUsers)) {
                UserMapper::makeInstance()
                        ->filterID($userID)
                        ->getEntityOrFail(new E\NotFoundException("invalid userID: $userID"));
                // lất siteFK của user
                $user = UserMapper::makeInstance()
                        ->filterID($userID)
                        ->getEntity();

                // thêm thành viên mới vào db
                $userRoleDataUpdate = [
                    'userID' => $userID,
                    'roleID' => $roleID,
                    'siteFK' => $user->siteFK
                ];
                // kiểm tra thành viên mới đã có nhóm mặc định nào trước đó chưa?
                $userRole = $this->filterUserRoleDefault($userID, $user->siteFK);
                $userRoleDataUpdate['default'] = $userRole->roleID ? 0 : 1;

                $this->db->insert('user_role_user', $userRoleDataUpdate);
            }
        }
    }

    function updateRolePrivilege($roleID, $privileges) {
        $this->db->delete('user_role_privilege', 'roleID=?', [$roleID]);
        if (!is_array($privileges)) {
            return;
        }
        foreach ($privileges as $privID) {
            PrivilegeMapper::makeInstance()
                    ->filterID($privID)
                    ->getEntityOrFail(new E\NotFoundException("invalid privID: $privID"));
            $this->db->insert('user_role_privilege', [
                'roleID' => $roleID,
                'privilegeID' => $privID
            ]);
        }
    }

    /**
     * Tìm kiếm tất cả bản role thuộc userID
     * @param string $userID
     * @return $this
     */
    function filterUser($userID) {
        $roles = AnyMapper::makeInstance()
                ->select('roleID', true)
                ->from('user_role_user')
                ->where('userID=?', __FUNCTION__)
                ->setParamWhere($userID, __FUNCTION__)
                ->getCol();
        if (empty($roles)) {
            $this->filterID(__METHOD__ . ': not found any role match user ' . $userID, __FUNCTION__);
        } else {
            $this->filterID($roles);
        }

        return $this;
    }

    function filterSiteFK($siteFK) {
        $this->where('role.siteFK = ?', __FUNCTION__)
                ->setParamWhere($siteFK, __FUNCTION__);
        return $this;
    }

    //<Doanh>: filter user role default
    function filterUserRoleDefault($userID, $siteID, $default = 1) {
        if (!$userID || !$siteID) {
            return $this;
        }

        $userRole = AnyMapper::makeInstance()
                ->from('user_role_user')
                ->where('`userID`=? AND `default`=? AND `siteFK`=?', __FUNCTION__)
                ->setParamWhere($userID, __FUNCTION__ . 1)
                ->setParamWhere($default, __FUNCTION__ . 2)
                ->setParamWhere($siteID, __FUNCTION__ . 3)
                ->getEntity();

        return $userRole;
    }

    function setLoadPrivileges() {
        $this->autoloadPrivs = true;
        return $this;
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);
        if ($this->autoloadPrivs) {
            $entity->privileges = [];
            if ($entity->id) {
                $entity->privileges = AnyMapper::makeInstance()
                        ->select('privilegeID')
                        ->from('user_role_privilege')
                        ->where('roleID=?', __FUNCTION__)
                        ->setParamWhere($entity->id, __FUNCTION__)
                        ->getCol();
            }
        }

        return $entity;
    }

    function deleteRole($siteID, $id) {
        //check noDelete
        $role = $this->filterSiteFK($siteID)->filterID($id)->getEntity();

        if (!$role->id) {
            throw new E\BadRequestException('Role not found');
        }

        if ($role->noDelete) {
            throw new E\BadRequestException("noDelete");
        }

        $this->startTrans();
        $this->filterSiteFK($siteID)->filterID($id)->delete();
        $this->completeTransOrFail();
    }

}
