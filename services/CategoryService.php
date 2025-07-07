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
     * @param string|null $category_type Kategori tipi filtresi (geriye uyumluluk için)
     * @param int|null $parent_id Üst kategori filtresi (NULL ise ana kategoriler)
     * @return array Kategoriler
     */
    public function getAllCategories($category_type = null, $parent_id = null) {
        try {
            $query = 'categories?select=*';
            
            // Kategori tipi (eski) veya üst kategori (yeni) filtresi
            if ($category_type) {
                $query .= '&category_type=eq.' . $category_type;
            }
            
            // parent_id parametresi belirtilmişse, buna göre filtrele
            if ($parent_id !== null) {
                if ($parent_id === 0) {
                    // Ana kategoriler (parent_id IS NULL)
                    $query .= '&parent_id=is.null';
                } else {
                    // Belirli bir üst kategorinin alt kategorileri
                    $query .= '&parent_id=eq.' . intval($parent_id);
                }
            }
            
            $query .= '&order=name.asc';
            
            $response = $this->supabase->request($query);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Tüm kategorileri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ana kategorileri getiren metod (parent_id = NULL)
     * 
     * @return array Ana kategoriler
     */
    public function getMainCategories() {
        return $this->getAllCategories(null, 0); // 0 = ana kategoriler (parent_id IS NULL)
    }
    
    /**
     * Belirli bir ana kategoriye ait alt kategorileri getir
     * 
     * @param int $parent_id Ana kategori ID
     * @return array Alt kategoriler
     */
    public function getSubcategories($parent_id) {
        if (!$parent_id) return [];
        return $this->getAllCategories(null, $parent_id);
    }
    
    /**
     * Tüm kategorileri hiyerarşik yapıda getir
     * 
     * @return array Ana kategoriler ve alt kategorileri içeren hiyerarşik yapı
     */
    public function getCategoriesHierarchy() {
        try {
            // Ana kategorileri al
            $main_categories = $this->getMainCategories();
            
            // Her ana kategori için alt kategorileri ekle
            foreach ($main_categories as &$main_category) {
                $main_category['subcategories'] = $this->getSubcategories($main_category['id']);
            }
            
            return $main_categories;
        } catch (Exception $e) {
            error_log("Kategorileri hiyerarşik yapıda getirme hatası: " . $e->getMessage());
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
            
            $response = $this->supabase->request('categories', 'POST', $data);
            
            
            // Response'da body varsa ve boş değilse başarılı
            if (isset($response['body']) && !empty($response['body'])) {
                return true;
            }
            
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
            // Önce bu kategoriye ait ürün var mı kontrol et (yeni çoklu kategori sistemi)
            $products_response = $this->supabase->request('product_categories?select=product_id&category_id=eq.' . intval($category_id) . '&limit=1');
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
     * Kategorileri tipine göre gruplu olarak getir (Eski kategori yapısı ile uyumlu)
     * Stil kategorileri hariç tutulur.
     * 
     * @return array Kategori tipi anahtarlı, kategoriler değerli dizi
     */
    public function getCategoriesGroupedByType() {
        try {
            $categories = $this->getAllCategories();
            $grouped = [];
            
            foreach ($categories as $category) {
                $type = $category['category_type'] ?? 'product_type';
                // Stil kategorilerini hariç tut
                if ($type !== 'style') {
                    if (!isset($grouped[$type])) {
                        $grouped[$type] = [];
                    }
                    $grouped[$type][] = $category;
                }
            }
            
            return $grouped;
        } catch (Exception $e) {
            error_log("Kategorileri gruplama hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Admin için kategorileri ürün sayılarıyla getir
     * 
     * @param bool $hierarchical Hiyerarşik yapıda döndürülsün mü?
     * @return array Kategoriler ve ürün sayıları
     */
    public function getCategoriesWithProductCounts($hierarchical = false) {
        try {
            $categories = $this->getAllCategories();
            
            foreach ($categories as &$category) {
                // Ürün sayılarını ekle
                $products_response = $this->supabase->request('product_categories?select=product_id&category_id=eq.' . $category['id']);
                $products = $products_response['body'] ?? [];
                $category['product_count'] = count($products);
            }
            
            // Hiyerarşik yapı isteniyorsa düzenle
            if ($hierarchical) {
                $hierarchy = [];
                $categories_indexed = [];
                
                // Kategorileri ID'ye göre indeksle
                foreach ($categories as $category) {
                    $categories_indexed[$category['id']] = $category;
                    $categories_indexed[$category['id']]['subcategories'] = [];
                }
                
                // Hiyerarşik yapıyı oluştur
                foreach ($categories as $category) {
                    if ($category['parent_id'] === null) {
                        // Ana kategori
                        $hierarchy[] = &$categories_indexed[$category['id']];
                    } else if (isset($categories_indexed[$category['parent_id']])) {
                        // Alt kategori
                        $categories_indexed[$category['parent_id']]['subcategories'][] = $category;
                    }
                }
                
                return $hierarchy;
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
