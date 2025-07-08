<?php
/**
 * Ürün Admin Servisi
 * 
 * Admin panel için özel ürün işlemlerini içerir
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';

class ProductAdminService {
    private $db;
    
    public function __construct() {
        $this->db = database();
    }
    
    /**
     * Admin panel için ürünleri getiren metod - Batch Query Optimizasyonu
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Filtreler (search, category, status)
     * @return array Ürünler ve pagination bilgisi
     */
    public function getAdminProducts($limit = 20, $offset = 0, $filters = []) {
        try {
            $conditions = [];
            
            // Arama filtresi
            if (!empty($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $conditions['name'] = ['LIKE', $search];
            }
            
            // Kategori filtresi
            if (!empty($filters['category_id'])) {
                // Bu kategoriye ait ürün ID'lerini al
                $product_relations = $this->db->select('product_categories', 
                    ['category_id' => intval($filters['category_id'])], 'product_id');
                
                if (!empty($product_relations)) {
                    $product_ids = array_column($product_relations, 'product_id');
                    $conditions['id'] = ['IN', $product_ids];
                } else {
                    // Kategoride ürün yok
                    return [
                        'products' => [],
                        'total' => 0,
                        'limit' => $limit,
                        'offset' => $offset
                    ];
                }
            }
            
            // Durum filtresi
            if (isset($filters['is_featured'])) {
                $conditions['is_featured'] = intval($filters['is_featured']);
            }
            
            // Toplam sayıyı al
            $total_count = $this->db->count('product_models', $conditions);
            
            // Ürünleri getir - sadece gerekli alanlar
            $options = [
                'order' => 'id ASC',
                'limit' => $limit,
                'offset' => $offset
            ];
            
            $products = $this->db->select('product_models', $conditions, 
                'id, name, base_price, is_featured, created_at', $options);
            
            // Batch olarak ilişkili verileri getir
            $enriched_products = $this->enrichProductsForAdminBatch($products);
            
            return [
                'products' => $enriched_products,
                'total' => $total_count,
                'limit' => $limit,
                'offset' => $offset
            ];
            
        } catch (Exception $e) {
            error_log("Admin ürünleri getirme hatası: " . $e->getMessage());
            return [
                'products' => [],
                'total' => 0,
                'limit' => $limit,
                'offset' => $offset
            ];
        }
    }
    
    /**
     * Admin panel için ürün verilerini batch olarak zenginleştir - OPTIMIZED
     * 
     * @param array $products Ham ürün verileri
     * @return array Zenginleştirilmiş ürün verileri
     */
    private function enrichProductsForAdminBatch($products) {
        if (empty($products)) {
            return [];
        }
        
        $product_ids = array_column($products, 'id');
        
        // BATCH 1: Tüm kategori ilişkilerini tek sorguda al
        $category_relations = $this->db->select('product_categories', 
            ['product_id' => ['IN', $product_ids]], 'product_id, category_id');
        
        // BATCH 2: İlgili kategorileri tek sorguda al
        $category_ids = array_unique(array_column($category_relations, 'category_id'));
        $categories_lookup = [];
        if (!empty($category_ids)) {
            $categories = $this->db->select('categories', 
                ['id' => ['IN', $category_ids]], 'id, name, slug');
            $categories_lookup = array_column($categories, null, 'id');
        }
        
        // BATCH 3: Tüm cinsiyet ilişkilerini tek sorguda al
        $gender_relations = $this->db->select('product_genders', 
            ['product_id' => ['IN', $product_ids]], 'product_id, gender_id');
        
        // BATCH 4: İlgili cinsiyetleri tek sorguda al
        $gender_ids = array_unique(array_column($gender_relations, 'gender_id'));
        $genders_lookup = [];
        if (!empty($gender_ids)) {
            $genders = $this->db->select('genders', 
                ['id' => ['IN', $gender_ids]], 'id, name, slug');
            $genders_lookup = array_column($genders, null, 'id');
        }
        
        // BATCH 5: Tüm ana görselleri tek sorguda al
        $primary_images = $this->db->select('product_images', [
            'model_id' => ['IN', $product_ids],
            'is_primary' => 1
        ], 'model_id, image_url');
        $images_lookup = array_column($primary_images, 'image_url', 'model_id');
        
        // Memory'de hızlı lookup haritaları oluştur
        $product_categories_map = [];
        foreach ($category_relations as $rel) {
            $product_categories_map[$rel['product_id']][] = $rel['category_id'];
        }
        
        $product_genders_map = [];
        foreach ($gender_relations as $rel) {
            $product_genders_map[$rel['product_id']][] = $rel['gender_id'];
        }
        
        // Ürünleri zenginleştir - Memory'de işlem
        $enriched_products = [];
        foreach ($products as $product) {
            $product_id = $product['id'];
            
            $enriched_product = [
                'id' => $product_id,
                'name' => $product['name'],
                'base_price' => $product['base_price'],
                'is_featured' => $product['is_featured'],
                'created_at' => $product['created_at']
            ];
            
            // Kategorileri ekle
            $product_category_ids = $product_categories_map[$product_id] ?? [];
            if (!empty($product_category_ids)) {
                $first_category_id = $product_category_ids[0];
                $category = $categories_lookup[$first_category_id] ?? null;
                if ($category) {
                    $enriched_product['category_name'] = $category['name'];
                    $enriched_product['categories'] = $category;
                } else {
                    $enriched_product['category_name'] = 'Kategorisiz';
                    $enriched_product['categories'] = [];
                }
            } else {
                $enriched_product['category_name'] = 'Kategorisiz';
                $enriched_product['categories'] = [];
            }
            
            // Cinsiyetleri ekle
            $product_gender_ids = $product_genders_map[$product_id] ?? [];
            $product_genders = [];
            foreach ($product_gender_ids as $gender_id) {
                if (isset($genders_lookup[$gender_id])) {
                    $product_genders[] = $genders_lookup[$gender_id];
                }
            }
            $enriched_product['genders'] = $product_genders;
            
            // Ana görseli ekle
            $enriched_product['image_url'] = $images_lookup[$product_id] ?? null;
            
            $enriched_products[] = $enriched_product;
        }
        
        return $enriched_products;
    }
    
    /**
     * Admin panel için ürün verilerini zenginleştir - LEGACY (Geriye uyumluluk için)
     * 
     * @param array $products Ham ürün verileri
     * @return array Zenginleştirilmiş ürün verileri
     */
    private function enrichProductsForAdmin($products) {
        $enriched_products = [];
        
        foreach ($products as $product) {
            // Temel ürün bilgileri
            $enriched_product = [
                'id' => $product['id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'base_price' => $product['base_price'],
                'is_featured' => $product['is_featured'],
                'created_at' => $product['created_at']
            ];
            
            // Kategori bilgilerini ekle - Supabase uyumlu şekilde
            $category_relations = $this->db->select('product_categories', ['product_id' => $product['id']], 'category_id');
            $categories = [];
            
            if (!empty($category_relations)) {
                $category_ids = array_column($category_relations, 'category_id');
                $categories = $this->db->select('categories', ['id' => ['IN', $category_ids]], 'id, name, slug');
            }
            
            if (!empty($categories)) {
                $enriched_product['category_name'] = $categories[0]['name'];
                $enriched_product['categories'] = $categories[0]; // Admin panelinde tek kategori göster
            } else {
                $enriched_product['category_name'] = 'Kategorisiz';
                $enriched_product['categories'] = [];
            }
            
            // Cinsiyet bilgilerini ekle - Supabase uyumlu şekilde
            $gender_relations = $this->db->select('product_genders', ['product_id' => $product['id']], 'gender_id');
            $genders = [];
            
            if (!empty($gender_relations)) {
                $gender_ids = array_column($gender_relations, 'gender_id');
                $genders = $this->db->select('genders', ['id' => ['IN', $gender_ids]], 'id, name, slug');
            }
            
            $enriched_product['genders'] = $genders;
            
            // Ana görsel bilgisini ekle
            $images = $this->db->select('product_images', [
                'model_id' => $product['id'],
                'is_primary' => 1
            ], 'image_url', ['limit' => 1]);
            
            if (!empty($images)) {
                $enriched_product['image_url'] = $images[0]['image_url'];
            } else {
                // Birincil yoksa herhangi bir resim
                $images = $this->db->select('product_images', ['model_id' => $product['id']], 'image_url', ['limit' => 1]);
                if (!empty($images)) {
                    $enriched_product['image_url'] = $images[0]['image_url'];
                }
            }
            
            // Varyant sayısını ekle
            $variant_count = $this->db->count('product_variants', ['model_id' => $product['id']]);
            $enriched_product['variant_count'] = $variant_count;
            
            // Görsel sayısını ekle
            $image_count = $this->db->count('product_images', ['model_id' => $product['id']]);
            $enriched_product['image_count'] = $image_count;
            
            $enriched_products[] = $enriched_product;
        }
        
        return $enriched_products;
    }
    
    /**
     * Ürün istatistiklerini getir
     * 
     * @return array İstatistikler
     */
    public function getProductStats() {
        try {
            $stats = [];
            
            // Toplam ürün sayısı
            $stats['total_products'] = $this->db->count('product_models');
            
            // Öne çıkan ürün sayısı
            $stats['featured_products'] = $this->db->count('product_models', ['is_featured' => 1]);
            
            // Kategorisiz ürün sayısı
            $products_with_categories = $this->db->select('product_categories', [], 'DISTINCT product_id');
            $products_with_category_ids = array_column($products_with_categories, 'product_id');
            
            if (!empty($products_with_category_ids)) {
                $stats['uncategorized_products'] = $this->db->count('product_models', 
                    ['id' => ['NOT IN', $products_with_category_ids]]);
            } else {
                $stats['uncategorized_products'] = $stats['total_products'];
            }
            
            // Varyant sayısı
            $stats['total_variants'] = $this->db->count('product_variants');
            
            // Görsel sayısı
            $stats['total_images'] = $this->db->count('product_images');
            
            // Bu ayki yeni ürünler
            $this_month_start = date('Y-m-01 00:00:00');
            $stats['this_month_products'] = $this->db->count('product_models', 
                ['created_at' => ['>=', $this_month_start]]);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Ürün istatistikleri getirme hatası: " . $e->getMessage());
            return [
                'total_products' => 0,
                'featured_products' => 0,
                'uncategorized_products' => 0,
                'total_variants' => 0,
                'total_images' => 0,
                'this_month_products' => 0
            ];
        }
    }
    
    /**
     * Kategori bazında ürün sayılarını getir
     * 
     * @return array Kategori istatistikleri
     */
    public function getCategoryProductCounts() {
        try {
            $categories = $this->db->select('categories', [], 'id, name, slug', ['order' => 'name ASC']);
            $category_stats = [];
            
            foreach ($categories as $category) {
                $product_count = $this->db->count('product_categories', ['category_id' => $category['id']]);
                
                $category_stats[] = [
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'product_count' => $product_count
                ];
            }
            
            return $category_stats;
            
        } catch (Exception $e) {
            error_log("Kategori ürün sayıları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Son eklenen ürünleri getir
     * 
     * @param int $limit Limit
     * @return array Son ürünler
     */
    public function getRecentProducts($limit = 10) {
        try {
            $options = [
                'order' => 'created_at DESC',
                'limit' => $limit
            ];
            
            $products = $this->db->select('product_models', [], 
                'id, name, base_price, is_featured, created_at', $options);
            
            // Kategorileri ekle - Supabase uyumlu şekilde
            foreach ($products as &$product) {
                $category_relations = $this->db->select('product_categories', ['product_id' => $product['id']], 'category_id', ['limit' => 1]);
                
                if (!empty($category_relations)) {
                    $categories = $this->db->select('categories', ['id' => $category_relations[0]['category_id']], 'name', ['limit' => 1]);
                    $product['category_name'] = !empty($categories) ? $categories[0]['name'] : 'Kategorisiz';
                } else {
                    $product['category_name'] = 'Kategorisiz';
                }
            }
            
            return $products;
            
        } catch (Exception $e) {
            error_log("Son ürünler getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ürün detayını admin için getir
     * 
     * @param int $product_id Ürün ID
     * @return array|null Ürün detayı
     */
    public function getProductDetailForAdmin($product_id) {
        try {
            $product_id = intval($product_id);
            if ($product_id <= 0) {
                return null;
            }
            
            // Temel ürün bilgisi
            $products = $this->db->select('product_models', ['id' => $product_id], '*', ['limit' => 1]);
            
            if (empty($products)) {
                return null;
            }
            
            $product = $products[0];
            
            // Kategorileri ekle - Supabase uyumlu şekilde
            $category_relations = $this->db->select('product_categories', ['product_id' => $product_id], 'category_id');
            $categories = [];
            
            if (!empty($category_relations)) {
                $category_ids = array_column($category_relations, 'category_id');
                $categories = $this->db->select('categories', ['id' => ['IN', $category_ids]], 'id, name, slug');
            }
            
            $product['categories'] = $categories;
            
            // Cinsiyetleri ekle - Supabase uyumlu şekilde
            $gender_relations = $this->db->select('product_genders', ['product_id' => $product_id], 'gender_id');
            $genders = [];
            
            if (!empty($gender_relations)) {
                $gender_ids = array_column($gender_relations, 'gender_id');
                $genders = $this->db->select('genders', ['id' => ['IN', $gender_ids]], 'id, name, slug');
            }
            
            $product['genders'] = $genders;
            
            // Görselleri ekle
            $images = $this->db->select('product_images', ['model_id' => $product_id], '*', 
                ['order' => 'is_primary DESC, id ASC']);
            $product['images'] = $images;
            
            // Varyantları ekle
            $variants = $this->db->select('product_variants', ['model_id' => $product_id], '*', 
                ['order' => 'id ASC']);
            $product['variants'] = $variants;
            
            return $product;
            
        } catch (Exception $e) {
            error_log("Admin ürün detayı getirme hatası: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Ürün durum istatistiklerini getir
     * 
     * @return array Durum istatistikleri
     */
    public function getProductStatusStats() {
        try {
            return [
                'active' => $this->db->count('product_models'),
                'featured' => $this->db->count('product_models', ['is_featured' => 1]),
                'not_featured' => $this->db->count('product_models', ['is_featured' => 0]),
                'with_variants' => $this->getProductsWithVariantsCount(),
                'without_variants' => $this->getProductsWithoutVariantsCount(),
                'with_images' => $this->getProductsWithImagesCount(),
                'without_images' => $this->getProductsWithoutImagesCount()
            ];
            
        } catch (Exception $e) {
            error_log("Ürün durum istatistikleri hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Varyantı olan ürün sayısını getir
     * 
     * @return int Ürün sayısı
     */
    private function getProductsWithVariantsCount() {
        try {
            $products_with_variants = $this->db->select('product_variants', [], 'DISTINCT model_id');
            return count($products_with_variants);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Varyantı olmayan ürün sayısını getir
     * 
     * @return int Ürün sayısı
     */
    private function getProductsWithoutVariantsCount() {
        try {
            $products_with_variants = $this->db->select('product_variants', [], 'DISTINCT model_id');
            $products_with_variant_ids = array_column($products_with_variants, 'model_id');
            
            if (!empty($products_with_variant_ids)) {
                return $this->db->count('product_models', ['id' => ['NOT IN', $products_with_variant_ids]]);
            } else {
                return $this->db->count('product_models');
            }
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Görseli olan ürün sayısını getir
     * 
     * @return int Ürün sayısı
     */
    private function getProductsWithImagesCount() {
        try {
            $products_with_images = $this->db->select('product_images', [], 'DISTINCT model_id');
            return count($products_with_images);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Görseli olmayan ürün sayısını getir
     * 
     * @return int Ürün sayısı
     */
    private function getProductsWithoutImagesCount() {
        try {
            $products_with_images = $this->db->select('product_images', [], 'DISTINCT model_id');
            $products_with_image_ids = array_column($products_with_images, 'model_id');
            
            if (!empty($products_with_image_ids)) {
                return $this->db->count('product_models', ['id' => ['NOT IN', $products_with_image_ids]]);
            } else {
                return $this->db->count('product_models');
            }
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Arama önerilerini getir
     * 
     * @param string $query Arama terimi
     * @param int $limit Limit
     * @return array Öneriler
     */
    public function getSearchSuggestions($query, $limit = 10) {
        try {
            $search_term = '%' . $query . '%';
            $options = [
                'limit' => $limit,
                'order' => 'name ASC'
            ];
            
            $products = $this->db->select('product_models', 
                ['name' => ['LIKE', $search_term]], 
                'id, name', 
                $options);
            
            return $products;
            
        } catch (Exception $e) {
            error_log("Arama önerileri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// Singleton örneği
function product_admin_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new ProductAdminService();
    }
    
    return $instance;
}
