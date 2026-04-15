<?php

namespace Company\User\Model;

use Company\Exception as E;
use Company\MVC\Module;
use Company\MVC\Trigger;

class DepartmentMapper extends \Company\SQL\Mapper {

    protected $dbVersion = '1.0.0';
    protected $loadAncestor;

    public function tableAlias() {
        return 'dep';
    }

    public function tableName() {
        return 'user_department';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('dep.path');
        $this->dbVersion = Module::getInstance('company/user')->getMeta()->version;
    }

    /**
     * Lấy đơn vị gốc
     */
    function getRootEntity() {
        return $this->makeInstance()->makeEntity([
                    'id' => 0,
                    'name' => 'RootDirectory'
        ]);
    }

    function updateDepartment($id, $input) {
        $isInsert = $id ? false : true;

        //validate required
        $required = ['name', 'siteFK', 'code'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field]))) {
                throw new E\BadRequestException("Missing required field: " . $field);
            }
        }

        //fields to update
        $attrFields = ['name', 'active', 'noDelete',  'siteFK'];
        $attrs = [];
        foreach ($attrFields as $field) {
            $attrs[$field] = arrData($input, $field);
        }
        //format du lieu
        $attrs['active'] = $attrs['active'] ? 1 : 0;

        $tableFields = ['parentID', 'code'];
        foreach ($tableFields as $field) {
            $updateData[$field] = arrData($input, $field);
        }

        //validate depID
        if ($updateData['parentID']) {
            //must exists
            $this->makeInstance()
                    ->filterID($updateData['parentID'])
                    ->existsOrFail($updateData['parentID'], new E\BadRequestException("Department not found"));
        } else {
            $updateData['parentID'] = 0;
        }

        //validate unqiue code

        if ($updateData['code']) {
            $this->makeInstance()->uniqueOrFail('code', $updateData['code'], $id);
        }

        if ($isInsert) {
            $id = $updateData['id'] = uid();
            $attrs += [
                'createdDate' => \DateTimeEx::create()->toIsoString(),
                'dbVersion' => $this->dbVersion
            ];
        }

        //update
        $this->startTrans();
        //trigger
        $triggerParams = [
            'id' => $id,
            'isInsert' => $isInsert,
            'input' => $input,
            'updateData' => $updateData,
            'attrs' => $attrs
        ];
        Trigger::execute('user/department::beforeUpdate', $triggerParams);
        //execute sql
        if ($isInsert) {
            $this->insert(array_merge($updateData, $attrs));
        } else {
            $department = $this->makeInstance()->filterID($id)->filterActive(null)->filterSiteFK($input['siteFK'])->getEntity();
            $id = $department->id;
            if(!$id){
              throw new E\BadRequestException("Department not found");
            }
            
            $this->makeInstance()
                    ->filterID($id)
                    ->filterActive(null)
                    ->update(array_merge($updateData, $attrs));
        }

        $this->makeInstance()
                ->filterID($id)
                ->filterActive(null)
                ->updateJson('attrs', $attrs);

        Trigger::execute('user/department::afterUpdate', $triggerParams);
        $this->completeTransOrFail();

        $this->rebuildPath('parentID', 'path', 'id');

        return result(true, ['id' => $id]);
    }

    /**
     * Tìm kiếm like
     * @param string $name
     * @return $this
     */
    function filterName($name) {
        if (strlen($name)) {
            $this->where('dep.name LIKE ?', __FUNCTION__)
                    ->setParamWhere("%$name%", __FUNCTION__);
        }
        return $this;
    }

    /**
     * Tìm kiếm chính xác
     * @param type $code
     * @return $this
     */
    function filterCode($code) {
        if (strlen($code)) {
            $this->where('dep.code=?', __FUNCTION__)
                    ->setParamWhere($code, __FUNCTION__);
        }

        return $this;
    }

    /**
     * 
     * @param type $active null = ko filter, 1 = active, 0 = inactive
     * @return $this
     */
    function filterActive($active = 1) {
        if ($active === null || $active == 'null') {
            unset($this->where[__FUNCTION__]);
            unset($this->paramsWhere[__FUNCTION__]);
        } else if ($active) {
            $this->where('dep.active=?', __FUNCTION__)
                    ->setParamWhere(1, __FUNCTION__);
        } else {
            $this->where('dep.active=?', __FUNCTION__)
                    ->setParamWhere(0, __FUNCTION__);
        }

        return $this;
    }

    function deleteDep($siteID, $id, $force = false) {
        $this->checkBeforeDelete($id);
        $dep = $this->makeInstance()
                ->filterActive(null)
                ->filterSiteFK($siteID)
                ->filterID($id)
                ->getEntity();

        if (!$dep->id) {
            throw new E\BadRequestException('Department not found');
        }
        
        //chỉ inactive mới được xóa
        if ($dep->active) {
            throw new E\BadRequestException('Must deactivate before delete');
        }

        //không cho xóa nodelete
        if (!$force) {
            if ($dep->noDelete) {
                throw new E\BadRequestException('this is noDelete dep');
            }
        }


        //kiểm tra dep con và user con
        $this->makeInstance()
                ->filterParentID($id)
                ->existsThenFail(new E\BadRequestException('child dep exists'));
        UserMapper::makeInstance()
                ->filterDepFK($id)
				->filterActive()
                ->existsThenFail(new E\BadRequestException('user dep exists'));

        $this->startTrans();
        $this->filterSiteFK($siteID)->filterID($id)->filterActive(null)->delete();
        $this->completeTransOrFail();
    }

    function filterParentID($parentID) {
        if (strlen($parentID) && $parentID !== null) {
            $this->where('dep.parentID=?', __FUNCTION__)->setParamWhere($parentID, __FUNCTION__);
        }
        return $this;
    }

    /**
     * Không cho xóa nếu còn department/user phụ thuộc
     * @param type $id
     */
    function checkBeforeDelete($id) {
        $childUser = UserMapper::makeInstance()->filterDepFK($id)->filterActive()->getEntity();
        if ($childUser->id) {
            throw new E\BadRequestException("User exists");
        }

        $childDep = $this->makeInstance()->filterParentID($id)->getEntity();
        if ($childDep->id) {
            throw new E\BadRequestException("Child department exists");
        }

        Trigger::execute('user/department::beforeDelete');
    }

    /**
     * Load các thư mục cha
     * @param type $bool
     * @return $this
     */
    function setLoadAncestors($bool = true) {
        $this->loadAncestor = $bool;
        return $this;
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);

        if ($this->loadAncestor) {
            $entity->ancestors = $this->loadAncestor($entity->path);
        }

        return $entity;
    }

    function loadAncestor($path) {
        $ids = explode('/', trim($path, '/'));
        if (empty($ids)) {
            return [];
        }
        //phần tử cuối là chính nó, cần loại bỏ
        array_pop($ids);
        $ancestors = [];
        foreach ($ids as $id) {
            $ancestors[] = $this->makeInstance()->filterID($id)->getEntity();
        }
        return array_merge([$this->getRootEntity()], $ancestors);
    }

    function filterSiteFK($siteFK) {
        $this->where('dep.siteFK = ?', __FUNCTION__)
                ->setParamWhere($siteFK, __FUNCTION__);
        return $this;
    }

}
