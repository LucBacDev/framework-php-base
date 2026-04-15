<?php

namespace Company\User\Model;

use Company\Auth\Auth;
use Company\Exception as E;
use Company\Exception as Ex;
use Company\MVC\Module;
use Company\Setting\Model\SettingDataMapper;
use Company\Site\Model\SiteMapper;
use Company\SQL\AnyMapper;

class UserMapper extends \Company\SQL\Mapper {

    protected $dbVersion;
    protected $autoloadRoles;
    protected $autholoadPrivs;
    protected $autoloadDep;
    protected $autoloadLogin;

    public function tableAlias() {
        return 'uu';
    }

    public function tableName() {
        return 'user_user';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('uu.createdDate DESC');
        $this->dbVersion = Module::getInstance('company/user')->getMeta()->version;
    }

    function updateUser($id, $input) {
        $isInsert = !$id;
        //validate required
        $required = ['fullname', 'siteFK'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }

        //fields to update
        $fields = ['fullname', 'depFK', 'active', 'noDelete', 'siteFK'];
        $excludeAttrs = array_merge($fields, ['id', 'userLinkID', 'department', 'login', 'privileges', 'createdDate',
            'deleted', 'dbVersion', 'roles', 'version']);
        $attrs = [];
        $fieldData = [];
        foreach($input as $field => $val) {
            if(in_array($field, $fields))
                $fieldData[$field] = $val;
            else if(!in_array($field, $excludeAttrs))
                $attrs[$field] = $val;
        }


         //validate depFK
        if ($fieldData['depFK']) {
            //must exists
            $depFK = $fieldData['depFK'];
            DepartmentMapper::makeInstance()
                    ->filterID($depFK)
                    ->existsOrFail(new E\BadRequestException("Department not found $depFK"));
        }
        
        $fieldData['noDelete'] = arrData($fieldData,'noDelete', 0);

        //validate file
        //validate login
        if ($isInsert && !isset($input['login'])) {
            throw new E\BadRequestException("Require login data");
        }

        if ($isInsert) {
            $id = $fieldData['id'] = uid();
            $fieldData['userLinkID'] = $id;
            $fieldData['siteFK'] = $input['siteFK'];
            $fieldData += [
                'createdDate' => \DateTimeEx::create()->toIsoString(),
                'dbVersion' => $this->dbVersion,
                'active' => (int) arrData($input, 'active', 1)
            ];
        }

        if (isset($input['login'])) {
            if (isset($input['login']['localdb']['password'])) {
                if (php_sapi_name() != "cli")
                    if (!UserMapper::checkPasswordStrength($input['login']['localdb']['password'])) {
                        throw new E\BadRequestException("Password invalid");
                    }
                $input['login']['localdb']['passwd'] = $input['login']['localdb']['password'];
            }
        }

        //update
        $this->startTrans();
        if ($isInsert) {
            $this->insert($fieldData);
            //update attrs
            if(!empty($attrs))
                $this->makeInstance()->filterID($id)->updateJson('attrs', $attrs);
        } else {
            $user = $this->makeInstance()->filterID($id)->filterSiteFK($input['siteFK'])->getEntity();
            if (!$id) {
                throw new E\BadRequestException("User not found");
            }
            $this->makeInstance()
                ->filterID($id)
                ->update($fieldData);

            $privateFields = ['siteFK', 'depFK', 'active', 'createdDate', 'dbVersion', 'userLinkID', 'noDelete'];
            foreach($fieldData as $field => $val) {
                if(in_array($field, $privateFields))
                    unset($fieldData[$field]);
            }
            static::makeInstance()->filterUserLinkID($user->userLinkID)->update($fieldData);

            //update attrs
            if(!empty($attrs))
                $this->makeInstance()->filterUserLinkID($id)->updateJson('attrs', $attrs);
        }

        //update login
        if (isset($input['login'])) {
            if (isset($input['login']['localdb']['password'])) {
                $input['login']['localdb']['passwd'] = $input['login']['localdb']['password'];
                $this->updateLogin($id, $input['login']);
            }
        }

        //update role
        if (isset($input['roles'])) {
            $this->updateUserRole($id, $input['roles'], $input['siteFK']);
        }

        //update priv
        if (isset($input['privileges'])) {
            $this->updateUserPrivilege($id, $input['privileges']);
        }
        $this->completeTransOrFail();

        return result(true, ['id' => $id]);
    }

    protected function updateLogin($userID, $logins) {
        $this->db->delete('user_login', 'userID=?', [$userID]);

        foreach ($logins as $type => $login) {
            //validate
            if (!isset($login['account'])) {
                throw new E\BadRequestException("Invalid login info");
            } elseif (strlen($login['account']) < 5 || strlen($login['account']) > 30) {
                throw new E\BadRequestException("account from 5 to 15 character");
            }

            $loginMethod = \Company\Auth\Auth::getInstance()->getAuthMethod($type);
            $loginMethod->handleLoginUpdate($userID, $type, $login['account'], $login['passwd']);
        }
    }

