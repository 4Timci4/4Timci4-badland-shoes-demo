<?php
/**
 * AutoCache Sistemi - Zero Manual Management
 * 
 * Tamamen otomatik cache yönetimi
 * - Otomatik cache
 * - Otomatik invalidation
 * - Otomatik cleanup
 * - Zero configuration
 */

class AutoCache {
    private static $instance = null;
    private $cache_data = [];
    private $cache_timestamps = [];
    private $default_ttl = 900; // 15 dakika
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Otomatik cache get/set - hiç düşünme, sadece kullan
     * 
     * @param string $key Cache anahtarı
     * @param callable $callback Data yükleme fonksiyonu
     * @param int $ttl Cache süresi (saniye)
     * @return mixed Cached veya fresh data
     */
    public function get($key, $callback, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        $cache_key = $this->generateKey($key);
        
        // Cache'de var mı ve süresi dolmamış mı kontrol et
        if ($this->isValid($cache_key)) {
            return $this->cache_data[$cache_key];
        }
        
        // Fresh data yükle
        $fresh_data = $callback();
        
        // Cache'e kaydet
        $this->set($cache_key, $fresh_data, $ttl);
        
        return $fresh_data;
    }
    
    /**
     * Manuel cache set (nadiren kullanılır)
     */
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        $cache_key = $this->generateKey($key);
        
        $this->cache_data[$cache_key] = $data;
        $this->cache_timestamps[$cache_key] = time() + $ttl;
        
        // Otomatik cleanup
        $this->autoCleanup();
    }
    
    /**
     * Pattern'e göre otomatik cache temizleme
     * 
     * @param string $pattern Silme pattern'i (admin_products_*, product_*)
     */
    public function autoInvalidate($pattern) {
        $pattern_regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
        
        foreach (array_keys($this->cache_data) as $key) {
            if (preg_match($pattern_regex, $key)) {
                unset($this->cache_data[$key]);
                unset($this->cache_timestamps[$key]);
            }
        }
    }
    
    /**
     * Cache'de veri var mı ve geçerli mi kontrol et
     */
    private function isValid($cache_key) {
        if (!isset($this->cache_data[$cache_key])) {
            return false;
        }
        
        if (!isset($this->cache_timestamps[$cache_key])) {
            return false;
        }
        
        // Süre dolmuş mu?
        if (time() > $this->cache_timestamps[$cache_key]) {
            unset($this->cache_data[$cache_key]);
            unset($this->cache_timestamps[$cache_key]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Cache key oluştur
     */
    private function generateKey($key) {
        return 'autocache_' . md5($key);
    }
    
    /**
     * Otomatik cleanup - expired cache'leri temizle
     */
    private function autoCleanup() {
        $current_time = time();
        
        foreach ($this->cache_timestamps as $key => $expire_time) {
            if ($current_time > $expire_time) {
                unset($this->cache_data[$key]);
                unset($this->cache_timestamps[$key]);
            }
        }
    }
    
    /**
     * Cache istatistikleri (debug için)
     */
    public function getStats() {
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
    
    /**
     * Tüm cache'i temizle
     */
    public function clear() {
        $this->cache_data = [];
        $this->cache_timestamps = [];
    }
    
    /**
     * Smart cache - büyük veri için optimize edilmiş
     * 
     * @param string $key
     * @param callable $callback
     * @param int $ttl
     * @param bool $serialize Büyük veri için serialization
     * @return mixed
     */
    public function getOptimized($key, $callback, $ttl = null, $serialize = false) {
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

/**
 * Global helper fonksiyon - her yerden kullan
 */
function autoCache() {
    return AutoCache::getInstance();
}

/**
 * Quick cache helper - tek satırda cache
 */
function quickCache($key, $callback, $ttl = 900) {
    return autoCache()->get($key, $callback, $ttl);
}

/**
 * Smart invalidation helper
 */
function invalidateCache($pattern) {
    autoCache()->autoInvalidate($pattern);
}
