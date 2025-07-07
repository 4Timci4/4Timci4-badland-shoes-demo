<?php
/**
 * Ürün Filtreleme Servisi
 * 
 * Ürün filtreleme ve kategori/cinsiyet sorgulamalarını içerir
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';

class ProductFilterService {
    private $db;
    
    public function __construct() {
        $this->db = database();
    }
    
    /**
     * Kategori slug'larından ID'leri getir
     * 
     * @param array|string $category_slugs Kategori slug'ları
     * @return array Kategori ID'leri
     */
    public function getCategoryIdsBySlugs($category_slugs) {
        try {
            $slugs = is_array($category_slugs) ? $category_slugs : [$category_slugs];
            $category_ids = [];
            
            foreach ($slugs as $slug) {
                $categories = $this->db->select('categories', ['slug' => $slug], 'id');
                if (!empty($categories)) {
                    $category_ids[] = $categories[0]['id'];
                }
            }
            
            return $category_ids;
        } catch (Exception $e) {
            error_log("Kategori ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cinsiyet slug'larından ID'leri getir
     * 
     * @param array|string $gender_slugs Cinsiyet slug'ları
     * @return array Cinsiyet ID'leri
     */
    public function getGenderIdsBySlugs($gender_slugs) {
        try {
            $slugs = is_array($gender_slugs) ? $gender_slugs : [$gender_slugs];
            $gender_ids = [];
            
            foreach ($slugs as $slug) {
                $genders = $this->db->select('genders', ['slug' => $slug], 'id');
                if (!empty($genders)) {
                    $gender_ids[] = $genders[0]['id'];
                }
            }
            
            return $gender_ids;
        } catch (Exception $e) {
            error_log("Cinsiyet ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Belirli kategorilere ait ürün ID'lerini getir
     * 
     * @param array $category_ids Kategori ID'leri
     * @return array Ürün ID'leri
     */
    public function getProductIdsByCategories($category_ids) {
        try {
            if (empty($category_ids)) {
                return [];
            }
            
            $relations = $this->db->select('product_categories', ['category_id' => ['IN', $category_ids]], 'product_id');
            return array_unique(array_column($relations, 'product_id'));
        } catch (Exception $e) {
            error_log("Kategori ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Belirli cinsiyetlere ait ürün ID'lerini getir
     * 
     * @param array $gender_ids Cinsiyet ID'leri
     * @return array Ürün ID'leri
     */
    public function getProductIdsByGenders($gender_ids) {
        try {
            if (empty($gender_ids)) {
                return [];
            }
            
            $relations = $this->db->select('product_genders', ['gender_id' => ['IN', $gender_ids]], 'product_id');
            return array_unique(array_column($relations, 'product_id'));
        } catch (Exception $e) {
            error_log("Cinsiyet ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Filtreleme parametrelerine göre ürün ID'lerini getir
     * 
     * @param array $filters Filtreler (categories, genders, featured, price_min, price_max)
     * @return array|null Ürün ID'leri (null = filtre yok, [] = eşleşen ürün yok)
     */
    public function getFilteredProductIds($filters) {
        try {
            $product_ids = null; // Başlangıçta filtre yok
            
            // 1. Kategori filtresi
            if (!empty($filters['categories'])) {
                $category_slugs = is_array($filters['categories']) ? $filters['categories'] : [$filters['categories']];
                $category_ids = $this->getCategoryIdsBySlugs($category_slugs);
                
                if (!empty($category_ids)) {
                    $category_product_ids = $this->getProductIdsByCategories($category_ids);
                    $product_ids = $this->intersectProductIds($product_ids, $category_product_ids);
                    
                    if (empty($product_ids)) {
                        return []; // Kategoriye ait ürün yok
                    }
                } else {
                    return []; // Kategori bulunamadı
                }
            }
            
            // 2. Cinsiyet filtresi
            if (!empty($filters['genders'])) {
                $gender_slugs = is_array($filters['genders']) ? $filters['genders'] : [$filters['genders']];
                $gender_ids = $this->getGenderIdsBySlugs($gender_slugs);
                
                if (!empty($gender_ids)) {
                    $gender_product_ids = $this->getProductIdsByGenders($gender_ids);
                    $product_ids = $this->intersectProductIds($product_ids, $gender_product_ids);
                    
                    if (empty($product_ids)) {
                        return []; // Cinsiyete ait ürün yok
                    }
                } else {
                    return []; // Cinsiyet bulunamadı
                }
            }
            
            // 3. Öne çıkan filtresi (diğer filtrelerle birlikte database sorgusunda kullanılacak)
            // 4. Fiyat filtresi (diğer filtrelerle birlikte database sorgusunda kullanılacak)
            
            return $product_ids; // null = filtre yok, array = filtrelenmiş ID'ler
            
        } catch (Exception $e) {
            error_log("Filtrelenmiş ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Öne çıkan ürün ID'lerini getir
     * 
     * @return array Öne çıkan ürün ID'leri
     */
    public function getFeaturedProductIds() {
        try {
            $products = $this->db->select('product_models', ['is_featured' => 1], 'id');
            return array_column($products, 'id');
        } catch (Exception $e) {
            error_log("Öne çıkan ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Fiyat aralığındaki ürün ID'lerini getir
     * 
     * @param float|null $min_price Minimum fiyat
     * @param float|null $max_price Maksimum fiyat
     * @return array Ürün ID'leri
     */
    public function getProductIdsByPriceRange($min_price = null, $max_price = null) {
        try {
            $conditions = [];
            
            if ($min_price !== null) {
                $conditions['base_price'] = ['>=', floatval($min_price)];
            }
            
            if ($max_price !== null) {
                if (isset($conditions['base_price'])) {
                    // Hem min hem max var
                    $conditions['base_price'] = ['BETWEEN', [floatval($min_price), floatval($max_price)]];
                } else {
                    $conditions['base_price'] = ['<=', floatval($max_price)];
                }
            }
            
            if (empty($conditions)) {
                return []; // Fiyat filtresi yok
            }
            
            $products = $this->db->select('product_models', $conditions, 'id');
            return !empty($products) ? array_column($products, 'id') : [];
            
        } catch (Exception $e) {
            error_log("Fiyat aralığı ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * İki ürün ID dizisinin kesişimini al
     * 
     * @param array|null $existing_ids Mevcut ID'ler (null = filtre yok)
     * @param array $new_ids Yeni ID'ler
     * @return array Kesişim
     */
    private function intersectProductIds($existing_ids, $new_ids) {
        if ($existing_ids === null) {
            return $new_ids; // İlk filtre
        }
        
        return array_intersect($existing_ids, $new_ids);
    }
    
    /**
     * Kategori bilgilerini slug ile getir
     * 
     * @param string $category_slug Kategori slug
     * @return array|null Kategori bilgisi
     */
    public function getCategoryBySlug($category_slug) {
        try {
            $categories = $this->db->select('categories', ['slug' => $category_slug], '*', ['limit' => 1]);
            return !empty($categories) ? $categories[0] : null;
        } catch (Exception $e) {
            error_log("Kategori slug getirme hatası: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cinsiyet bilgilerini slug ile getir
     * 
     * @param string $gender_slug Cinsiyet slug
     * @return array|null Cinsiyet bilgisi
     */
    public function getGenderBySlug($gender_slug) {
        try {
            $genders = $this->db->select('genders', ['slug' => $gender_slug], '*', ['limit' => 1]);
            return !empty($genders) ? $genders[0] : null;
        } catch (Exception $e) {
            error_log("Cinsiyet slug getirme hatası: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Aktif kategorileri getir
     * 
     * @return array Kategori listesi
     */
    public function getActiveCategories() {
        try {
            return $this->db->select('categories', [], '*', ['order' => 'name ASC']);
        } catch (Exception $e) {
            error_log("Aktif kategoriler getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Aktif cinsiyetleri getir
     * 
     * @return array Cinsiyet listesi
     */
    public function getActiveGenders() {
        try {
            return $this->db->select('genders', [], '*', ['order' => 'name ASC']);
        } catch (Exception $e) {
            error_log("Aktif cinsiyetler getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// Singleton örneği
function product_filter_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new ProductFilterService();
    }
    
    return $instance;
}
