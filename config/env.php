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

// Supabase Bağlantı Bilgileri
// Vercel ortam değişkenlerinden veya yerel geliştirme için doğrudan tanımlanır
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://rfxleyiyvpygdpdbnmib.supabase.co/rest/v1');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJmeGxleWl5dnB5Z2RwZGJubWliIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTE0NTQ1ODYsImV4cCI6MjA2NzAzMDU4Nn0.ze5ip-K5ZwYpajdasSSGQayPUiFJILvkX9LJVrKsu08');

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
    
    error_log("Debug - Supabase Bağlantı: {$url_parts['host']}, API Anahtarı: {$masked_key}");
}
