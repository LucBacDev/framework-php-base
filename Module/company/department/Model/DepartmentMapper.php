<?php

namespace Company\Department\Model;

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

    /**
     * Tạo mới hoặc cập nhật phòng ban
     * Pattern chuẩn framework: kiểm tra $id để phân biệt insert/update
     */
    function updateDepartment($id, $input) {
        $isInsert = !$id;

        // validate required
        $required = ['name', 'siteFK', 'code'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field] ?? ''))) {
                throw new E\BadRequestException("Thiếu trường bắt buộc: " . $field);
            }
        }

        // lọc các trường cho phép cập nhật
        $attrFields = ['name', 'active', 'noDelete', 'siteFK'];
        $attrs = [];
        foreach ($attrFields as $field) {
            $attrs[$field] = arrData($input, $field);
        }
        $attrs['active'] = $attrs['active'] ? 1 : 0;

        $tableFields = ['parentID', 'code'];
        $updateData = [];
        foreach ($tableFields as $field) {
            $updateData[$field] = arrData($input, $field);
        }

        // validate parentID: nếu có thì phải tồn tại
        if ($updateData['parentID']) {
            $this->makeInstance()
                ->filterID($updateData['parentID'])
                ->existsOrFail(new E\BadRequestException("Phòng ban cha không tồn tại: " . $updateData['parentID']));
        } else {
            $updateData['parentID'] = 0;
        }

        // validate code phải unique
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

        // transaction
        $this->startTrans();

        // trigger trước khi cập nhật
        $triggerParams = [
            'id' => $id,
            'isInsert' => $isInsert,
            'input' => $input,
            'updateData' => $updateData,
            'attrs' => $attrs
        ];
        Trigger::execute('department::beforeUpdate', $triggerParams);

        if ($isInsert) {
            $this->insert(array_merge($updateData, $attrs));
        } else {
            $dep = $this->makeInstance()
                ->filterID($id)
                ->filterActive(null)
                ->filterSiteFK($input['siteFK'])
                ->getEntity();
            if (!$dep->id) {
                throw new E\BadRequestException("Phòng ban không tồn tại");
            }
            $this->makeInstance()
                ->filterID($id)
                ->filterActive(null)
                ->update(array_merge($updateData, $attrs));
        }

        // cập nhật attrs JSON
        $this->makeInstance()
            ->filterID($id)
            ->filterActive(null)
            ->updateJson('attrs', $attrs);

        Trigger::execute('department::afterUpdate', $triggerParams);
        $this->completeTransOrFail();

        // rebuild đường dẫn cây thư mục
        $this->rebuildPath('parentID', 'path', 'id');

        return result(true, ['id' => $id]);
    }

    /**
     * Xóa phòng ban
     */
    function deleteDepartment($siteID, $id, $force = false) {
        // kiểm tra trước khi xóa
        $this->checkBeforeDelete($id);

        $dep = $this->makeInstance()
            ->filterActive(null)
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->getEntity();

        if (!$dep->id) {
            throw new E\BadRequestException('Phòng ban không tồn tại');
        }

        // chỉ inactive mới được xóa
        if ($dep->active) {
            throw new E\BadRequestException('Phải deactivate trước khi xóa');
        }

        // không cho xóa nếu noDelete
        if (!$force) {
            if ($dep->noDelete) {
                throw new E\BadRequestException('Phòng ban này không được phép xóa');
            }
        }

        $this->startTrans();
        $this->makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->filterActive(null)
            ->delete();
        $this->completeTransOrFail();
    }

    /**
     * Kiểm tra trước khi xóa: không cho xóa nếu còn phòng ban con
     */
    function checkBeforeDelete($id) {
        $childDep = $this->makeInstance()->filterParentID($id)->getEntity();
        if ($childDep->id) {
            throw new E\BadRequestException("Còn phòng ban con, không thể xóa");
        }
        Trigger::execute('department::beforeDelete', ['id' => $id]);
    }

    /**
     * Cập nhật trạng thái active
     */
    function updateStatusActive($siteID, $id) {
        $dep = $this->makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->filterActive(null)
            ->getEntity();

        if (!$dep->id) {
            throw new E\BadRequestException("Phòng ban không tồn tại");
        }

        $newActive = $dep->active ? 0 : 1;
        $this->makeInstance()
            ->filterID($id)
            ->filterActive(null)
            ->update(['active' => $newActive]);

        // Cập nhật attrs JSON (Entity đọc active từ attrs, không phải table column)
        $this->makeInstance()
            ->filterID($id)
            ->filterActive(null)
            ->updateJson('attrs', ['active' => $newActive]);

        return result(true, ['active' => $newActive]);
    }

    // ============================================================
    // FILTER METHODS
    // ============================================================

    function filterName($name) {
        if (strlen($name)) {
            $this->where('dep.name LIKE ?', __FUNCTION__)
                ->setParamWhere("%$name%", __FUNCTION__);
        }
        return $this;
    }

    function filterCode($code) {
        if (strlen($code)) {
            $this->where('dep.code=?', __FUNCTION__)
                ->setParamWhere($code, __FUNCTION__);
        }
        return $this;
    }

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

    function filterParentID($parentID) {
        if (strlen($parentID) && $parentID !== null) {
            $this->where('dep.parentID=?', __FUNCTION__)
                ->setParamWhere($parentID, __FUNCTION__);
        }
        return $this;
    }

    function filterSiteFK($siteFK) {
        $this->where('dep.siteFK = ?', __FUNCTION__)
            ->setParamWhere($siteFK, __FUNCTION__);
        return $this;
    }

    function filterNotID($id) {
        if ($id) {
            $this->where('dep.id != ?', __FUNCTION__)
                ->setParamWhere($id, __FUNCTION__);
        }
        return $this;
    }

    // ============================================================
    // LOAD RELATIONS
    // ============================================================

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
        // phần tử cuối là chính nó, loại bỏ
        array_pop($ids);
        $ancestors = [];
        foreach ($ids as $id) {
            $ancestors[] = $this->makeInstance()->filterID($id)->getEntity();
        }
        return array_merge([$this->getRootEntity()], $ancestors);
    }
}
