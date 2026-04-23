<?php

namespace Company\SQL;
use Company\Cache\CacheDriver;
use InvalidArgumentException;

class DB {

    /** @var static */
    static protected $_instance = [];
    static protected $config=[];
    const CACHE_ALIVE_HOST = 'sqlAliveHost';

    /** @var \ADOConnection */
    protected $conn;
    protected $cachedDate;
    protected $type;
    protected $hosts;
    protected $user;
    protected $pass;
    protected $db;
    protected $debug;


//    static function config($connName, $type, $hosts, $user, $pass, $db, $debug) {
//        static::$_instance[$connName] = new static($type, $hosts, $user, $pass, $db, $debug);
//    }

    static function setConfig($connName, $type, $hosts, $user, $pass, $db, $debug){
        static::$config[$connName] = [
            "type" => $type,
            "hosts" => $hosts,
            "user" => $user,
            "pass" => $pass,
            "db" => $db,
            "debug" => $debug
        ];

    }

    /** @return static */
    static function getInstance($name = 'default') {
        if (isset(static::$_instance[$name])) {
            return static::$_instance[$name];
        }

        if (isset(static::$_instance['default'])) {
            return static::$_instance['default'];
        }

        throw new \BadMethodCallException("DB conn not exists: $name");
    }

    static function Connect(){
        foreach (static::$config as $connName => $connConfig) {
            static::$_instance[$connName] = new static(
                $connConfig["type"],
                $connConfig["hosts"],
                $connConfig["user"],
                $connConfig["pass"],
                $connConfig["db"],
                $connConfig["debug"]
            );
        }

    }

    static function Disconnect(){
        foreach (static::$_instance as $connName => $instance) {
            static::$_instance[$connName]->close();
        }
    }

    function close()
    {
        if($this->conn == null)
            return;
        $this->conn->close();
        unset($this->conn);
    }

    function __construct($type, $hosts, $user, $pass, $db, $debug) {
        $this->type = $type;
        $this->hosts = is_array($hosts) ? $hosts : [$hosts];
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
        $this->debug = $debug;

    }

    public function autoConnect() {
        if($this->conn && $this->conn->isConnected()) {
            return;
        }

        //when one DB server in cluster dies, PHP will cache
        //died hosts so that next time they will not request to it in some time
        $cacheKey = static::CACHE_ALIVE_HOST . $this->db;
        $cache = CacheDriver::getInstance(CacheDriver::PRIVATE_MEMORY_CACHE);
        $aliveSqlHost = $cache->get($cacheKey);

        $this->conn = \NewADOConnection($this->type);
        $this->conn->debug = $this->debug;

        $host = $cachedHost = false;
        if(in_array($aliveSqlHost, $this->hosts)){
            $host  = $cachedHost = $aliveSqlHost;
        }

        $status = false;
        if ($this->debug) {
            echo "\nconnect sql\n<hr>";
        }
        if ($host) {
            //connect to first host
            $status = $this->conn->Connect($host, $this->user, $this->pass, $this->db);
        }
        if (!$status) {
            foreach ($this->hosts as $host) {
                if ($host == $cachedHost) {
                    continue; //ignore failed host
                }
                $status = $this->conn->Connect($host, $this->user, $this->pass, $this->db);
                if ($status) {
                    break;
                }
            }
        }
        if ($status) {
            $cache->set($cacheKey, $host, 60);
            $conn = $this->conn;
            register_shutdown_function(function () use ($conn) {
                //TA: close conn to prevent transaction deadlock
                try {
                    //execute wrong query to rollback trans
                    if ($conn->transOff > 0) {
                        $conn->execute("try wrong sql");
                        $conn->CompleteTrans();
                    }
                    @$conn->Close();
                } catch (\Exception $ex) {
                }
            });
        } else {
            throw new \Exception("Cannot connect SQL Database");
        }
        $this->conn->SetCharSet('utf8');
        $this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
    }

    function __call($name, $arguments) {
        $this->autoConnect();

        $callback = array($this->conn, $name);
        if (!is_callable($callback)) {
            throw new \Exception('DB not exists::' . $name);
        }
        return call_user_func_array($callback, $arguments);
    }

