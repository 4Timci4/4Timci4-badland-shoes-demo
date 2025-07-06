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
    
    /**
     * Yeni kategori oluşturma metodu
     * 
     * @param array $data Kategori verileri
     * @return bool Başarı durumu
     */
    public function createCategory($data) {
        try {
            // Debug: Gönderilen veriyi logla
            error_log("CategoryService::createCategory - Gönderilen data: " . json_encode($data));
            
            $response = $this->supabase->request('categories', 'POST', $data);
            
            // Debug: Dönen yanıtı logla
            error_log("CategoryService::createCategory - Supabase response: " . json_encode($response));
            
            // Response'da body varsa ve boş değilse başarılı
            if (isset($response['body']) && !empty($response['body'])) {
                return true;
            }
            
            error_log("CategoryService::createCategory - Empty response body received");
            return false;
            
        } catch (Exception $e) {
            error_log("CategoryService::createCategory - Exception: " . $e->getMessage());
            error_log("CategoryService::createCategory - Exception Code: " . $e->getCode());
            return false;
        }
    }
    
    /**
     * Kategori güncelleme metodu
     * 
     * @param int $category_id Kategori ID
     * @param array $data Güncellenecek veriler
     * @return bool Başarı durumu
     */
    public function updateCategory($category_id, $data) {
        try {
            $response = $this->supabase->request('categories?id=eq.' . intval($category_id), 'PATCH', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Kategori güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kategori silme metodu
     * 
     * @param int $category_id Kategori ID
     * @return bool Başarı durumu
     */
    public function deleteCategory($category_id) {
        try {
            // Önce bu kategoriye ait ürün var mı kontrol et
            $products_response = $this->supabase->request('product_models?select=id&category_id=eq.' . intval($category_id) . '&limit=1');
            $products = $products_response['body'] ?? [];
            
            if (!empty($products)) {
                return false; // Kategoriye ait ürün varsa silinemez
            }
            
            $response = $this->supabase->request('categories?id=eq.' . intval($category_id), 'DELETE');
            return !empty($response);
        } catch (Exception $e) {
            error_log("Kategori silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kategori ID'ye göre kategori getirme metodu
     * 
     * @param int $category_id Kategori ID
     * @return array|null Kategori veya bulunamazsa boş dizi
     */
    public function getCategoryById($category_id) {
        try {
            $response = $this->supabase->request('categories?id=eq.' . intval($category_id) . '&limit=1');
            $result = $response['body'] ?? [];
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Kategori getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Slug oluşturma metodu
     * 
     * @param string $text Dönüştürülecek metin
     * @return string Slug
     */
    public function generateSlug($text) {
        // Türkçe karakterleri dönüştür
        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'I', 'O', 'S', 'U'];
        $text = str_replace($turkish, $english, $text);
        
        // Küçük harfe dönüştür ve sadece alfanumerik karakterleri bırak
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Admin için kategorileri ürün sayılarıyla getir
     * 
     * @return array Kategoriler ve ürün sayıları
     */
    public function getCategoriesWithProductCounts() {
        try {
            $categories = $this->getAllCategories();
            
            foreach ($categories as &$category) {
                $products_response = $this->supabase->request('product_models?select=id&category_id=eq.' . $category['id']);
                $products = $products_response['body'] ?? [];
                $category['product_count'] = count($products);
            }
            
            return $categories;
        } catch (Exception $e) {
            error_log("Kategoriler ve ürün sayıları getirme hatası: " . $e->getMessage());
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
