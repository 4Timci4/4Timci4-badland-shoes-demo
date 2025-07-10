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
            $products = $this->db->select('product_models', ['id' => $model_id], ['*'], ['limit' => 1]);
            
            if (empty($products)) {
                return [];
            }
            
            $product = $products[0];
            
            // Kategori bilgilerini ekle - MariaDB uyumlu şekilde
            $category_relations = $this->db->select('product_categories', ['product_id' => $model_id], 'category_id');
            $categories = [];
            
            if (!empty($category_relations)) {
                $category_ids = array_column($category_relations, 'category_id');
                if (!empty($category_ids)) {
                    // MariaDB için IN operatörünü düzelt
                    $categories = $this->db->select('categories', ['id' => ['IN', $category_ids]], ['id', 'name', 'slug']);
                }
            }
            
            if (!empty($categories)) {
                $product['category_name'] = $categories[0]['name'];
                $product['category_slug'] = $categories[0]['slug'];
                $product['categories'] = $categories; // Tüm kategoriler
            } else {
                // Kategori bulunamazsa varsayılan değerler
                $product['category_name'] = 'Ayakkabı';
                $product['category_slug'] = 'ayakkabi';
                $product['categories'] = [];
            }
            
            // Cinsiyet bilgilerini ekle - MariaDB uyumlu şekilde
            $gender_relations = $this->db->select('product_genders', ['product_id' => $model_id], 'gender_id');
            $genders = [];
            
            if (!empty($gender_relations)) {
                $gender_ids = array_column($gender_relations, 'gender_id');
                if (!empty($gender_ids)) {
                    // MariaDB için IN operatörünü düzelt
                    $genders = $this->db->select('genders', ['id' => ['IN', $gender_ids]], ['id', 'name', 'slug']);
                }
            }
            
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
            
            return $product;
            
        } catch (Exception $e) {
            error_log("Ürün modeli getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bir ürün modeline ait varyantları getir
     * 
     * @param int $model_id Ürün model ID'si
     * @param bool $active_only Sadece aktif varyantları getir (varsayılan: true)
     * @return array Ürün varyantları
     */
    public function getProductVariants($model_id, $active_only = true) {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return [];
            }
            
            $conditions = ['model_id' => $model_id];
            
            // Eğer sadece aktif varyantlar isteniyorsa filtrele
            if ($active_only) {
                $conditions['is_active'] = true;
            }
            
            return $this->db->select('product_variants', $conditions, ['*']);
            
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
            
            return $this->db->select('product_images', ['model_id' => $model_id], ['*'], ['order' => 'is_primary DESC, id ASC']);
            
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
                'id, name, description, is_featured', ['limit' => 1]);
            
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
            
            $products = $this->db->select('product_models', ['id' => ['IN', $clean_ids]], ['*']);
            
            // Her ürün için ek bilgileri ekle
            foreach ($products as &$product) {
                
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
    
    /**
     * Belirli bir varyantı ID'ye göre getir
     *
     * @param int $variant_id Varyant ID'si
     * @return array|null Varyant bilgisi veya bulunamazsa boş dizi
     */
    public function getVariantById($variant_id) {
        try {
            $variant_id = intval($variant_id);
            if ($variant_id <= 0) {
                return [];
            }
            
            // Varyant bilgilerini al
            $variants = $this->db->select('product_variants', ['id' => $variant_id], ['*'], ['limit' => 1]);
            
            if (empty($variants)) {
                return [];
            }
            
            $variant = $variants[0];
            
            // Renk bilgilerini al
            if (!empty($variant['color_id'])) {
                $colors = $this->db->select('colors', ['id' => $variant['color_id']], ['name', 'hex_code'], ['limit' => 1]);
                if (!empty($colors)) {
                    $variant['color_name'] = $colors[0]['name'];
                    $variant['color_hex'] = $colors[0]['hex_code'];
                }
            }
            
            // Beden bilgilerini al
            if (!empty($variant['size_id'])) {
                $sizes = $this->db->select('sizes', ['id' => $variant['size_id']], ['size_value'], ['limit' => 1]);
                if (!empty($sizes)) {
                    $variant['size_value'] = $sizes[0]['size_value'];
                }
            }
            
            return $variant;
            
        } catch (Exception $e) {
            error_log("Varyant getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Belirli bir varyanta ve renge ait görselleri getir
     *
     * @param int $model_id Ürün model ID'si
     * @param int $color_id Renk ID'si
     * @return array Ürün görselleri
     */
    public function getVariantImages($model_id, $color_id) {
        try {
            $model_id = intval($model_id);
            $color_id = intval($color_id);
            
            if ($model_id <= 0 || $color_id <= 0) {
                return [];
            }
            
            // Önce belirli renk için görselleri ara
            $images = $this->db->select('product_images', [
                'model_id' => $model_id,
                'color_id' => $color_id
            ], ['*'], ['order' => 'is_primary DESC, id ASC']);
            
            // Eğer renk için görsel bulunamazsa, ana ürün görsellerini getir
            if (empty($images)) {
                $images = $this->db->select('product_images', [
                    'model_id' => $model_id,
                    'is_primary' => 1
                ], ['*'], ['limit' => 1]);
                
                // Birincil görsel de yoksa, herhangi bir görsel
                if (empty($images)) {
                    $images = $this->db->select('product_images', [
                        'model_id' => $model_id
                    ], ['*'], ['limit' => 1]);
                }
            }
            
            return $images;
            
        } catch (Exception $e) {
            error_log("Varyant görselleri getirme hatası: " . $e->getMessage());
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
