<?php
/**
 * Cache Configuration
 * 
 * Sık kullanılan veritabanı sorgularını önbelleğe alma
 */

class CacheConfig {
    
    // Önbellek süresi (saniye)
    private static $cacheTimeout = 300; // 5 dakika
    
    /**
     * Veriyi önbellekten getir, yoksa veritabanından çek ve önbelleğe al
     * 
     * @param string $key Önbellek anahtarı
     * @param callable $dataCallback Veri yoksa çalıştırılacak fonksiyon
     * @param int $timeout Özel önbellek süresi (saniye)
     * @return mixed Önbellekteki veri
     */
    public static function get($key, $dataCallback, $timeout = null) {
        // Önbellek anahtarını oluştur
        $cacheKey = 'cache_' . md5($key);
        
        // Önbellekte veri var mı kontrol et
        if (self::hasValidCache($cacheKey, $timeout)) {
            return $_SESSION[$cacheKey]['data'];
        }
        
        // Veriyi callback fonksiyonundan al
        $data = $dataCallback();
        
        // Veriyi önbelleğe al
        self::set($key, $data, $timeout);
        
        return $data;
    }
    
    /**
     * Veriyi önbelleğe al
     * 
     * @param string $key Önbellek anahtarı
     * @param mixed $data Önbelleğe alınacak veri
     * @param int $timeout Özel önbellek süresi (saniye)
     */
    public static function set($key, $data, $timeout = null) {
        $cacheKey = 'cache_' . md5($key);
        $expireTime = time() + ($timeout ?: self::$cacheTimeout);
        
        $_SESSION[$cacheKey] = [
            'data' => $data,
            'expire' => $expireTime
        ];
    }
    
    /**
     * Önbellekteki veriyi temizle
     * 
     * @param string $key Önbellek anahtarı (null ise tüm önbelleği temizle)
     */
    public static function clear($key = null) {
        if ($key === null) {
            // Tüm önbelleği temizle
            foreach ($_SESSION as $sessionKey => $value) {
                if (strpos($sessionKey, 'cache_') === 0) {
                    unset($_SESSION[$sessionKey]);
                }
            }
        } else {
            // Belirli bir anahtarı temizle
            $cacheKey = 'cache_' . md5($key);
            if (isset($_SESSION[$cacheKey])) {
                unset($_SESSION[$cacheKey]);
            }
        }
    }
    
    /**
     * Önbellekte geçerli veri var mı kontrol et
     * 
     * @param string $cacheKey Önbellek anahtarı
     * @param int $timeout Özel önbellek süresi (saniye)
     * @return bool Geçerli veri varsa true
     */
    private static function hasValidCache($cacheKey, $timeout = null) {
        if (!isset($_SESSION[$cacheKey]) || !isset($_SESSION[$cacheKey]['expire'])) {
            return false;
        }
        
        $expireTime = $_SESSION[$cacheKey]['expire'];
        $currentTime = time();
        
        // Süre dolmuşsa false döndür
        if ($currentTime > $expireTime) {
            unset($_SESSION[$cacheKey]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Tüm süresi dolmuş önbellekleri temizle
     */
    public static function clearExpiredCache() {
        // Session başlatılmamışsa veya $_SESSION tanımlı değilse işlem yapma
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
    
    /**
     * Önbellek istatistiklerini getir
     * 
     * @return array Önbellek istatistikleri
     */
    public static function getStats() {
        $stats = [
            'total' => 0,
            'active' => 0,
            'expired' => 0,
            'items' => []
        ];
        
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

// Otomatik olarak süresi dolmuş önbellekleri temizle
CacheConfig::clearExpiredCache();