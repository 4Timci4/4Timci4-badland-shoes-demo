<?php
/**
 * Ortam Değişkenleri ve Konfigürasyon
 * 
 * Bu dosya ortam değişkenlerini ve temel sabitleri içerir.
 */

// .env dosyasını yükle (eğer varsa)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(sprintf('%s=%s', trim($key), trim($value)));
        }
    }
}

// Database Türü Seçimi
// 'supabase' veya 'mariadb' değerlerini alabilir
define('DB_TYPE', getenv('DB_TYPE') ?: 'supabase');

// Supabase Bağlantı Bilgileri
// Vercel ortam değişkenlerinden veya yerel geliştirme için doğrudan tanımlanır
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://rfxleyiyvpygdpdbnmib.supabase.co');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJmeGxleWl5dnB5Z2RwZGJubWliIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1MTQ1NDU4NiwiZXhwIjoyMDY3MDMwNTg2fQ.MX3WymdKFlrk7LnYX4qRFgRhfSEyK0aIGLzjua6j2iU');

// MariaDB/MySQL Bağlantı Bilgileri
// Yerel geliştirme için varsayılan değerler
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_NAME', getenv('DB_NAME') ?: 'bandland_shoes');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// Uygulamanın çalıştığı ortam (development, production, testing)
define('APP_ENV', getenv('APP_ENV') ?: 'development');

/**
 * Veritabanı bağlantı bilgilerini şifreli olarak konsola yazar
 * Hata ayıklama amaçlıdır, üretim ortamında kullanılmamalıdır
 */
function debug_connection_info() {
    if (APP_ENV !== 'development') {
        return;
    }
    
    $url_parts = parse_url(SUPABASE_URL);
    $masked_key = substr(SUPABASE_KEY, 0, 5) . '...' . substr(SUPABASE_KEY, -5);
    
}
