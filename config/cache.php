<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class CacheConfig
{
    private static $db;
    private static $cacheTimeout = 3600; // 1 saat

    private static function init()
    {
        if (self::$db === null) {
            self::$db = database();
        }
    }

    public static function get($key, $dataCallback, $timeout = null)
    {
        self::init();
        self::clearExpired();

        $cacheKey = md5($key);

        try {
            $result = self::$db->select('cache', ['cache_key' => $cacheKey], '*', ['limit' => 1]);

            if (!empty($result)) {
                $cachedItem = $result[0];
                if ($cachedItem['expires_at'] >= time()) {
                    $data = unserialize($cachedItem['cache_value']);
                    if ($data !== false) {
                        return $data;
                    }
                }
            }
        } catch (Exception $e) {
            // Tablo yoksa veya başka bir DB hatası varsa, cache'i atla
            error_log("Cache get error: " . $e->getMessage());
        }

        $data = $dataCallback();
        self::set($key, $data, $timeout);
        return $data;
    }

    public static function set($key, $data, $timeout = null)
    {
        self::init();
        $cacheKey = md5($key);
        $expireTime = time() + ($timeout ?: self::$cacheTimeout);
        $serializedData = serialize($data);

        try {
            $existing = self::$db->select('cache', ['cache_key' => $cacheKey], 'cache_key', ['limit' => 1]);

            $payload = [
                'cache_value' => $serializedData,
                'expires_at' => $expireTime
            ];

            if (!empty($existing)) {
                self::$db->update('cache', $payload, ['cache_key' => $cacheKey]);
            } else {
                $payload['cache_key'] = $cacheKey;
                self::$db->insert('cache', $payload);
            }
        } catch (Exception $e) {
            error_log("Cache set error: " . $e->getMessage());
        }
    }

    public static function clear($key = null)
    {
        self::init();
        try {
            if ($key === null) {
                // Tüm cache'i temizle
                self::$db->delete('cache', []);
            } else {
                $cacheKey = md5($key);
                self::$db->delete('cache', ['cache_key' => $cacheKey]);
            }
        } catch (Exception $e) {
            error_log("Cache clear error: " . $e->getMessage());
        }
    }

    public static function clearExpired()
    {
        self::init();
        try {
            self::$db->delete('cache', ['expires_at' => ['<', time()]]);
        } catch (Exception $e) {
            error_log("Cache clearExpired error: " . $e->getMessage());
        }
    }

    public static function getStats()
    {
        self::init();
        $stats = [
            'total' => 0,
            'active' => 0,
            'expired' => 0,
            'items' => []
        ];

        try {
            $all_items = self::$db->select('cache');
            $currentTime = time();

            foreach ($all_items as $item) {
                $stats['total']++;
                $isExpired = $item['expires_at'] < $currentTime;

                if ($isExpired) {
                    $stats['expired']++;
                } else {
                    $stats['active']++;
                }

                $stats['items'][] = [
                    'key' => $item['cache_key'],
                    'expire' => date('Y-m-d H:i:s', $item['expires_at']),
                    'is_expired' => $isExpired,
                    'size' => strlen($item['cache_value'])
                ];
            }

        } catch (Exception $e) {
            error_log("Cache getStats error: " . $e->getMessage());
        }

        return $stats;
    }
}