    function updateUserRole($userID, $roles, $siteFK) {
        $this->db->delete('user_role_user', 'userID=?', [$userID]);
        if (empty($roles)) {
            return;
        }
        
        // check form roles gửi lên có role nào được đặt mặc định ko?
        $roleDefault = false;
        foreach ($roles as $role) {
            if ($role['default']) {
                $roleDefault = true;
                break;
            }
        }

        foreach ($roles as $key => $role) {
            $roleID = $role['id'];
            // format dữ liệu cho default
            $default = $role['default'] ? $role['default'] : 0;
            //check exists
            RoleMapper::makeInstance()
                    ->filterID($roleID)
                    ->existsOrFail(new Ex\BadRequestException("role not found: $roleID"));
            
            // trường hợp trong form ko có role mặc định
            // role đầu tiên được chọn làm mặc định
            if(!$roleDefault){
                $default = ($key == 0) ? 1 : 0;
            }

            $this->db->insert('user_role_user', [
                'userID' => $userID,
                'roleID' => $roleID,
                'default' => $default,
                'siteFK' => $siteFK
            ]);
        }
    }

    function updateUserPrivilege($userID, $privs) {
        $this->db->delete('user_user_privilege', 'userID=?', [$userID]);
        if (empty($privs)) {
            return;
        }
        foreach ($privs as $priv) {
            //check exists
            PrivilegeMapper::makeInstance()
                    ->filterID($priv)
                    ->existsOrFail(new Ex\BadRequestException("priv not found: $priv"));
            $this->db->insert('user_user_privilege', [
                'userID' => $userID,
                'privilegeID' => $priv
            ]);
        }
    }

    /**
     * Tìm user theo thông tin đăng nhập
     * @param string $type
     * @param string $account
     * @param string $password
     * @return $this
     */
    function filterLogin($type, $account, $password = null) {

        $mapper = AnyMapper::makeInstance()
                ->from('user_login')
                ->where('`type`=? AND account=?', __FUNCTION__)
                ->setParamWhere($type, __FUNCTION__ . 1)
                ->setParamWhere($account, __FUNCTION__ . 2);
        if ($password) {
            $mapper->where('passwd=?', __FUNCTION__ . 3)
                    ->setParamWhere($password, __FUNCTION__ . 3);
        }
        $userID = $mapper->select('userID')->getOne();
        if ($userID) {
            $this->filterID($userID);
        } else {
            //không tìm thấy login nên cố tình đặt ĐK sai
            $this->filterID(__METHOD__ . ": account not found");
        }
        return $this;
    }

    function filterActive($active = 1) {
        if ($active === null || $active == 'null') {
            unset($this->where[__FUNCTION__]);
            unset($this->paramsWhere[__FUNCTION__]);
        } else if ($active) {
            $this->where('uu.active=?', __FUNCTION__)
                    ->setParamWhere(1, __FUNCTION__);
        } else {
            $this->where('uu.active=?', __FUNCTION__)
                    ->setParamWhere(0, __FUNCTION__);
        }
        return $this;
    }

    function filterDeleted($deleted = 0) {
        $this->where('uu.deleted=?', __FUNCTION__)
                ->setParamWhere($deleted, __FUNCTION__);
        return $this;
    }

    function filterDepFK($depFK) {
        if ($depFK != NULL) {
            $this->where('uu.depFK=?', __FUNCTION__)
                    ->setParamWhere($depFK, __FUNCTION__);
        }
        return $this;
    }

    function filterFullName($name) {
        $this->where('uu.fullname LIKE ?', __FUNCTION__)
                ->setParamWhere("%$name%", __FUNCTION__);
        return $this;
    }

    function updateLoginCount($id) {
       //TA: should do nothing
    }

    function setLoadRoles() {
        $this->autoloadRoles = true;
        return $this;
    }

    function setLoadPrivileges() {
        $this->autholoadPrivs = true;
        return $this;
    }

    function setLoadDep() {
        $this->autoloadDep = true;
        return $this;
    }

    function setLoadLogin($bool = true) {
        $this->autoloadLogin = true;
        return $this;
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);
        if ($this->autoloadRoles) {
            $entity->roles = [];
            if ($entity->id) {
                $entity->roles = RoleMapper::makeInstance()
                        ->setLoadPrivileges()
                        ->filterUser($entity->id)
                        ->getEntities(function($rawData, $en) use($entity) {
                            $userRole = RoleMapper::makeInstance()->filterUserRoleDefault($entity->id, $entity->siteFK);
                            $en->default = ($en->id == $userRole->roleID) ? 1 : 0;
                        })
                        ->toArray();
            }
        }

