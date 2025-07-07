<?php
/**
 * Veritabanı Bağlantı Konfigürasyonu
 * 
 * Bu dosya Supabase bağlantı bilgilerini içerir ve diğer modülleri entegre eder.
 */

// Ortam değişkenlerini içe aktar
require_once __DIR__ . '/env.php';

// Database Factory'yi içe aktar
require_once __DIR__ . '/../lib/DatabaseFactory.php';

// Ürün servisini içe aktar
require_once __DIR__ . '/../services/ProductService.php';

// Kategori servisini içe aktar
require_once __DIR__ . '/../services/CategoryService.php';

// Blog servisini içe aktar
require_once __DIR__ . '/../services/BlogService.php';

// İletişim servisini içe aktar
require_once __DIR__ . '/../services/ContactService.php';

/**
 * Tüm renkleri getiren fonksiyon
 * 
 * @return array Renkler
 */
function get_colors() {
    try {
        // Yeni database abstraction layer kullan
        return database()->select('colors', [], ['id', 'name', 'hex_code', 'created_at']);
    } catch (Exception $e) {
        error_log("Renkleri getirme hatası: " . $e->getMessage());
        return [];
    }
}

/**
 * Tüm bedenleri getiren fonksiyon
 * 
 * @return array Bedenler
 */
function get_sizes() {
    try {
        // Yeni database abstraction layer kullan
        return database()->select('sizes', [], ['id', 'size_value', 'size_type', 'created_at']);
    } catch (Exception $e) {
        error_log("Bedenleri getirme hatası: " . $e->getMessage());
        return [];
    }
}
