<?php


class AutoCache
{
    private static $instance = null;
    private $cache_data = [];
    private $cache_timestamps = [];
    private $default_ttl = 900;


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function get($key, $callback, $ttl = null)
    {
        $ttl = $ttl ?? $this->default_ttl;
        $cache_key = $this->generateKey($key);


        if ($this->isValid($cache_key)) {
            return $this->cache_data[$cache_key];
        }


        $fresh_data = $callback();


        $this->set($cache_key, $fresh_data, $ttl);

        return $fresh_data;
    }


    public function set($key, $data, $ttl = null)
    {
        $ttl = $ttl ?? $this->default_ttl;
        $cache_key = $this->generateKey($key);

        $this->cache_data[$cache_key] = $data;
        $this->cache_timestamps[$cache_key] = time() + $ttl;


        $this->autoCleanup();
    }


    public function autoInvalidate($pattern)
    {
        $pattern_regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';

        foreach (array_keys($this->cache_data) as $key) {
            if (preg_match($pattern_regex, $key)) {
                unset($this->cache_data[$key]);
                unset($this->cache_timestamps[$key]);
            }
        }
    }


    private function isValid($cache_key)
    {
        if (!isset($this->cache_data[$cache_key])) {
            return false;
        }

        if (!isset($this->cache_timestamps[$cache_key])) {
            return false;
        }


        if (time() > $this->cache_timestamps[$cache_key]) {
            unset($this->cache_data[$cache_key]);
            unset($this->cache_timestamps[$cache_key]);
            return false;
        }

        return true;
    }


    private function generateKey($key)
    {
        return 'autocache_' . md5($key);
    }


    private function autoCleanup()
    {
        $current_time = time();

        foreach ($this->cache_timestamps as $key => $expire_time) {
            if ($current_time > $expire_time) {
                unset($this->cache_data[$key]);
                unset($this->cache_timestamps[$key]);
            }
        }
    }


    public function getStats()
    {
        $total_items = count($this->cache_data);
        $expired_items = 0;
        $current_time = time();

        foreach ($this->cache_timestamps as $expire_time) {
            if ($current_time > $expire_time) {
                $expired_items++;
            }
        }

        return [
            'total_items' => $total_items,
            'active_items' => $total_items - $expired_items,
            'expired_items' => $expired_items,
            'memory_usage' => memory_get_usage(true)
        ];
    }


    public function clear()
    {
        $this->cache_data = [];
        $this->cache_timestamps = [];
    }


    public function getOptimized($key, $callback, $ttl = null, $serialize = false)
    {
        $ttl = $ttl ?? $this->default_ttl;
        $cache_key = $this->generateKey($key);

        if ($this->isValid($cache_key)) {
            $data = $this->cache_data[$cache_key];
            return $serialize ? unserialize($data) : $data;
        }

        $fresh_data = $callback();
        $cache_data = $serialize ? serialize($fresh_data) : $fresh_data;

        $this->set($cache_key, $cache_data, $ttl);

        return $fresh_data;
    }
}


function autoCache()
{
    return AutoCache::getInstance();
}


function quickCache($key, $callback, $ttl = 900)
{
    return autoCache()->get($key, $callback, $ttl);
}


function invalidateCache($pattern)
{
    autoCache()->autoInvalidate($pattern);
}
