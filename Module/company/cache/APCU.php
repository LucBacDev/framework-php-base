<?php


namespace Company\Cache;

use Doctrine\Common\Cache\ApcuCache;

class APCU implements CacheInterface
{
    protected $cache;
    public function __construct()
    {
        $this->cache = new ApcuCache();
    }

    public function set($key, $value, $lifeTime = 0)
    {
        return $this->cache->save($key, $value, $lifeTime);
    }

    public function get($key)
    {
        return $this->cache->fetch($key);
    }

    public function contains($key)
    {
        return $this->cache->contains($key);
    }

    public function delete(...$keys)
    {
        return $this->cache->deleteMultiple(func_get_args());
    }

    public function deleteAll() {
        return $this->cache->deleteAll();
    }

    public function incr(string $key, int $value = 1)
    {
        return apcu_inc($key, $value);
    }

    public function decr(string $key, int $value = 1)
    {
        return apcu_dec($key, $value);
    }

    public function hGetAll($key) {
        return $this->get($key);
    }

    public function hSet($key, $hashKey, $value) {
        $data = $this->get($key);
        if (!$data)
            $data = [];
        $data[$hashKey] = $value;
        $this->set($key, $data);
        return true;
    }

    function hDel($key, ...$hashKeys) {
        $data = $this->get($key);
        if (!$data)
            $data = [];
        foreach ($hashKeys as $hashKey)
            unset($data[$hashKey]);
        $this->set($key, $data);
        return true;
    }
}