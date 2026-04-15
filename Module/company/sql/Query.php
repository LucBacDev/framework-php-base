<?php

namespace Company\SQL;

class Query extends \Macroable {

    protected $query;
    public $select = array();
    public $from;
    public $join = array();
    public $where = array();
    public $orderBy;
    public $limit;
    public $offset;
    public $groupBy;
    public $having;
    public $lockInShareMode = false;

    static function makeInstance() {
        return new static;
    }

    /**
     * 
     * @param string|array $fields
     * @param string $override default FALSE, TRUE sẽ xóa hết select cũ
     */
    function select($fields, $override = true) {
        if ($override) {
            $this->select = (array) $fields;
        } else {
            $this->select[] = $fields;
        }
        return $this;
    }

    /**
     * Tên bảng chính
     * @param string $table
     */
    function from($table) {
        $this->from = $table;
        return $this;
    }

    /**
     * 
     * @param string $table
     * @param string $key
     */
    function join($table, $key = null) {
        if ($key === null) {
            $this->join[] = "JOIN $table";
        } else {
            $this->join[$key] = $table;
        }
        return $this;
    }

    /**
     * 
     * @param string $table
     * @param string $key
     */
    function innerJoin($table, $key = null) {
        if ($key === null) {
            $this->join[] = "INNER JOIN $table";
        } else {
            $this->join[$key] = "INNER JOIN $table";
        }
        return $this;
    }

    /**
     * 
     * @param string $table
     * @param string $key
     */
    function leftJoin($table, $key = null) {
        if ($key === null) {
            $this->join[] = "LEFT JOIN $table";
        } else {
            $this->join[$key] = "LEFT JOIN $table";
        }
        return $this;
    }

    /**
     * 
     * @param string $table
     * @param string $key
     */
    function fullJoin($table, $key = null) {
        if ($key === null) {
            $this->join[] = "FULL JOIN $table";
        } else {
            $this->join[$key] = "FULL JOIN $table";
        }
        return $this;
    }

    /**
     * 
     * @param string $table
     * @param string $key
     */
    function rightJoin($table, $key = null) {
        if ($key === null) {
            $this->join[] = "RIGHT JOIN $table";
        } else {
            $this->join[$key] = "RIGHT JOIN $table";
        }
        return $this;
    }

    /**
     * 
     * @param string $statement
     * @param mixed $where_key
     */
    function where($statement, $where_key = null) {
        if ($where_key OR $where_key === 0 OR $where_key === '0') {
            if ($statement !== null && $statement !== false) {
                $this->where[$where_key] = $statement;
            } elseif (isset($this->where[$where_key])) {
                unset($this->where[$where_key]);
            }
        } else {
            $this->where[] = $statement;
        }
        return $this;
    }

    /**
     * 
     * @param string $field
     */
    function orderBy($field) {
        $this->order_by = $this->escapeString($field);
        return $this;
    }

    /**
     *
     * @param string $field
     */
    function groupBy($field) {
        $this->groupBy = $this->escapeString($field);
        return $this;
    }

    /**
     * 
     * @param int $limit
     * @param int $offset
     */
    function limit($limit, $offset = null) {
        $this->limit = (int) $limit;
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * 
     * @param int $offset
     */
    function offset($offset) {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * 
     * @param bool $if_expression
     * @return static
     */
    function when($if_expression) {
        return $this->decorate(new If_Logic_Decorator($if_expression));
    }

    /**
     * 
     * @return string
     */
    function __toString() {
        $select = empty($this->select) ? '*' : implode(',', $this->select);
        $sql = "SELECT $select FROM $this->from";
        if (!empty($this->join)) {
            $sql .= "\n" . implode("\n", $this->join);
        }
        if (!empty($this->where)) {
            $sql .= "\nWHERE " . implode("\n AND ", $this->where);
        }

        if (!empty($this->groupBy)) {
            $sql .= "\nGROUP BY $this->groupBy";
        }
        if (!empty($this->having)) {
            $sql .= "\nHAVING $this->having";
        }
        if (!empty($this->order_by)) {
            $sql .= "\nORDER BY $this->order_by";
        }
        if (!empty($this->limit)) {
            $sql .= "\nLIMIT {$this->limit}";
            if (!empty($this->offset)) {
                $sql .= "\nOFFSET $this->offset";
            }
        }
        if ($this->lockInShareMode)
            $sql .= " LOCK IN SHARE MODE";
        return $sql;
    }

    function escapeString($str) {
        $arr_search = array('&', '<', '>', '"', "'", '/', "\\", "\\");
        $arr_replace = array();
        foreach ($arr_search as $v) {
            $arr_replace[] = htmlentities($v);
        }
        return str_replace($arr_search, $arr_replace, $str ?? "");
    }

    function lockInShareMode($value = true) {
        $this->lockInShareMode = $value;
        return $this;
    }
}
