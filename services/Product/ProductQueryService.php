<?php
/**
 * Ürün Sorgu Servisi
 * 
 * Temel ürün sorgu işlemlerini içerir
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';

class ProductQueryService {
    private $db;
    
    public function __construct() {
        $this->db = database();
    }
    
    /**
     * Belirli bir ürün modelini ID'ye göre getir
     * 
     * @param int $model_id Ürün model ID'si
     * @return array|null Ürün modeli veya bulunamazsa boş dizi
     */
    public function getProductModel($model_id) {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return [];
            }
            
            // Temel ürün bilgisini al
            $products = $this->db->select('product_models', ['id' => $model_id], '*', ['limit' => 1]);
            
            if (empty($products)) {
                return [];
            }
            
            $product = $products[0];
            $product['price'] = $product['base_price']; // Tutarlılık için
            
            // Kategori bilgilerini ekle
            $categories = $this->db->selectWithJoins('product_categories', [
                [
                    'type' => 'INNER',
                    'table' => 'categories',
                    'condition' => 'product_categories.category_id = categories.id'
                ]
            ], ['product_categories.product_id' => $model_id], 'categories.id, categories.name, categories.slug');
            
            if (!empty($categories)) {
                $product['category_name'] = $categories[0]['name'];
                $product['category_slug'] = $categories[0]['slug'];
                $product['categories'] = $categories; // Tüm kategoriler
            }
            
            // Cinsiyet bilgilerini ekle
            $genders = $this->db->selectWithJoins('product_genders', [
                [
                    'type' => 'INNER',
                    'table' => 'genders',
                    'condition' => 'product_genders.gender_id = genders.id'
                ]
            ], ['product_genders.product_id' => $model_id], 'genders.id, genders.name, genders.slug');
            
            $product['genders'] = $genders;
            
            // Ana görsel bilgisini ekle
            $images = $this->db->select('product_images', [
                'model_id' => $model_id,
                'is_primary' => 1
            ], 'image_url', ['limit' => 1]);
            
            if (!empty($images)) {
                $product['image_url'] = $images[0]['image_url'];
            } else {
                // Birincil yoksa herhangi bir resim
                $images = $this->db->select('product_images', ['model_id' => $model_id], 'image_url', ['limit' => 1]);
                if (!empty($images)) {
                    $product['image_url'] = $images[0]['image_url'];
                }
            }
            
            return [$product];
            
        } catch (Exception $e) {
            error_log("Ürün modeli getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bir ürün modeline ait varyantları getir
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün varyantları
     */
    public function getProductVariants($model_id) {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return [];
            }
            
            return $this->db->select('product_variants', ['model_id' => $model_id], '*');
            
        } catch (Exception $e) {
            error_log("Ürün varyantları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bir ürün modeline ait görselleri getir
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün görselleri
     */
    public function getProductImages($model_id) {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return [];
            }
            
            return $this->db->select('product_images', ['model_id' => $model_id], '*', ['order' => 'is_primary DESC, id ASC']);
            
        } catch (Exception $e) {
            error_log("Ürün görselleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ürün temel bilgilerini getir (hafif versiyon)
     * 
     * @param int $model_id Ürün model ID'si
     * @return array|null Ürün temel bilgileri
     */
    public function getProductBasicInfo($model_id) {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return null;
            }
            
            $products = $this->db->select('product_models', ['id' => $model_id], 
                'id, name, description, base_price, is_featured', ['limit' => 1]);
            
            return !empty($products) ? $products[0] : null;
            
        } catch (Exception $e) {
            error_log("Ürün temel bilgi getirme hatası: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Birden fazla ürün modelini ID'lere göre getir
     * 
     * @param array $model_ids Ürün model ID'leri
     * @return array Ürün modelleri
     */
    public function getProductModelsByIds($model_ids) {
        try {
            if (empty($model_ids)) {
                return [];
            }
            
            // ID'leri temizle
            $clean_ids = array_map('intval', $model_ids);
            $clean_ids = array_filter($clean_ids, function($id) { return $id > 0; });
            
            if (empty($clean_ids)) {
                return [];
            }
            
            $products = $this->db->select('product_models', ['id' => ['IN', $clean_ids]], '*');
            
            // Her ürün için ek bilgileri ekle
            foreach ($products as &$product) {
                $product['price'] = $product['base_price'];
                
                // Ana görsel
                $images = $this->db->select('product_images', [
                    'model_id' => $product['id'],
                    'is_primary' => 1
                ], 'image_url', ['limit' => 1]);
                
                if (!empty($images)) {
                    $product['image_url'] = $images[0]['image_url'];
                }
            }
            
            return $products;
            
        } catch (Exception $e) {
            error_log("Çoklu ürün getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// Singleton örneği
function product_query_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new ProductQueryService();
    }
    
    return $instance;
}