        if ($this->autholoadPrivs) {
            $entity->privileges = [];
            if ($entity->id) {
                $entity->privileges = $this->loadPrivs($entity->id, true);
            }
        }

        if ($this->autoloadDep && $entity->id) {
            if ($entity->depFK) {
                $entity->department = DepartmentMapper::makeInstance()
                        ->filterID($entity->depFK)
                        ->getEntity();
            } else {
                $entity->department = DepartmentMapper::makeInstance()->getRootEntity();
            }
        }
        if ($this->autoloadLogin && $entity->userLinkID) {
            $logins = AnyMapper::makeInstance()
                    ->from('user_login')
                    ->where('userID=?')
                    ->setParamWhere($entity->userLinkID, 'where')
                    ->getEntities()
                    ->toArray();
            $entity->login = [];
            foreach ($logins as $login) {
                $entity->login[$login->type] = $login;
            }
        }

        return $entity;
    }

    function loadPrivs($userID, $includeGroupPem = false) {
        if (!$userID) {
            return array();
        }

        $sql = "SELECT privilegeID FROM user_user_privilege WHERE userID='$userID'";
        if ($includeGroupPem) {
            $roles = "SELECT roleID FROM user_role_user WHERE userID='$userID'";
            $sql .= "\nUNION SELECT privilegeID FROM user_role_privilege WHERE roleID IN($roles)";
        }

        return $this->db->GetCol($sql);
    }

    function filterName($name) {
        if ($name) {
            $this->where('uu.fullname LIKE ?', __FUNCTION__)
                    ->setParamWhere("%$name%", __FUNCTION__);
        }
        return $this;
    }

    function filterSiteFK($siteFK) {
        $this->where('uu.siteFK = ?', __FUNCTION__)
                ->setParamWhere($siteFK, __FUNCTION__);
        return $this;
    }

    function deleteUser($siteID, $id, $force = false) {
        //check noDelete
        //check active = 0
        $updateData = [];
        $user = $this->makeInstance()
                ->filterActive(null)
                ->filterSiteFK($siteID)
                ->filterUserLinkID($id)
                ->getEntity();

        if (!$user->id) {
            throw new E\BadRequestException('User not found');
        }

        //chỉ inactive mới được xóa
        if ($user->active) {
            throw new E\BadRequestException('Must deactivate before delete');
        }

        //không cho xóa nodelete
        if (!$force) {
            if ($user->noDelete) {
                throw new E\BadRequestException('this is noDelete dep');
            }
        }

        $this->startTrans();
        $updateData['deleted'] = 1;
        $this->filterSiteFK($siteID)->filterID($id)->filterActive(null)->update($updateData);
        $this->completeTransOrFail();
    }

    function filterUserLinkID($id) {
        $this->where('uu.userLinkID=?', __FUNCTION__)
                ->setParamWhere($id, __FUNCTION__);
        return $this;
    }

    function getSitesID($userID) {
        // lấy danh sách tất cả các user được ghép tài khoản
        $users = $this->filterUserLinkID($userID)->getEntities();
        // các id site được ghép tài khoản
        $arrSiteID = [];
        foreach ($users as $user) {
            $arrSiteID[] = $user->siteFK;
        }

        return $arrSiteID;
    }

    static function checkPasswordStrength($password) {

        $passwordLength = SettingDataMapper::makeInstance()->getSetting('master', "PasswordLength");
        $passwordLength = $passwordLength ? (int) $passwordLength : 6;

        $complexPassword = SettingDataMapper::makeInstance()->getSetting('master', "ComplexPassword");
        $complexPassword = boolval($complexPassword);

        if (strlen($password) < $passwordLength)
            return false;

        if ($complexPassword) {
            $number = preg_match('@[0-9]@', $password);
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);

            return $number && $uppercase && $lowercase && $specialChars;
        }

        return true;
    }

    /**
     * @param type $secondAccount tài khoản phụ
     * @param type $mainAccount tài khoản chính
     * @return $this
     */
    function updateMergeSite($secondAccount, $mainAccount) {
        $updateFields = [ 'userLinkID'];
        $attrs = [];
        foreach ($updateFields as $field) {
            $attrs[$field] = $mainAccount->$field;
        }
        // cập nhật lại dữ liệu cho tài khoản phụ
        $this->startTrans();
        UserMapper::makeInstance()
            ->filterID($secondAccount->id)
            ->update( $attrs);
        // xóa tài khoản phụ khỏi hệ thống
        $this->db->Execute("DELETE FROM user_login WHERE userID='{$secondAccount->id}'");
        $this->completeTransOrFail();
        return $this;
    }
}
