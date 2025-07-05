<?php
/**
 * Ürün Servisi
 * 
 * Bu dosya, ürün verilerine erişim sağlayan servisi içerir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/SupabaseClient.php';

/**
 * Ürün servis sınıfı
 * 
 * Ürünlerle ilgili tüm veritabanı işlemlerini içerir
 */
class ProductService {
    private $supabase;
    
    /**
     * ProductService sınıfını başlatır
     */
    public function __construct() {
        $this->supabase = supabase();
    }
    
    /**
     * Ürün modellerini getiren metod
     * 
     * @param int $limit Maksimum ürün sayısı
     * @param int $offset Başlangıç indeksi
     * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
     * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
     * @param string|null $sort Sıralama seçeneği (opsiyonel)
     * @return array Ürün modelleri
     */
    public function getProductModels($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null) {
        $params = [];
        $whereConditions = [];
        
        // Temel SQL sorgusu
        $sql = "
        SELECT
            pm.id, 
            pm.name, 
            pm.description, 
            pm.base_price,
            pm.is_featured,
            c.name as category_name,
            c.slug as category_slug,
            pi.image_url
        FROM 
            product_models pm
        JOIN 
            categories c ON pm.category_id = c.id
        LEFT JOIN 
            (SELECT DISTINCT ON (model_id) model_id, image_url FROM product_images WHERE is_primary = true) pi 
            ON pm.id = pi.model_id
        ";
        
        // Kategori filtresi ekle
        if (!empty($category_slugs)) {
            if (is_array($category_slugs)) {
                $whereConditions[] = "c.slug IN :categories";
                $params[':categories'] = $category_slugs;
            } else {
                $whereConditions[] = "c.slug = :category";
                $params[':category'] = $category_slugs;
            }
        }
        
        // Öne çıkan filtresi ekle
        if ($featured !== null) {
            $whereConditions[] = "pm.is_featured = :featured";
            $params[':featured'] = $featured;
        }
        
        // WHERE koşullarını ekle
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // Sıralama seçeneği
        if ($sort) {
            switch ($sort) {
                case 'price_asc':
                    $sql .= " ORDER BY pm.base_price ASC";
                    break;
                case 'price_desc':
                    $sql .= " ORDER BY pm.base_price DESC";
                    break;
                case 'name_asc':
                    $sql .= " ORDER BY pm.name ASC";
                    break;
                case 'name_desc':
                    $sql .= " ORDER BY pm.name DESC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY pm.created_at DESC";
                    break;
                default:
                    $sql .= " ORDER BY pm.id ASC";
            }
        } else {
            $sql .= " ORDER BY pm.id ASC";
        }
        
        // Sayfalama
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int)$limit;
        $params[':offset'] = (int)$offset;
        
