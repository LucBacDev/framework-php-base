<?php

namespace Company\Cassandra;

use Company\Entity\Entity;
use Company\SQL\Query;

abstract class Mapper extends Query {

    static protected $_db;
    static protected $_debug = 0;
    static protected $instance;
    static protected $configs;

    private $allowFiltering = false;

    protected $cqlOptions = [];
    protected $db;
    protected $paramsWhere = [];

    function __construct()
    {
        $this->db = static::$_db;
        $this->select('*')->from($this->tableName());
    }

    /**
     * @param string $level \Cassandra::CONSISTENCY_ALL
     */
    function setConsistency($level) {
        $this->cqlOptions['consistency'] = $level;
    }

    /**
     * Cho phép override instance của Mapper
     * @param static $instance
     */
    static function setInstance(Mapper $instance) {
        //do Mapper được kế thừa rất nhiều nên dùng self để độc lập các thành phần
        static::$instance = $instance;
    }

    /**
     * @return static
     */
    static function makeInstance() {
        if (self::$instance) {
            $class = get_class(static::$instance);
            return new $class;
        } else {
            return new static;
        }
    }

    abstract function tableName();

    /**
     * Tên trường khóa chính, default = id
     */
    function getPkField() {
        return 'id';
    }

    function makeEntity($rawData) {
        return new Entity($rawData);
    }

    /**
     * instanlize cassandra session object
     */
    static function connect()
    {
        if (self::$configs) {
            $cluster = \Cassandra::cluster();
            //connect to multiple servers

            call_user_func_array(array($cluster, 'withContactPoints'), static::$configs["servers"]);

            if (static::$configs["user"])
            {
                $cluster->withCredentials(static::$configs["user"], static::$configs["pass"]);
            }
//        $cluster->build();
            //use keyspace
            static::$_db = $cluster
                ->build()
                ->connect(static::$configs["keyspace"]);
        }
    }

    /**
     * config cassandra
     * @param $servers
     * @param $user
     * @param $pass
     * @param $keyspace
     * @param boolean $debug
     */
    static function setConfig($servers, $user, $pass, $keyspace, $debug = 0){
        static::$configs["servers"] = $servers;
        static::$configs["user"] = $user;
        static::$configs["pass"] = $pass;
        static::$configs["keyspace"] = $keyspace;

        static::$_debug = $debug;
    }

    /**
     * close cassandra object
     */
    static function closeDB(){
        if (static::$_db)
            static::$_db->close();
    }

    function enableAllowFiltering() {
        $this->allowFiltering = true;
        return $this;
    }

    function getAll()
    {
        if (static::$_db)
            self::connect();

        $ret = $this->execute()->get();
        $res = [];
        while ($ret) {
            foreach ($ret as $row) {
                $res[] = $row;
            }
            $ret = $ret->nextPage();
        }
        return $res;
    }

    function getRow()
    {
        $this->limit(1);
        $all = $this->getAll();
        return isset($all[0]) ? $all[0] : [];
    }

    function getCol()
    {
        $ret = array();
        foreach ($this->getAll() as $row)
        {
            $ret[] = reset($row);
        }
        return $ret;
    }

    function getOne()
    {
        $this->limit(1);
        foreach ($this->getAll() as $row)
        {
            foreach ($row as $val)
            {
                return $val;
            }
        }
        return null;
    }

    /**
     * Get one entity from dataset
     * @param callable $callback
     * @return Entity
     */
    function getEntity($callback = null) {
        $this->limit(1);
        $row = $this->getRow();
        $entity = $this->makeEntity($row);
        if (is_callable($callback)) {
            call_user_func($callback, $row, $entity);
        }
        return $entity;
    }

    /**
     * Get one entity from dataset or fail if not exists
     * @param \Exception $customEx throw NotFound hoặc custom exceptopn
     * @return Entity
     */
    function getEntityOrFail($customEx = null) {
        $entity = $this->getRow();
        if (empty($entity)) {
            if ($customEx) {
                throw $customEx;
            } else {
                throw new \Company\Exception\NotFoundException("Entity not found");
            }
        }

        $entity = $this->makeEntity($entity);
        return $entity;
    }

    /** @return \Collection */
    function getEntities($callback = null) {
        $rows = $this->getAll();
        $rows = $rows ? $rows : array();
        $entities = array();

        foreach ($rows as $row) {
            $entity = $this->makeEntity($row);
            if (is_callable($callback)) {
                call_user_func($callback, $row, $entity);
            }
            $entities[] = $entity;
        }

        return new \Collection($entities);
    }

    /**
     * Kiểm tra điều kiện rỗng hay không
     */
    function isConditionEmpty() {
        return empty($this->where) && empty($this->join);
    }

    /**
     * Check bản ghi tồn tại
     * @return bool true = tồn tại
     */
    function isExists() {
        if ($this->isConditionEmpty()) {
            throw new \BadMethodCallException("isExists must have where");
        }
        $result = $this->getRow();
        if (empty($result))
            return false;

        return true;
    }

    /**
     * Check bản ghi tồn tại, nếu không throw exceptop
     * @param \Exception $customEx Exception do NSD truyền
     * @throws \Company\Exception\NotFoundException
     */
    function existsOrFail($customEx = null) {
        if (!$this->isExists()) {
            if ($customEx) {
                throw $customEx;
            } else {
                throw new \Company\Exception\NotFoundException("existsOrFail");
            }
        }
    }

