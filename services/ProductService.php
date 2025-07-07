<?php
/**
 * Ürün Servisi - Ana Koordinatör
 * 
 * Bu dosya, ürün verilerine erişim sağlayan ana koordinatör servisidir.
 * Diğer ürün servislerini kullanarak işlemleri yönlendirir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/DatabaseFactory.php';
require_once __DIR__ . '/Product/ProductQueryService.php';
require_once __DIR__ . '/Product/ProductFilterService.php';
require_once __DIR__ . '/Product/ProductApiService.php';
require_once __DIR__ . '/Product/ProductAdminService.php';
require_once __DIR__ . '/Product/ProductManagementService.php';

/**
 * Ürün ana koordinatör servis sınıfı
 * 
 * Diğer ürün servislerini koordine eder ve geriye uyumluluk sağlar
 */
class ProductService {
    private $db;
    private $queryService;
    private $filterService;
    private $apiService;
    private $adminService;
    private $managementService;
    
    /**
     * ProductService sınıfını başlatır
     */
    public function __construct() {
        $this->db = database();
        $this->queryService = new ProductQueryService();
        $this->filterService = new ProductFilterService();
        $this->apiService = new ProductApiService();
        $this->adminService = new ProductAdminService();
        $this->managementService = new ProductManagementService();
    }
    
    /**
     * Ürün modellerini getir (Legacy metod - yeni servislere yönlendirilir)
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Filtreler
     * @return array Ürün modelleri
     */
    public function getProductModels($limit = 20, $offset = 0, $filters = []) {
        // Yeni API servisi üzerinden çalışır
        $params = [
            'page' => 1,
            'limit' => $limit
        ];
        
        // Filtreleri dönüştür
        if (!empty($filters['category_slug'])) {
            $params['categories'] = [$filters['category_slug']];
        }
        if (!empty($filters['gender_slug'])) {
            $params['genders'] = [$filters['gender_slug']];
        }
        
        $result = $this->apiService->getProductsForApi($params);
        return $result['products'] ?? [];
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
        return $this->apiService->getProductModelsWithMultiCategory($limit, $offset, $category_slugs, $featured, $sort);
    }
    
    /**
     * API için ürünleri getiren, filtreleme, sıralama ve sayfalama destekli metod
     * 
     * @param array $params Filtreleme, sıralama ve sayfalama parametreleri
     * @return array Ürünler ve toplam ürün sayısı
     */
    public function getProductsForApi($params = []) {
        return $this->apiService->getProductsForApi($params);
    }
    
    
    /**
     * Belirli bir ürün modelini ID'ye göre getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array|null Ürün modeli veya bulunamazsa boş dizi
     */
    public function getProductModel($model_id) {
        return $this->queryService->getProductModel($model_id);
    }
    
    /**
     * Bir ürün modeline ait varyantları getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün varyantları
     */
    public function getProductVariants($model_id) {
        return $this->queryService->getProductVariants($model_id);
    }
    
    /**
     * Bir ürün modeline ait görselleri getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün görselleri
     */
    public function getProductImages($model_id) {
        return $this->queryService->getProductImages($model_id);
    }
    
    /**
     * Toplam ürün sayısını getiren metod
     *
     * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
     * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
     * @return int Toplam ürün sayısı
     */
    public function getTotalProductCount($category_slugs = null, $featured = null) {
        return $this->apiService->getTotalProductCount($category_slugs, $featured);
    }
    
    /**
     * Admin panel için ürünleri getiren metod
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Filtreler
     * @return array Ürünler ve pagination bilgisi
     */
    public function getAdminProducts($limit = 20, $offset = 0, $filters = []) {
        return $this->adminService->getAdminProducts($limit, $offset, $filters);
    }
    
    /**
     * Admin panel için ürünleri getiren metod - Optimize Edilmiş (Geriye uyumluluk)
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Ürünler ve pagination bilgisi
     */
    public function getAdminProductsOptimized($limit = 20, $offset = 0) {
        return $this->adminService->getAdminProducts($limit, $offset);
    }
    
    /**
     * Ürün silme metodu - Cascade delete ile bağlantılı verileri de siler
     * 
     * @param int $product_id Ürün ID
     * @return bool Başarı durumu
     */
    public function deleteProduct($product_id) {
        return $this->managementService->deleteProduct($product_id);
    }
    
    /**
     * Ürün durumu güncelleme metodu
     * 
     * @param int $product_id Ürün ID
     * @param bool $is_featured Öne çıkan durumu
     * @return bool Başarı durumu
     */
    public function updateProductStatus($product_id, $is_featured) {
        return $this->managementService->updateProductStatus($product_id, $is_featured);
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
    return product_service()->getProductModelsWithMultiCategory($limit, $offset, $category_slugs, $featured, $sort);
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
function get_admin_products($limit = 20, $offset = 0) {
    return product_service()->getAdminProducts($limit, $offset);
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

/**
 * API için ürünleri filtreleme, sıralama ve sayfalama ile getiren fonksiyon
 * 
 * @param array $params Filtreleme, sıralama ve sayfalama parametreleri
 * @return array Ürünler ve toplam sayfa bilgisi
 */
function get_products_for_api($params = []) {
    return product_service()->getProductsForApi($params);
}