    function __get($name) {
        $this->autoConnect();

        if (isset($this->conn->{$name})) {
            return $this->conn->{$name};
        }
    }

    function __set($name, $value) {
        $this->autoConnect();

        $this->conn->{$name} = $value;
    }

    function getDate($cache = true) {
        if (!$this->cachedDate || !$cache) {
            $this->cachedDate = $this->GetOne("SELECT NOW()");
        }
        return $this->cachedDate;
    }

    /**
     *
     * @param string $table Tên bảng
     * @param string $where Điều kiện
     * @param array $params Tham số
     * @return int Số bản ghi xóa hoặc FALSE
     */
    public function delete($table, $where, $params = array()) {
        $sql = "DELETE FROM $table WHERE $where";
        $result = $this->Execute($sql, $params);
        if ($result == false) {
            return false;
        } else {
            return $this->Affected_Rows();
        }
    }

    /**
     * Insert vào CSDL
     * @param string $table Tên bảng
     * @param array $arr_data Mảng dữ liệu
     * @param string $id Tên trường khóa chính
     * @return boolean FALSE nếu thất bại
     * @throws InvalidArgumentException
     */
    public function insert($table, $arr_data, $id = null) {
        if (!is_array($arr_data)) {
            throw new \InvalidArgumentException('$arr_data Phải là array $k=>$v');
        }
        if (empty($arr_data)) {
            throw new \InvalidArgumentException('$arr_data Không được empty()');
        }
        foreach ($arr_data as $field => $val) {
            if (strpos($field, '`') === false) {
                unset($arr_data[$field]);
                $arr_data["`$field`"] = $val;
            }
        }
        $sql = "INSERT INTO $table(" . implode(',', array_keys($arr_data)) . ") VALUES(?" . str_repeat(',?', count($arr_data) - 1) . ")";
        return $this->Execute($sql, array_values($arr_data));
    }

    /**
     * Replace vào CSDL
     * @param string $table Tên bảng
     * @param array $arr_data Mảng dữ liệu
     * @param string $id Tên trường khóa chính
     * @return boolean FALSE nếu thất bại
     * @throws InvalidArgumentException
     */
    public function replace($table, $arr_data, $id = null) {
        if (!is_array($arr_data)) {
            throw new \InvalidArgumentException('$arr_data Phải là array $k=>$v');
        }
        if (empty($arr_data)) {
            throw new \InvalidArgumentException('$arr_data Không được empty()');
        }
        foreach ($arr_data as $field => $val) {
            if (strpos($field, '`') === false) {
                unset($arr_data[$field]);
                $arr_data["`$field`"] = $val;
            }
        }
        $sql = "REPLACE INTO $table(" . implode(',', array_keys($arr_data)) . ") VALUES(?" . str_repeat(',?', count($arr_data) - 1) . ")";
        return $this->Execute($sql, array_values($arr_data));
    }

    function Execute($sql, $inputArray = []) {
        $this->autoConnect();

        return $this->conn->Execute($sql, $inputArray);
    }

    /**
     * Insert nhiều row bằng một lệnh SQL
     * @param string $table
     * @param array $arr_data Mảng 2 chiều
     * @param bool $onDuplicateKeyUpdate
     * @throws InvalidArgumentException
     */
    function insertMany($table, $arr_data, $onDuplicateKeyUpdate = false) {

        if (!is_array($arr_data)) {
            throw new InvalidArgumentException('$arr_data Phải là array $k=>$v');
        }
        if (empty($arr_data)) {
            throw new InvalidArgumentException('$arr_data Không được empty()');
        }
        $first_row = $arr_data[0];
        $fields = implode(",", array_keys($first_row));
        $params = array();
        $count_fields = count($first_row);
        $sql = "INSERT INTO $table($fields) VALUES(?" . str_repeat(",?", $count_fields - 1) . ")";
        $sql .= str_repeat(",(? " . str_repeat(",?", $count_fields - 1) . ")", count($arr_data) - 1);

        if ($onDuplicateKeyUpdate) {
            $sql .= " ON DUPLICATE KEY UPDATE ";
            foreach (array_keys($first_row) as $field)
                $sql .= "$field = VALUES($field),";
            $sql = rtrim($sql, ", ");
        }

        foreach ($arr_data as $row) {
            $params = array_merge($params, array_values($row));
        }

        return $this->Execute($sql, $params);
    }

