<?php


namespace Company\Cache;


interface CacheInterface
{
    /**
     * @param $key
     * @param mixed $value
     * @param int $lifeTime
     * @return bool
     */
    public function set($key, $value, $lifeTime = 0);

    /**
     * @param $key
     * @return false|mixed
     */
    public function get($key);

    /**
     * @param $key
     * @return bool
     */
    public function contains($key);

    /**
     * @param $keys
     * @return bool
     */
    public function delete(...$keys);

    /**
     * @param string $key
     * @param int $value
     * @return int|bool
     */
    public function incr(string $key, int $value = 1);

    /**
     * @param string $key
     * @param int $value
     * @return int|bool
     */
    public function decr(string $key, int $value = 1);

}