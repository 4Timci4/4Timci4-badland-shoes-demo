<?php
/**
 * Veritabanı Bağlantı Konfigürasyonu
 * 
 * Bu dosya Supabase bağlantı bilgilerini içerir ve diğer modülleri entegre eder.
 */

// Ortam değişkenlerini içe aktar
require_once __DIR__ . '/env.php';

// Supabase İstemci sınıfını içe aktar
require_once __DIR__ . '/../lib/SupabaseClient.php';

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
        $response = supabase()->request('colors?select=*');
        return $response['body'] ?? [];
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
        $response = supabase()->request('sizes?select=*');
        return $response['body'] ?? [];
    } catch (Exception $e) {
        error_log("Bedenleri getirme hatası: " . $e->getMessage());
        return [];
    }
}