    /**
     * Nếu bản ghi tồn tại thì fail
     * @param \Exception $customEx Exception do NSD truyền
     * @throws \Company\Exception\NotFoundException
     */
    function existsThenFail($customEx = null) {
        if ($this->isExists()) {
            if ($customEx) {
                throw $customEx;
            } else {
                throw new \Company\Exception\NotFoundException("existsIsFail");
            }
        }
    }

    function execute(){
        if (static::$_db)
            self::connect();

        $query = $this->__toString();

        if ($this->allowFiltering)
            $query .= " ALLOW FILTERING";

        $statement = new \Cassandra\SimpleStatement($query);
        $options = $this->cqlOptions;
        if(!empty($this->paramsWhere)) {
            $options['arguments'] = $this->paramsWhere;
        }
        if (empty($options))
        {
            if (static::$_debug)
            {
                //encode and print debug
                echo "\n<hr>\n" . htmlentities($query) . "\n<hr>\n";
            }

            //return future
            return $this->db->executeAsync($statement);
        }

        if (static::$_debug)
        {
            $_debug = '(cql): ' . $query;
            //replace ? placeholder
            if (isset($options['arguments']))
            {
                foreach ($options['arguments'] as $val)
                {
                    if (!is_numeric($val) && is_string($val))
                    {
                        $val = "'$val'";
                    }
                    $_debug = preg_replace("/\?/", $val, $_debug, 1);
                }
            }
            //encode and print debug
            echo "\n<hr>\n" . htmlentities($_debug) . "\n<hr>\n";
        }
        $prepared = $this->db->prepare($query);
        //return future
        return $this->db->executeAsync($prepared, $options);
    }

    function insert($data) {
        if (static::$_db)
            self::connect();

        $options = $this->cqlOptions;
        $keys = array_keys($data);
        $keys = implode(',', $keys);
        $keyspace = $this->tableName();
        $cql = $this->db->prepare("INSERT INTO $keyspace($keys) VALUES(?" . str_repeat(',?', count($data) - 1) . ")");
        $options['arguments'] = array_values($data);

        try {
            $this->db->execute($cql, $options);
            return true;
        } catch (\Cassandra\Exception $e) {
            var_dump($e->getMessage());
        }

        return false;
    }

    function update($data)
    {
        if (static::$_db)
            self::connect();

        $options = $this->cqlOptions;
        $options['arguments'] = array_values($data);
        $keyspace = $this->tableName();
        $cql = '';
        foreach ($data as $key => $value)
        {
            $cql = $cql ? $cql . ", $key=?" : "UPDATE $keyspace SET $key=?";
        }

        $cql .= ' WHERE ' . implode("\n AND ", $this->where);
        try {
            $this->db->execute($this->db->prepare($cql), $options);
            return true;
        } catch (\Cassandra\Exception $e) {
            var_dump($e->getMessage());
        }
        return false;
    }

    // delete by PK
    function delete() {
        if (static::$_db)
            self::connect();

        $keyspace = $this->tableName();
        $cql = "DELETE FROM $keyspace WHERE " . implode("\n AND ", $this->where);

        return $this->db->execute($cql);
    }

    /**
     * mã hóa mảng key->value thành string map cho cassandra
     */
    function encodeMap($array)
    {
        $ret = array();
        foreach ($array as $k => $v)
        {
            $k = is_numeric($k) ? (double) $k : "'$k'";
            $v = is_numeric($v) ? (double) $v : "'$v'";
            $ret[] = "$k:$v";
        }
        $ret = '{' . implode(',', $ret) . '}';
        return $ret;
    }

    /**
     *
     * @param string $file đường dẫn file CQL
     * @param bool $abortOnError dừng khi gặp lỗi, default = true
     */
    static function importCQL($file, $abortOnError = true) {
        if (static::$_db)
            self::connect();

        $sqls = \Collection::makeInstance(explode(';', file_get_contents($file)))
            ->reject(function($item) { //loại bỏ các query rỗng
                $item = trim($item, " \n\r");
                return empty($item);
            });

        foreach ($sqls as $sql) {

//            try {
                static::$_db->execute($sql);
//            } catch (\Cassandra\Exception $e) {
//                echo "Import $file failed : ",  $e->getMessage(), "\n";
//                if ($abortOnError)
//                    return false;
//            }

        }

        return true;
    }

    function cassandraObjectToString(&$data, $timestampFormat = DATE_RFC3339_EXTENDED_2) {
        foreach ($data as $k => $v) {
            if (is_array($v))
                $data[$k] = array_map(array($this, 'convert'), $v);
            else
                $data[$k] = $this->convert($v, $timestampFormat);
        }
    }

    function convert($inp, $timestampFormat) {
        if ($inp instanceof \Cassandra\Timestamp) {
            $cond = $inp->microtime(true) == $inp->time(); // true neu ko co microtime
            if (!$cond)
                return convertMicrotimeToDate($inp->microtime(true), $timestampFormat);
            else
                return date($timestampFormat, $inp->time());
        }

        if ($inp instanceof \Cassandra\Date)
            return $inp->toDateTime()->format("Y-m-d");

        if ($inp instanceof \Cassandra\Bigint)
            return $inp->value();

        return $inp;
    }
}