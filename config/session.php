<?php
/**
 * Database-Based Session Configuration
 * Veritabanı tabanlı güvenilir session yönetimi
 */

require_once __DIR__ . '/../lib/DatabaseSessionHandler.php';

// Session ayarlarını yapılandır (sadece bir kez)
if (session_status() === PHP_SESSION_NONE) {
    // Temel session ayarları
    ini_set('session.cookie_lifetime', 0); // Browser session
    ini_set('session.gc_maxlifetime', 21600); // 6 saat
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100); // Daha sık temizlik
    
    // Güvenlik ayarları
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // HTTP için false
    ini_set('session.use_strict_mode', 1);
    
    // Session name
    session_name('PHPSESSID_BANDLAND');
    
    // Custom session handler'ı kur
    $session_handler = new DatabaseSessionHandler();
    session_set_save_handler($session_handler, true);
}

/**
 * Basit ve güvenilir session start fonksiyonu
 */
function start_session_safely($regenerate = false) {
    try {
        // Session henüz başlamamışsa başlat
        if (session_status() === PHP_SESSION_NONE) {
            if (!session_start()) {
                error_log("Session başlatma hatası: " . error_get_last()['message'] ?? 'Bilinmeyen hata');
                return false;
            }
            error_log("Session başlatıldı: " . session_id());
        }
        
        // İlk kez session başlatılıyorsa timestamp ekle
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
            $_SESSION['last_activity'] = time();
            error_log("Yeni session oluşturuldu: " . session_id());
        } else {
            // Son aktivite zamanını güncelle
            $_SESSION['last_activity'] = time();
        }
        
        // Sadece açık talep olduğunda veya çok eski session'ları regenerate et
        if ($regenerate || (isset($_SESSION['created']) && (time() - $_SESSION['created']) > 21600)) {
            $old_id = session_id();
            session_regenerate_id(true);
            $_SESSION['created'] = time();
            error_log("Session yenilendi: " . $old_id . " -> " . session_id());
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Session güvenli başlatma hatası: " . $e->getMessage());
        return false;
    }
}

/**
 * Session validation - güçlendirilmiş kontroller
 */
function validate_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log("Session validation hatası: Session aktif değil");
        return false;
    }
    
    // Session timeout kontrolü (6 saat)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 21600) {
        error_log("Session timeout: Son aktivite " . (time() - $_SESSION['last_activity']) . " saniye önce");
        return false;
    }
    
    return true;
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