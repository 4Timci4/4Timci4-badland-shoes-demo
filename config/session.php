<?php
/**
 * Session Configuration and Security Settings
 * 
 * Session yönetimi ve güvenlik ayarları
 */

class SessionConfig {
    
    /**
     * Session güvenlik ayarlarını başlat
     */
    public static function init() {
        // Session zaten başlatılmışsa işlem yapma
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }
        
        // Session ayarlarını güvenlik için optimize et
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_lifetime', 0); // Browser kapanana kadar
        
        // Session timeout ayarları
        ini_set('session.gc_maxlifetime', 7200); // 2 saat
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        
        // Session name güvenlik için değiştir
        session_name('BANDLAND_SESSID');
        
        // Session başlat
        session_start();
        
        // Session güvenlik kontrolleri
        self::validateSession();
    }
    
    /**
     * Session güvenlik validasyonu
     */
    private static function validateSession() {
        // IP adresi değişiklik kontrolü
        if (!isset($_SESSION['user_ip'])) {
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        } elseif ($_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
            // IP adresi değişmiş, session'ı yok et
            self::destroySession();
            return;
        }
        
        // User-Agent değişiklik kontrolü
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            // User-Agent değişmiş, session'ı yok et
            self::destroySession();
            return;
        }
        
        // Session regeneration (her 30 dakikada bir)
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 dakika
            self::regenerateSession();
        }
    }
    
    /**
     * Session ID'yi güvenli şekilde yenile
     */
    public static function regenerateSession() {
        // Eski session verilerini sakla
        $oldSessionData = $_SESSION;
        
        // Session ID'yi yenile
        if (session_regenerate_id(true)) {
            // Session verilerini geri yükle
            $_SESSION = $oldSessionData;
            $_SESSION['last_regeneration'] = time();
            
            // Log kayıt
            error_log("Session regenerated for user: " . ($_SESSION['user_id'] ?? 'anonymous'));
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Session'ı güvenli şekilde yok et
     */
    public static function destroySession() {
        // Session verilerini temizle
        $_SESSION = [];
        
        // Session cookie'sini sil
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Session'ı yok et
        session_destroy();
        
        // Log kayıt
        error_log("Session destroyed due to security validation failure");
    }
    
    /**
     * Session timeout kontrolü
     */
    public static function checkTimeout($timeout = 1800) {
        if (isset($_SESSION['user_last_activity'])) {
            $inactiveTime = time() - $_SESSION['user_last_activity'];
            
            if ($inactiveTime > $timeout) {
                self::destroySession();
                return false;
            }
        }
        
        $_SESSION['user_last_activity'] = time();
        return true;
    }
    
    /**
     * Concurrent session kontrolü - Performans için optimize edildi
     */
    public static function checkConcurrentSession($userId, $db) {
        if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
            return true;
        }
        
        // Son kontrol zamanını kontrol et - her 5 dakikada bir kontrol yap
        if (isset($_SESSION['last_concurrent_check']) && (time() - $_SESSION['last_concurrent_check'] < 300)) {
            return true; // 5 dakika geçmediyse tekrar kontrol etme
        }
        
        // Aktif session'ı veritabanından kontrol et
        $currentSessionId = session_id();
        $userSessions = $db->select('user_sessions', ['user_id' => $userId], '*');
        
        // Son kontrol zamanını güncelle
        $_SESSION['last_concurrent_check'] = time();
        
        foreach ($userSessions as $session) {
            if ($session['session_id'] !== $currentSessionId) {
                // Başka bir session aktif, mevcut session'ı sonlandır
                self::destroySession();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * User session'ını veritabanına kaydet
     */
    public static function saveUserSession($userId, $db) {
        $sessionId = session_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $loginTime = date('Y-m-d H:i:s');
        
        // Eski session'ları sil
        $db->delete('user_sessions', ['user_id' => $userId]);
        
        // Yeni session'ı kaydet
        $db->insert('user_sessions', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'login_time' => $loginTime,
            'last_activity' => $loginTime
        ]);
    }
    
    /**
     * Session activity'sini güncelle - Performans için optimize edildi
     */
    public static function updateSessionActivity($userId, $db) {
        // Son güncelleme zamanını kontrol et - her 5 dakikada bir güncelle
        if (isset($_SESSION['last_activity_update']) && (time() - $_SESSION['last_activity_update'] < 300)) {
            return; // 5 dakika geçmediyse güncelleme yapma
        }
        
        $sessionId = session_id();
        $db->update('user_sessions',
            ['last_activity' => date('Y-m-d H:i:s')],
            ['user_id' => $userId, 'session_id' => $sessionId]
        );
        
        // Son güncelleme zamanını kaydet
        $_SESSION['last_activity_update'] = time();
    }
    
    /**
     * Session'ı temizle
     */
    public static function clearUserSession($userId, $db) {
        $db->delete('user_sessions', ['user_id' => $userId]);
    }
    
    /**
     * Session debug bilgilerini getir
     */
    public static function getDebugInfo() {
        return [
            'session_id' => session_id(),
            'session_name' => session_name(),
            'session_status' => session_status(),
            'session_timeout' => ini_get('session.gc_maxlifetime'),
            'cookie_secure' => ini_get('session.cookie_secure'),
            'cookie_httponly' => ini_get('session.cookie_httponly'),
            'use_strict_mode' => ini_get('session.use_strict_mode'),
            'last_activity' => $_SESSION['user_last_activity'] ?? null,
            'last_regeneration' => $_SESSION['last_regeneration'] ?? null,
            'user_ip' => $_SESSION['user_ip'] ?? null,
            'user_agent_hash' => isset($_SESSION['user_agent']) ? md5($_SESSION['user_agent']) : null
        ];
    }
}

// Session'ı otomatik başlat
SessionConfig::init();