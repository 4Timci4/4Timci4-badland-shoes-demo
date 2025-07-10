<?php
/**
 * Simple and Stable Session Configuration
 * Basit ve güvenilir session yönetimi
 */

// Session ayarlarını yapılandır (sadece bir kez)
if (session_status() === PHP_SESSION_NONE) {
    // Temel session ayarları
    ini_set('session.save_path', 'C:/xampp/tmp');
    ini_set('session.cookie_lifetime', 0); // Browser session
    ini_set('session.gc_maxlifetime', 7200); // 2 saat (çok daha uzun)
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 1000);
    
    // Güvenlik ayarları
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // HTTP için false
    ini_set('session.use_strict_mode', 1);
    
    // Session name
    session_name('PHPSESSID_BANDLAND');
}

/**
 * Basit ve güvenilir session start fonksiyonu
 */
function start_session_safely($regenerate = false) {
    // Session henüz başlamamışsa başlat
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        
        // İlk kez session başlatılıyorsa timestamp ekle
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
            $_SESSION['last_activity'] = time();
        }
    }
    
    // Son aktivite zamanını güncelle (agresif timeout yok)
    $_SESSION['last_activity'] = time();
    
    // Sadece çok eski session'ları temizle (4 saat)
    if (isset($_SESSION['created']) && (time() - $_SESSION['created']) > 14400) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    
    return true;
}

/**
 * Session validation - sadece temel kontroller
 */
function validate_session() {
    return session_status() === PHP_SESSION_ACTIVE;
}

/**
 * Güvenli session destroy
 */
function destroy_session_completely() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        
        // Session cookie'sini temizle
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
}

/**
 * Session bilgilerini al
 */
function get_session_info() {
    return [
        'status' => session_status(),
        'id' => session_id(),
        'name' => session_name(),
        'created' => $_SESSION['created'] ?? null,
        'last_activity' => $_SESSION['last_activity'] ?? null
    ];
}
?>