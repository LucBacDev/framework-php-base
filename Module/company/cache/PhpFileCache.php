<?php


namespace Company\Cache;

use Doctrine\Common\Cache\PhpFileCache as DoctrineFileCache;

class PhpFileCache implements CacheInterface
{
    protected $cache;
    static $path;

    static function setCachePath($path) {
        self::$path = $path;
    }

    public function __construct()
    {
        $this->cache = new DoctrineFileCache(self::$path);
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

    public function incr(string $key, int $value = 1)
    {
        // not support
    }

    public function decr(string $key, int $value = 1)
    {
        // not support
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