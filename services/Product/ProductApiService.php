<?php
/**
 * Ürün API Servisi
 * 
 * Frontend için ürün API işlemlerini içerir
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/ProductFilterService.php';

class ProductApiService {
    private $db;
    private $filterService;
    
    public function __construct() {
        $this->db = database();
        $this->filterService = new ProductFilterService();
    }
    
    /**
     * API için ürünleri getiren, filtreleme, sıralama ve sayfalama destekli metod
     * 
     * @param array $params Filtreleme, sıralama ve sayfalama parametreleri
     * @return array Ürünler ve toplam ürün sayısı
     */
    public function getProductsForApi($params = []) {
        try {
            // Varsayılan parametreler
            $defaults = [
                'page' => 1,
                'limit' => 9,
                'categories' => [],
                'genders' => [],
                'sort' => 'created_at-desc',
                'featured' => null,
                'price_min' => null,
                'price_max' => null
            ];
            
            // Parametreleri birleştir
            $params = array_merge($defaults, $params);
            
            // Sayfalama parametrelerini hesapla
            $page = max(1, intval($params['page']));
            $limit = max(1, intval($params['limit']));
            $offset = ($page - 1) * $limit;
            
            // Filtreleme ile ürün ID'lerini al
            $filtered_product_ids = $this->filterService->getFilteredProductIds($params);
            
            // Temel koşullar
            $conditions = [];
            
            // Filtrelenmiş ürün ID'leri varsa ekle
            if ($filtered_product_ids !== null) {
                if (empty($filtered_product_ids)) {
                    // Filtrelerle eşleşen ürün yok
                    return $this->emptyApiResponse($page, $limit);
                }
                $conditions['id'] = ['IN', $filtered_product_ids];
            }
            
            // Öne çıkan filtresi
            if ($params['featured'] !== null) {
                $conditions['is_featured'] = $params['featured'] ? 1 : 0;
            }
            
            // Fiyat filtresi
            if ($params['price_min'] !== null) {
                $conditions['base_price'] = ['>=', floatval($params['price_min'])];
            }
            
            if ($params['price_max'] !== null) {
                if (isset($conditions['base_price'])) {
                    // Hem min hem max var
                    $conditions['base_price'] = ['BETWEEN', [floatval($params['price_min']), floatval($params['price_max'])]];
                } else {
                    $conditions['base_price'] = ['<=', floatval($params['price_max'])];
                }
            }
            
            // Sıralama
            $order = $this->buildSortOrder($params['sort']);
            
            // Toplam sayıyı al
            $total_count = $this->db->count('product_models', $conditions);
            
            // Ürünleri getir
            $options = [
                'order' => $order,
                'limit' => $limit,
                'offset' => $offset
            ];
            
            $products = $this->db->select('product_models', $conditions, '*', $options);
            
            // Ürün verilerini zenginleştir
            $formatted_products = $this->enrichProductsForApi($products);
            
            // Toplam sayfa sayısını hesapla
            $total_pages = $limit > 0 ? ceil($total_count / $limit) : 0;
            
            // Sonuçları döndür
            return [
                'products' => $formatted_products,
                'total' => $total_count,
                'page' => $page,
                'limit' => $limit,
                'pages' => $total_pages,
                'filters' => $this->getAppliedFilters($params)
            ];
            
        } catch (Exception $e) {
            error_log("API için ürün getirme hatası: " . $e->getMessage());
            return $this->emptyApiResponse($params['page'] ?? 1, $params['limit'] ?? 9);
        }
    }
    
    /**
     * Çoklu kategori desteği ile ürün modelleri getiren metod
     * 
     * @param int $limit Maksimum ürün sayısı
     * @param int $offset Başlangıç indeksi
     * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
     * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
     * @param string|null $sort Sıralama seçeneği (opsiyonel)
     * @return array Ürün modelleri
     */
    public function getProductModelsWithMultiCategory($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null) {
        try {
            $conditions = [];
            
            // Kategori filtresi
            if (!empty($category_slugs)) {
                $category_ids = $this->filterService->getCategoryIdsBySlugs($category_slugs);
                
                if (!empty($category_ids)) {
                    $product_ids = $this->filterService->getProductIdsByCategories($category_ids);
                    
                    if (!empty($product_ids)) {
                        $conditions['id'] = ['IN', $product_ids];
                    } else {
                        return []; // Eşleşen ürün yoksa boş dizi döndür
                    }
                } else {
                    return []; // Kategori bulunamadıysa boş dizi döndür
                }
            }
            
            // Öne çıkan filtresi
            if ($featured !== null) {
                $conditions['is_featured'] = $featured ? 1 : 0;
            }
            
            // Sıralama
            $order = $this->buildSortOrder($sort);
            
            $options = [
                'order' => $order,
                'limit' => $limit,
                'offset' => $offset
            ];
            
            $products = $this->db->select('product_models', $conditions, '*', $options);
            
            // Ürün verilerini zenginleştir
            return $this->enrichProductsForApi($products);
            
        } catch (Exception $e) {
            error_log("Çoklu kategori ürün getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Toplam ürün sayısını getir
     *
     * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
     * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
     * @return int Toplam ürün sayısı
     */
    public function getTotalProductCount($category_slugs = null, $featured = null) {
        try {
            $conditions = [];
            
            // Kategori filtresi
            if (!empty($category_slugs)) {
                $category_ids = $this->filterService->getCategoryIdsBySlugs($category_slugs);
                if (!empty($category_ids)) {
                    $product_ids = $this->filterService->getProductIdsByCategories($category_ids);
                    if (!empty($product_ids)) {
                        $conditions['id'] = ['IN', $product_ids];
                    } else {
                        return 0; // Kategoriye ait ürün yok
                    }
                }
            }
            
            if ($featured !== null) {
                $conditions['is_featured'] = $featured ? 1 : 0;
            }
            
            return $this->db->count('product_models', $conditions);
            
        } catch (Exception $e) {
            error_log("Toplam ürün sayısı getirme hatası: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Ürün verilerini API için zenginleştir
     * 
     * @param array $products Ham ürün verileri
     * @return array Zenginleştirilmiş ürün verileri
     */
    private function enrichProductsForApi($products) {
        $formatted_products = [];
        
        foreach ($products as $product) {
            $formatted_product = [
                'id' => $product['id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'base_price' => $product['base_price'],
                'price' => $product['base_price'], // Tutarlılık için
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
                $formatted_product['category_name'] = $categories[0]['name'];
                $formatted_product['category_slug'] = $categories[0]['slug'];
                $formatted_product['categories'] = $categories; // Tüm kategoriler
            }
            
            // Cinsiyet bilgilerini ekle - Supabase uyumlu şekilde
            $gender_relations = $this->db->select('product_genders', ['product_id' => $product['id']], 'gender_id');
            $genders = [];
            
            if (!empty($gender_relations)) {
                $gender_ids = array_column($gender_relations, 'gender_id');
                $genders = $this->db->select('genders', ['id' => ['IN', $gender_ids]], 'id, name, slug');
            }
            
            $formatted_product['genders'] = $genders;
            
            // Görsel bilgisini ekle (birincil resim)
            $images = $this->db->select('product_images', [
                'model_id' => $product['id'],
                'is_primary' => 1
            ], 'image_url', ['limit' => 1]);
            
            if (!empty($images)) {
                $formatted_product['image_url'] = $images[0]['image_url'];
            } else {
                // Birincil yoksa herhangi bir resim
                $images = $this->db->select('product_images', ['model_id' => $product['id']], 'image_url', ['limit' => 1]);
                if (!empty($images)) {
                    $formatted_product['image_url'] = $images[0]['image_url'];
                } else {
                    // Placeholder resim
                    $formatted_product['image_url'] = '/assets/images/placeholder.svg';
                }
            }
            
            $formatted_products[] = $formatted_product;
        }
        
        return $formatted_products;
    }
    
    /**
     * Sıralama order string'i oluştur
     * 
     * @param string|null $sort Sıralama parametresi
     * @return string SQL ORDER BY string'i
     */
    private function buildSortOrder($sort) {
        $order = 'created_at DESC'; // Varsayılan
        
        if (!empty($sort)) {
            $sort_parts = explode('-', $sort);
            if (count($sort_parts) == 2) {
                $sort_column = $sort_parts[0];
                $sort_direction = strtoupper($sort_parts[1]);
                
                // Güvenli sıralama sütunları
                $allowed_columns = [
                    'price' => 'base_price',
                    'name' => 'name',
                    'created_at' => 'created_at',
                    'featured' => 'is_featured'
                ];
                
                if (isset($allowed_columns[$sort_column]) && in_array($sort_direction, ['ASC', 'DESC'])) {
                    $order = $allowed_columns[$sort_column] . ' ' . $sort_direction;
                }
            }
        }
        
        return $order;
    }
    
    /**
     * Boş API yanıtı döndür
     * 
     * @param int $page Sayfa numarası
     * @param int $limit Limit
     * @return array Boş yanıt
     */
    private function emptyApiResponse($page, $limit) {
        return [
            'products' => [],
            'total' => 0,
            'page' => $page,
            'limit' => $limit,
            'pages' => 0,
            'filters' => []
        ];
    }
    
    /**
     * Uygulanmış filtreleri döndür
     * 
     * @param array $params Parametreler
     * @return array Filtre bilgileri
     */
    private function getAppliedFilters($params) {
        $filters = [];
        
        if (!empty($params['categories'])) {
            $filters['categories'] = $params['categories'];
        }
        
        if (!empty($params['genders'])) {
            $filters['genders'] = $params['genders'];
        }
        
        if ($params['featured'] !== null) {
            $filters['featured'] = $params['featured'];
        }
        
        if ($params['price_min'] !== null) {
            $filters['price_min'] = $params['price_min'];
        }
        
        if ($params['price_max'] !== null) {
            $filters['price_max'] = $params['price_max'];
        }
        
        return $filters;
    }
    
    /**
     * Popüler ürünleri getir
     * 
     * @param int $limit Limit
     * @return array Popüler ürünler
     */
    public function getPopularProducts($limit = 8) {
        try {
            // Şimdilik öne çıkan ürünleri popüler olarak döndür
            // İleride satış sayısı, görüntülenme sayısı gibi veriler eklenebilir
            $conditions = ['is_featured' => 1];
            $options = [
                'order' => 'created_at DESC',
                'limit' => $limit
            ];
            
            $products = $this->db->select('product_models', $conditions, '*', $options);
            return $this->enrichProductsForApi($products);
            
        } catch (Exception $e) {
            error_log("Popüler ürünler getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Benzer ürünleri getir
     * 
     * @param int $product_id Ürün ID
     * @param int $limit Limit
     * @return array Benzer ürünler
     */
    public function getSimilarProducts($product_id, $limit = 4) {
        try {
            $product_id = intval($product_id);
            if ($product_id <= 0) {
                return [];
            }
            
            // Ürünün kategorilerini al
            $categories = $this->db->select('product_categories', ['product_id' => $product_id], 'category_id');
            
            if (empty($categories)) {
                return [];
            }
            
            $category_ids = array_column($categories, 'category_id');
            
            // Aynı kategorideki diğer ürünleri al (kendisi hariç)
            $similar_product_ids = $this->filterService->getProductIdsByCategories($category_ids);
            $similar_product_ids = array_filter($similar_product_ids, function($id) use ($product_id) {
                return $id != $product_id;
            });
            
            if (empty($similar_product_ids)) {
                return [];
            }
            
            $conditions = ['id' => ['IN', $similar_product_ids]];
            $options = [
                'order' => 'RAND()',
                'limit' => $limit
            ];
            
            $products = $this->db->select('product_models', $conditions, '*', $options);
            return $this->enrichProductsForApi($products);
            
        } catch (Exception $e) {
            error_log("Benzer ürünler getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// Singleton örneği
function product_api_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new ProductApiService();
    }
    
    return $instance;
}
