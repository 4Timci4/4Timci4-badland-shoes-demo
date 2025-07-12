<?php


class CacheConfig
{


    private static $cacheTimeout = 300;


    public static function get($key, $dataCallback, $timeout = null)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $dataCallback();
        }


        $cacheKey = 'cache_' . md5($key);


        if (self::hasValidCache($cacheKey, $timeout)) {
            return $_SESSION[$cacheKey]['data'];
        }


        $data = $dataCallback();


        self::set($key, $data, $timeout);

        return $data;
    }


    public static function set($key, $data, $timeout = null)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $cacheKey = 'cache_' . md5($key);
        $expireTime = time() + ($timeout ?: self::$cacheTimeout);

        $_SESSION[$cacheKey] = [
            'data' => $data,
            'expire' => $expireTime
        ];
    }


    public static function clear($key = null)
    {

        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION) || !is_array($_SESSION)) {
            return;
        }

        if ($key === null) {

            foreach ($_SESSION as $sessionKey => $value) {
                if (strpos($sessionKey, 'cache_') === 0) {
                    unset($_SESSION[$sessionKey]);
                }
            }
        } else {

            $cacheKey = 'cache_' . md5($key);
            if (isset($_SESSION[$cacheKey])) {
                unset($_SESSION[$cacheKey]);
            }
        }
    }


    private static function hasValidCache($cacheKey, $timeout = null)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        if (!isset($_SESSION[$cacheKey]) || !isset($_SESSION[$cacheKey]['expire'])) {
            return false;
        }

        $expireTime = $_SESSION[$cacheKey]['expire'];
        $currentTime = time();


        if ($currentTime > $expireTime) {
            unset($_SESSION[$cacheKey]);
            return false;
        }

        return true;
    }


    public static function clearExpiredCache()
    {

        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION) || !is_array($_SESSION)) {
            return;
        }

        $currentTime = time();

        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'cache_') === 0 && isset($value['expire']) && $currentTime > $value['expire']) {
                unset($_SESSION[$key]);
            }
        }
    }


    public static function getStats()
    {
        $stats = [
            'total' => 0,
            'active' => 0,
            'expired' => 0,
            'items' => []
        ];


        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION) || !is_array($_SESSION)) {
            return $stats;
        }

        $currentTime = time();

        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'cache_') === 0) {
                $stats['total']++;

                $isExpired = isset($value['expire']) && $currentTime > $value['expire'];

                if ($isExpired) {
                    $stats['expired']++;
                } else {
                    $stats['active']++;
                }

                $stats['items'][] = [
                    'key' => $key,
                    'expire' => isset($value['expire']) ? date('Y-m-d H:i:s', $value['expire']) : null,
                    'is_expired' => $isExpired,
                    'size' => strlen(serialize($value['data'] ?? null))
                ];
            }
        }

        return $stats;
    }
}


CacheConfig::clearExpiredCache();