    /**
     * replace nhiều row bằng một lệnh SQL
     * @param string $table
     * @param array $arr_data Mảng 2 chiều
     * @throws InvalidArgumentException
     */
    function replaceMany($table, $arr_data) {
        if (!is_array($arr_data)) {
            throw new InvalidArgumentException('$arr_data Phải là array $k=>$v');
        }
        if (empty($arr_data)) {
            throw new InvalidArgumentException('$arr_data Không được empty()');
        }
        $first_row = $arr_data[0];
        $fields = implode(",", array_keys($first_row));
        $params = array();
        $count_fields = count($first_row);
        $sql = "REPLACE INTO $table($fields) VALUES(?" . str_repeat(",?", $count_fields - 1) . ")";
        $sql .= str_repeat(",(? " . str_repeat(",?", $count_fields - 1) . ")", count($arr_data) - 1);
        foreach ($arr_data as $row) {
            $params = array_merge($params, array_values($row));
        }

        return $this->Execute($sql, $params);
    }

    /**
     * Update CSDL
     * @param string $table Tên bảng
     * @param array $arr_data Mảng dữ liệu
     * @param string $where Điều kiện. VD: '1=1'
     * @param array $params Mảng tham số
     * @return int Số bản ghi Update hoặc FALSE
     * @throws Exception
     */
    public function update($table, $arr_data, $where, $where_params = array()) {

        $this->autoConnect();
        if (empty($arr_data)) {
            throw new \Exception('$arr_data Không được empty()');
        }
        $sql = '';
        $params = array();
        if (is_array($arr_data)) {
            foreach ($arr_data as $k => $v) {
                if (strpos($k, '`') === false) {
                    $k = "`$k`";
                }
                $sql .= strlen($sql) > 0 ? ",$k=?" : "UPDATE $table SET $k=?";
                array_push($params, $v);
            }
        } else {
            $sql = "UPDATE $table SET $arr_data";
        }
        $sql .= " WHERE $where";
//        var_dump($sql, array_merge($params, $where_params));die;
        $result = $this->Execute($sql, array_merge($params, $where_params));
        if ($result == false) {
            return false;
        } else {
            return $this->Affected_Rows();
        }
    }

    /**
     *
     * @param string $file đường dẫn file SQL
     * @param bool $abortOnError dừng khi gặp lỗi, default = true
     */
    function importSQL($file, $abortOnError = true) {
        $this->autoConnect();

        $sqls = \Collection::makeInstance(explode(';', file_get_contents($file)))
            ->reject(function($item) { //loại bỏ các query rỗng
                $item = trim($item, " \n\r");
                return empty($item);
            });

        foreach ($sqls as $sql) {
            $this->conn->execute($sql);
            if ($abortOnError && $this->conn->ErrorNo()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kiểm tra bảng tồn tại không
     * @param string $tableName
     * @return boolean
     */
    function tableExists($tableName) {
        $this->autoConnect();

        $rs = $this->conn->execute("SHOW TABLES LIKE '$tableName'");
        if ($rs->RecordCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * Cơ chế lock đảm bảo xử lý tuần tự của SQL
     * @param string $resource
     * @param int timeout
     * @return bool
     */
    function customLock($resource, $timeout = 10) {
        return $this->GetOne("SELECT GET_LOCK(?, ?)", [$resource, int($timeout)]);
    }

    function customLockRelease($resource) {
        $this->Execute("SELECT RELEASE_LOCK(?)", [$resource]);
        return $this;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    function getRows($sql, $params = []) {
        return $this->GetAll($sql, $params);
    }

    /**
     * @return int
     */
    function getFoundRows() {
        return (int) $this->GetOne("SELECT FOUND_ROWS()");
    }

}