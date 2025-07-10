<?php
// Session konfigürasyonu - session_start()'dan önce çağrılmalı

// Session başlatma yardımcı fonksiyonu
function start_session_safely() {
    // Session henüz başlatılmamışsa ayarları yapılandır
    if (session_status() === PHP_SESSION_NONE) {
        // Session ayarlarını yapılandır (sistem default tmp dizinini kullan)
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', 1440);
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0); // HTTP için false, HTTPS için true olmalı
        ini_set('session.use_strict_mode', 1);
        
        // Session'ı başlat (sistem default tmp dizininde)
        session_start();
    }
}
?>