<?php
/**
 * Kategori Servisi
 * 
 * Bu dosya, kategori verilerine erişim sağlayan servisi içerir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/SupabaseClient.php';

/**
 * Kategori servisi
 * 
 * Kategorilerle ilgili tüm veritabanı işlemlerini içerir
 */
class CategoryService {
    private $supabase;
    
    /**
     * CategoryService sınıfını başlatır
     */
    public function __construct() {
        $this->supabase = supabase();
    }
    
    /**
     * Tüm kategorileri getiren metod
     * 
     * @return array Kategoriler
     */
    public function getCategories() {
        try {
            return $this->supabase->request('/rest/v1/categories');
        } catch (Exception $e) {
            error_log("Kategorileri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Tüm kategorileri getiren metod (Admin panel için)
     * 
     * @return array Kategoriler
     */
    public function getAllCategories() {
        try {
            $response = $this->supabase->request('categories?select=*&order=name.asc');
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Tüm kategorileri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Her kategori için ürün sayılarını getiren metod
     *
     * @return array Kategori slug'ı anahtar, ürün sayısı değer olan bir dizi
     */
    public function getCategoryProductCounts() {
        try {
            return $this->supabase->request('/rest/v1/rpc/get_category_product_counts', 'POST');
        } catch (Exception $e) {
            error_log("Kategori ürün sayıları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Belirli bir kategoriyi slug'a göre getiren metod
     * 
     * @param string $slug Kategori slug'ı
     * @return array|null Kategori veya bulunamazsa boş dizi
     */
    public function getCategoryBySlug($slug) {
        try {
            $query = [
                'select' => '*',
                'slug' => 'eq.' . $slug,
                'limit' => 1
            ];
            
            $result = $this->supabase->request('/rest/v1/categories?' . http_build_query($query));
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Kategori getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// CategoryService sınıfı singleton örneği
function category_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new CategoryService();
    }
    
    return $instance;
}

// Geriye uyumluluk için fonksiyonlar
/**
 * Tüm kategorileri getiren fonksiyon
 * 
 * @return array Kategoriler
 */
function get_categories() {
    return category_service()->getCategories();
}

/**
 * Her kategori için ürün sayılarını getiren fonksiyon
 *
 * @return array Kategori slug'ı anahtar, ürün sayısı değer olan bir dizi
 */
function get_category_product_counts() {
    return category_service()->getCategoryProductCounts();
}
