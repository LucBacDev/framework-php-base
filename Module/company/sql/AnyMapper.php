<?php

namespace Company\SQL;


/**
 * Nhiều trường hợp bảng quá nhỏ không đáng để tạo mapper mới sẽ dùng class này<br>
 * Call makeInstance($tableName) 
 */
class AnyMapper extends Mapper {

    static protected $anyMapperFrom;

    public function tableAlias() {
        return $this->from;
    }

    public function tableName() {
        return $this->from;
    }

    function from($table) {
        parent::from($table);
        AnyMapper::$anyMapperFrom = $table;
        return $this;
    }

    function __construct() {
        $this->from = AnyMapper::$anyMapperFrom;
        parent::__construct();
        $this->select('*');
    }

    function filterID($id) {
        $this->where($this->getPkField() . '=?', __FUNCTION__)
                ->setParamWhere($id, __FUNCTION__);
        return $this;
    }

}
