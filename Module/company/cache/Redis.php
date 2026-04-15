<?php


namespace Company\Cache;


use Company\Exception\NotFoundException;

class Redis implements CacheInterface
{
    protected $redis;
    protected static $config;

    public function __construct() {
        $this->redis = new \Redis();
        $this->redis->connect(
            self::$config["host"],
            self::$config["port"],
            self::$config["timeout"],
            self::$config["reserved"],
            self::$config["retry_interval"],
            self::$config["read_timeout"]
        );

        if ($auth = arrData(self::$config, "auth"))
            $this->redis->auth($auth);

    }

    public function __destruct() {
        $this->close();
    }

    static function setConfig($config) {
        self::$config = $config;
    }

    function setget($key, $value) {
        $this->redis->set($key, $value);
        return $value;
    }

    public function contains($key)
    {
        return boolval($this->redis->exists($key));
    }

    public function hashExists($name, $key)
    {
        return boolval($this->redis->hExists($name, $key));
    }

    public function delete(...$keys)
    {
        return boolval($this->del($keys));
    }

    public function __call($method, $arguments)
    {
        if(method_exists($this->redis, $method)) {
            return $this->redis->$method(...$arguments);
        }

        throw new NotFoundException("Method '$method' not found in Redis class");
    }

    public function set($key, $value, $lifeTime = null)
    {
        return $this->redis->set($key, $value, $lifeTime);
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function incr(string $key, int $value = 1)
    {
        return $this->redis->incrBy($key, $value);
    }

    public function decr(string $key, int $value = 1)
    {
        return $this->redis->decrBy($key, $value);
    }

    public function hSetEx($key, $ttl, $field)
    {
        return $this->redis->rawCommand('HEXPIRE', $key, $ttl, 'FIELDS', 1, $field);
    }

    public function fileLock($key, $ttl)
    {
        return $this->redis->rawCommand('SET', $key, 1, 'NX', 'EX', $ttl);
    }

    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    public function hScanCursor(string $key, &$cursor, ?string $pattern = null, int $count = 100)
    {
        return $this->redis->hScan($key, $cursor, $pattern, $count);
    }

    /**
     * Add job vào queue nếu chưa tồn tại trong HASH (dedup) + gắn TTL cho field
     */
    public function enqueueOnceHashTTL(string $hashKey, string $queueList, string $jobId, string $payload, int $ttl): bool {
        // HSETNX: chỉ set nếu field chưa tồn tại
        $added = $this->redis->hSetNx($hashKey, $jobId, time());

        if ($added == 1) {
            $this->hSetEx($hashKey, $ttl, $jobId);
            // Push vào queue
            $this->redis->rPush($queueList, $payload);
            return true;
        }

        return false;
    }

    /**
     * Worker lấy job bằng BLPOP
     */
    public function dequeueBlocking(string $queueList, int $timeout = 0)
    {
        return $this->redis->blPop($queueList, $timeout);
    }

    /**
     * Sau khi xử lý xong → xóa field khỏi HASH (dedup)
     */
    public function markDoneHash(string $hashKey, string $jobId): void
    {
        $this->redis->hDel($hashKey, $jobId);
    }



}