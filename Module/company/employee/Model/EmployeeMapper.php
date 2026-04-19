<?php

namespace Company\Employee\Model;

use Company\Department\Model\DepartmentMapper;
use Company\Exception as E;
use Company\MVC\Module;
use Company\MVC\Trigger;

class EmployeeMapper extends \Company\SQL\Mapper {

    protected $dbVersion;
    protected $autoloadDep;

    public function tableAlias() {
        return 'emp';
    }

    public function tableName() {
        return 'employee';
    }

    function __construct() {
        parent::__construct();
        $this->orderBy('emp.createdDate DESC');
        $this->dbVersion = Module::getInstance('company/employee')->getMeta()->version;
    }

    /**
     * Tạo mới hoặc cập nhật nhân sự
     * Pattern chuẩn framework: kiểm tra $id để phân biệt insert/update
     */
    function updateEmployee($id, $input) {
        $isInsert = !$id;

        // validate required
        $required = ['fullname', 'siteFK'];
        foreach ($required as $field) {
            if (!strlen(trim($input[$field] ?? ''))) {
                throw new E\BadRequestException("Thiếu trường bắt buộc: " . $field);
            }
        }

        // lọc các trường cho phép cập nhật
        $fields = ['fullname', 'code', 'email', 'phone', 'position', 'depFK', 'active', 'noDelete', 'siteFK'];
        $excludeAttrs = array_merge($fields, ['id', 'createdDate', 'deleted', 'dbVersion', 'department']);
        $updateData = [];
        $attrs = [];
        foreach ($input as $field => $val) {
            if (in_array($field, $fields)) {
                $updateData[$field] = $val;
            } else if (!in_array($field, $excludeAttrs)) {
                $attrs[$field] = $val;
            }
        }

        // format dữ liệu
        $updateData['active'] = arrData($updateData, 'active', 1) ? 1 : 0;
        $updateData['noDelete'] = arrData($updateData, 'noDelete', 0) ? 1 : 0;

        // validate depFK: nếu có thì phải tồn tại
        if (!empty($updateData['depFK']) && $updateData['depFK'] != '0') {
            DepartmentMapper::makeInstance()
                ->filterID($updateData['depFK'])
                ->existsOrFail(new E\BadRequestException("Phòng ban không tồn tại: " . $updateData['depFK']));
        }

        // validate code: nếu có thì phải unique
        if (!empty($updateData['code'])) {
            $existing = $this->makeInstance()
                ->filterCode($updateData['code'])
                ->filterSiteFK($input['siteFK'])
                ->getEntity();
            if ($existing->id && $existing->id != $id) {
                throw new E\BadRequestException("Mã nhân sự đã tồn tại: " . $updateData['code']);
            }
        }

        if ($isInsert) {
            $id = $updateData['id'] = uid();
            $updateData += [
                'createdDate' => \DateTimeEx::create()->toIsoString(),
                'dbVersion' => $this->dbVersion,
                'deleted' => 0
            ];
        }

        // transaction
        $this->startTrans();

        $triggerParams = [
            'id' => $id,
            'isInsert' => $isInsert,
            'input' => $input,
            'updateData' => $updateData,
            'attrs' => $attrs
        ];
        Trigger::execute('employee::beforeUpdate', $triggerParams);

        if ($isInsert) {
            $this->insert($updateData);
        } else {
            $emp = $this->makeInstance()
                ->filterID($id)
                ->filterSiteFK($input['siteFK'])
                ->filterActive(null)
                ->filterDeleted(0)
                ->getEntity();
            if (!$emp->id) {
                throw new E\BadRequestException("Nhân sự không tồn tại");
            }
            $this->makeInstance()
                ->filterID($id)
                ->update($updateData);
        }

        // cập nhật attrs JSON
        if (!empty($attrs)) {
            $this->makeInstance()
                ->filterID($id)
                ->updateJson('attrs', $attrs);
        }

        Trigger::execute('employee::afterUpdate', $triggerParams);
        $this->completeTransOrFail();

        return result(true, ['id' => $id]);
    }

    /**
     * Xóa nhân sự (soft delete)
     */
    function deleteEmployee($siteID, $id, $force = false) {
        $emp = $this->makeInstance()
            ->filterActive(null)
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->filterDeleted(0)
            ->getEntity();

        if (!$emp->id) {
            throw new E\BadRequestException('Nhân sự không tồn tại');
        }

        // chỉ inactive mới được xóa
        if ($emp->active) {
            throw new E\BadRequestException('Phải deactivate trước khi xóa');
        }

        // không cho xóa nếu noDelete
        if (!$force) {
            if ($emp->noDelete) {
                throw new E\BadRequestException('Nhân sự này không được phép xóa');
            }
        }

        $this->startTrans();
        // soft delete: đánh dấu deleted = 1
        $this->makeInstance()
            ->filterSiteFK($siteID)
            ->filterID($id)
            ->filterActive(null)
            ->update(['deleted' => 1]);
        $this->completeTransOrFail();
    }

    // ============================================================
    // FILTER METHODS
    // ============================================================

    function filterFullname($name) {
        if ($name) {
            $this->where('emp.fullname LIKE ?', __FUNCTION__)
                ->setParamWhere("%$name%", __FUNCTION__);
        }
        return $this;
    }

    function filterCode($code) {
        if ($code) {
            $this->where('emp.code=?', __FUNCTION__)
                ->setParamWhere($code, __FUNCTION__);
        }
        return $this;
    }

    function filterEmail($email) {
        if ($email) {
            $this->where('emp.email LIKE ?', __FUNCTION__)
                ->setParamWhere("%$email%", __FUNCTION__);
        }
        return $this;
    }

    function filterDepFK($depFK) {
        if ($depFK !== null && $depFK !== '') {
            $this->where('emp.depFK=?', __FUNCTION__)
                ->setParamWhere($depFK, __FUNCTION__);
        }
        return $this;
    }

    function filterActive($active = 1) {
        if ($active === null || $active == 'null') {
            unset($this->where[__FUNCTION__]);
            unset($this->paramsWhere[__FUNCTION__]);
        } else if ($active) {
            $this->where('emp.active=?', __FUNCTION__)
                ->setParamWhere(1, __FUNCTION__);
        } else {
            $this->where('emp.active=?', __FUNCTION__)
                ->setParamWhere(0, __FUNCTION__);
        }
        return $this;
    }

    function filterDeleted($deleted = 0) {
        $this->where('emp.deleted=?', __FUNCTION__)
            ->setParamWhere($deleted, __FUNCTION__);
        return $this;
    }

    function filterSiteFK($siteFK) {
        $this->where('emp.siteFK = ?', __FUNCTION__)
            ->setParamWhere($siteFK, __FUNCTION__);
        return $this;
    }

    function filterPosition($position) {
        if ($position) {
            $this->where('emp.position LIKE ?', __FUNCTION__)
                ->setParamWhere("%$position%", __FUNCTION__);
        }
        return $this;
    }

    // ============================================================
    // LOAD RELATIONS
    // ============================================================

    function setLoadDep() {
        $this->autoloadDep = true;
        return $this;
    }

    function makeEntity($rawData) {
        $entity = parent::makeEntity($rawData);

        if ($this->autoloadDep && $entity->id) {
            if ($entity->depFK && $entity->depFK != '0') {
                $entity->department = DepartmentMapper::makeInstance()
                    ->filterID($entity->depFK)
                    ->getEntity();
            } else {
                $entity->department = DepartmentMapper::makeInstance()->getRootEntity();
            }
        }

        return $entity;
    }
}