        try {
            // SQL sorgusu çalıştır
            $response = $this->supabase->executeRawSql($sql, $params);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Ürün modelleri getirme hatası: " . $e->getMessage());
            
            // Plan B: Temel REST API çağrısı
            try {
                return $this->getProductModelsWithFallback($limit, $offset, $category_slugs, $featured, $sort);
            } catch (Exception $fallbackError) {
                error_log("Ürün modelleri yedek getirme hatası: " . $fallbackError->getMessage());
                return [];
            }
        }
    }
    
    /**
     * Yedek ürün getirme metodu (API hata verdiğinde)
     */
    private function getProductModelsWithFallback($limit, $offset, $category_slugs, $featured, $sort) {
        $basic_query = [
            'select' => '*',
            'limit' => $limit,
            'offset' => $offset
        ];
        
        // Kategori filtresi
        if (!empty($category_slugs)) {
            $category_query = [
                'select' => 'id',
                'slug' => is_array($category_slugs) ? 'in.(' . implode(',', $category_slugs) . ')' : 'eq.' . $category_slugs
            ];
            $category_response = $this->supabase->request('categories?' . http_build_query($category_query));
            $category_result = $category_response['body'] ?? [];
            
            if (!empty($category_result)) {
                $category_ids = array_column($category_result, 'id');
                $basic_query['category_id'] = 'in.(' . implode(',', $category_ids) . ')';
            }
        }
        
        // Öne çıkan filtresi
        if ($featured !== null) {
            $basic_query['is_featured'] = 'eq.' . ($featured ? 'true' : 'false');
        }
        
        // Sıralama
        if ($sort) {
            $orderMap = [
                'price_asc' => 'base_price.asc',
                'price_desc' => 'base_price.desc',
                'name_asc' => 'name.asc',
                'name_desc' => 'name.desc',
                'newest' => 'created_at.desc'
            ];
            
            if (isset($orderMap[$sort])) {
                $basic_query['order'] = $orderMap[$sort];
            }
        }
        
        $products_response = $this->supabase->request('product_models?' . http_build_query($basic_query));
        $products = $products_response['body'] ?? [];
        
        // Kategori ve görsel bilgilerini ekle
        foreach ($products as &$product) {
            // Kategori bilgisi
            $cat_query = [
                'select' => 'name,slug',
                'id' => 'eq.' . $product['category_id'],
                'limit' => 1
            ];
            $cat_response = $this->supabase->request('categories?' . http_build_query($cat_query));
            $cat_result = $cat_response['body'] ?? [];
            
            if (!empty($cat_result)) {
                $product['category_name'] = $cat_result[0]['name'];
                $product['category_slug'] = $cat_result[0]['slug'];
            }
            
            // Görsel bilgisi
            $img_query = [
                'select' => 'image_url',
                'model_id' => 'eq.' . $product['id'],
                'is_primary' => 'eq.true',
                'limit' => 1
            ];
            $img_response = $this->supabase->request('product_images?' . http_build_query($img_query));
            $img_result = $img_response['body'] ?? [];
            
            if (!empty($img_result)) {
                $product['image_url'] = $img_result[0]['image_url'];
            }
        }
        
        return $products;
    }
    
    /**
     * Belirli bir ürün modelini ID'ye göre getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array|null Ürün modeli veya bulunamazsa boş dizi
     */
    public function getProductModel($model_id) {
        $sql = "
        SELECT
            pm.*,
            c.name as category_name,
            c.slug as category_slug,
            pi.image_url
        FROM 
            product_models pm
        JOIN 
            categories c ON pm.category_id = c.id
        LEFT JOIN 
            (SELECT DISTINCT ON (model_id) model_id, image_url FROM product_images WHERE is_primary = true) pi 
            ON pm.id = pi.model_id
        WHERE 
            pm.id = :model_id
        LIMIT 1
        ";
        
        try {
            $response = $this->supabase->executeRawSql($sql, [':model_id' => $model_id]);
            $result = $response['body'] ?? [];
            
            if (!empty($result)) {
                // base_price -> price olarak da ekle (tutarlılık için)
                $result[0]['price'] = $result[0]['base_price'];
                return $result;
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Ürün modeli getirme hatası: " . $e->getMessage());
            
            // Yedek yöntem ile tekrar dene
            try {
                return $this->getProductModelWithFallback($model_id);
            } catch (Exception $fallbackError) {
                error_log("Ürün modeli yedek getirme hatası: " . $fallbackError->getMessage());
                return [];
            }
        }
    }
    
    /**
     * Yedek ürün modeli getirme metodu
     */
    private function getProductModelWithFallback($model_id) {
        $query = [
            'select' => 'id,name,description,features,base_price,is_featured,created_at,category_id',
            'id' => 'eq.' . $model_id,
            'limit' => 1
        ];
        
        $product_response = $this->supabase->request('product_models?' . http_build_query($query));
        $product_result = $product_response['body'] ?? [];
        
        if (empty($product_result)) {
            return [];
        }
        
        // Kategori bilgisini al
        $category_id = isset($product_result[0]['category_id']) ? $product_result[0]['category_id'] : 0;
        $category_query = [
            'select' => 'name,slug',
            'id' => 'eq.' . $category_id,
            'limit' => 1
        ];
        
        $category_response = $this->supabase->request('categories?' . http_build_query($category_query));
        $category_result = $category_response['body'] ?? [];
        
        // Ürün görselini al
        $image_query = [
            'select' => 'image_url',
            'model_id' => 'eq.' . $model_id,
            'is_primary' => 'eq.true',
            'limit' => 1
        ];
        
        $image_response = $this->supabase->request('product_images?' . http_build_query($image_query));
        $image_result = $image_response['body'] ?? [];
        
        // Ürün ve kategori verilerini birleştir
        $product = $product_result[0];
        $product['price'] = $product['base_price']; // Tutarlılık için
        
        if (!empty($category_result)) {
            $product['category_name'] = $category_result[0]['name'];
            $product['category_slug'] = $category_result[0]['slug'];
        }
        
        if (!empty($image_result)) {
            $product['image_url'] = $image_result[0]['image_url'];
        }
        
        return [$product];
    }
    
    /**
     * Bir ürün modeline ait varyantları getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün varyantları
     */
    public function getProductVariants($model_id) {
        try {
            $response = $this->supabase->request('product_variants?model_id=eq.' . $model_id);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Ürün varyantları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bir ürün modeline ait görselleri getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün görselleri
     */
    public function getProductImages($model_id) {
        try {
            $response = $this->supabase->request('product_images?model_id=eq.' . $model_id);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Ürün görselleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Toplam ürün sayısını getiren metod
     *
     * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
     * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
     * @return int Toplam ürün sayısı
     */
    public function getTotalProductCount($category_slugs = null, $featured = null) {
        $query = [
            'select' => 'count',
        ];
        
        if (!empty($category_slugs)) {
            if (is_array($category_slugs)) {
                $query['category_slug'] = 'in.(' . implode(',', $category_slugs) . ')';
            } else {
                $query['category_slug'] = 'eq.' . $category_slugs;
            }
        }
        
        if ($featured !== null) {
            $query['is_featured'] = 'eq.' . ($featured ? 'true' : 'false');
        }
        
        try {
            $queryString = http_build_query($query);
            $response = $this->supabase->request('product_models_view?' . $queryString);
            $result = $response['body'] ?? [];
            
            if (is_array($result) && isset($result[0]['count'])) {
                return (int)$result[0]['count'];
            }
            
            return 0;
        } catch (Exception $e) {
            error_log("Toplam ürün sayısı getirme hatası: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Admin panel için ürünleri getiren metod
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param string $search Arama terimi
     * @param string $category_filter Kategori filtresi
     * @param string $status_filter Durum filtresi
     * @return array Ürünler ve pagination bilgisi
     */
    public function getAdminProducts($limit = 20, $offset = 0, $search = '', $category_filter = '', $status_filter = '') {
        try {
            // Product models tablosundan veri çek, kategorilerle birlikte
            $query_parts = ['select=id,name,base_price,is_featured,created_at,category_id,categories(name)'];
            
            // Arama filtresi
            if (!empty($search)) {
                $query_parts[] = 'name=ilike.*' . urlencode($search) . '*';
            }
            
            // Kategori filtresi
            if (!empty($category_filter) && $category_filter !== 'all') {
                $query_parts[] = 'category_id=eq.' . intval($category_filter);
            }
            
            // Durum filtresi
            if (!empty($status_filter) && $status_filter !== 'all') {
                if ($status_filter === 'featured') {
                    $query_parts[] = 'is_featured=eq.true';
                } elseif ($status_filter === 'normal') {
                    $query_parts[] = 'is_featured=eq.false';
                }
            }
            
            // Sıralama ve sayfalama
            $query_parts[] = 'order=created_at.desc';
            $query_parts[] = 'limit=' . intval($limit);
            $query_parts[] = 'offset=' . intval($offset);
            
            $query_string = implode('&', $query_parts);
            $response = $this->supabase->request('product_models?' . $query_string);
            $products = $response['body'] ?? [];
            
            // Kategori bilgilerini manuel olarak ekle (join çalışmazsa)
            foreach ($products as &$product) {
                if (!isset($product['categories']) && isset($product['category_id'])) {
                    $cat_response = $this->supabase->request('categories?select=name&id=eq.' . $product['category_id'] . '&limit=1');
                    $cat_data = $cat_response['body'] ?? [];
                    $product['categories'] = !empty($cat_data) ? $cat_data[0] : ['name' => 'Kategorisiz'];
                }
            }
            
            // Toplam sayı için basit count sorgusu
            $count_query_parts = [];
            if (!empty($search)) {
                $count_query_parts[] = 'name=ilike.*' . urlencode($search) . '*';
            }
            if (!empty($category_filter) && $category_filter !== 'all') {
                $count_query_parts[] = 'category_id=eq.' . intval($category_filter);
            }
            if (!empty($status_filter) && $status_filter !== 'all') {
                if ($status_filter === 'featured') {
                    $count_query_parts[] = 'is_featured=eq.true';
                } elseif ($status_filter === 'normal') {
                    $count_query_parts[] = 'is_featured=eq.false';
                }
            }
            
            $count_query_string = empty($count_query_parts) ? '' : '?' . implode('&', $count_query_parts);
            $count_response = $this->supabase->request('product_models' . $count_query_string);
            $total_count = count($count_response['body'] ?? []);
            
            return [
                'products' => $products,
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
     * Ürün silme metodu
     * 
     * @param int $product_id Ürün ID
     * @return bool Başarı durumu
     */
    public function deleteProduct($product_id) {
        try {
            $response = $this->supabase->request('product_models?id=eq.' . intval($product_id), 'DELETE');
            return !empty($response);
        } catch (Exception $e) {
            error_log("Ürün silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürün durumu güncelleme metodu
     * 
     * @param int $product_id Ürün ID
     * @param bool $is_featured Öne çıkan durumu
     * @return bool Başarı durumu
     */
    public function updateProductStatus($product_id, $is_featured) {
        try {
            $data = ['is_featured' => $is_featured];
            $response = $this->supabase->request('product_models?id=eq.' . intval($product_id), 'PATCH', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Ürün durumu güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
}

// ProductService sınıfı singleton örneği
function product_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new ProductService();
    }
    
    return $instance;
}

// Geriye uyumluluk için fonksiyonlar
/**
 * Ürün modellerini getiren fonksiyon
 * 
 * @param int $limit Maksimum ürün sayısı
 * @param int $offset Başlangıç indeksi
 * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
 * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
 * @param string|null $sort Sıralama seçeneği (opsiyonel)
 * @return array Ürün modelleri
 */
function get_product_models($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null) {
    return product_service()->getProductModels($limit, $offset, $category_slugs, $featured, $sort);
}

/**
 * Belirli bir ürün modelini ID'ye göre getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array|null Ürün modeli veya bulunamazsa null
 */
function get_product_model($model_id) {
    return product_service()->getProductModel($model_id);
}

/**
 * Bir ürün modeline ait varyantları getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array Ürün varyantları
 */
function get_product_variants($model_id) {
    return product_service()->getProductVariants($model_id);
}

/**
 * Bir ürün modeline ait görselleri getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array Ürün görselleri
 */
function get_product_images($model_id) {
    return product_service()->getProductImages($model_id);
}

/**
 * Toplam ürün sayısını getiren fonksiyon
 *
 * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
 * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
 * @return int Toplam ürün sayısı
 */
function get_total_product_count($category_slugs = null, $featured = null) {
    return product_service()->getTotalProductCount($category_slugs, $featured);
}

/**
 * Admin için tüm ürünleri getiren fonksiyon
 * 
 * @param int $limit Limit
 * @param int $offset Offset
 * @param string $search Arama terimi
 * @param string $category_filter Kategori filtresi
 * @param string $status_filter Durum filtresi
 * @return array Ürünler ve pagination bilgisi
 */
function get_admin_products($limit = 20, $offset = 0, $search = '', $category_filter = '', $status_filter = '') {
    return product_service()->getAdminProducts($limit, $offset, $search, $category_filter, $status_filter);
}

/**
 * Ürün silme fonksiyonu
 * 
 * @param int $product_id Ürün ID
 * @return bool Başarı durumu
 */
function delete_product($product_id) {
    return product_service()->deleteProduct($product_id);
}

/**
 * Ürün durumu güncelleme fonksiyonu
 * 
 * @param int $product_id Ürün ID
 * @param bool $is_featured Öne çıkan durumu
 * @return bool Başarı durumu
 */
function update_product_status($product_id, $is_featured) {
    return product_service()->updateProductStatus($product_id, $is_featured);
}
