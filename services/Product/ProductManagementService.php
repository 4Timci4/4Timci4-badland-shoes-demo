<?php
/**
 * Ürün Yönetim Servisi
 * 
 * Ürün CRUD işlemlerini ve yönetim işlemlerini içerir
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/../../lib/AutoCache.php';

class ProductManagementService {
    private $db;
    
    public function __construct() {
        $this->db = database();
    }
    
    /**
     * Ürün silme metodu - Cascade delete ile bağlantılı verileri de siler
     * 
     * @param int $product_id Ürün ID
     * @return bool Başarı durumu
     */
    public function deleteProduct($product_id) {
        try {
            $product_id = intval($product_id);
            
            if ($product_id <= 0) {
                throw new Exception("Geçersiz ürün ID: $product_id");
            }
            
            // Ürün var mı kontrol et
            $product = $this->db->select('product_models', ['id' => $product_id], 'id', ['limit' => 1]);
            if (empty($product)) {
                throw new Exception("Ürün bulunamadı: $product_id");
            }
            
            // Transaction başlat (MariaDB için)
            if (method_exists($this->db, 'beginTransaction')) {
                $this->db->beginTransaction();
            }
            
            try {
                // 1. Ürün kategorilerini sil
                $this->db->delete('product_categories', ['product_id' => $product_id]);
                
                // 2. Ürün cinsiyetlerini sil
                $this->db->delete('product_genders', ['product_id' => $product_id]);
                
                // 3. Ürün görsellerini sil
                $this->db->delete('product_images', ['model_id' => $product_id]);
                
                // 4. Ürün varyantlarını sil
                $this->db->delete('product_variants', ['model_id' => $product_id]);
                
                // 5. Ürün özelliklerini sil (varsa)
                $this->db->delete('product_attributes', ['product_id' => $product_id]);
                
                // 6. En son ana ürün modelini sil
                $result = $this->db->delete('product_models', ['id' => $product_id]);
                
                // Transaction commit
                if (method_exists($this->db, 'commit')) {
                    $this->db->commit();
                }
                
                // OTOMATIK CACHE INVALIDATION - Ürün silindikten sonra
                $this->invalidateProductCaches($product_id);
                
                return $result !== false;
                
            } catch (Exception $e) {
                // Transaction rollback
                if (method_exists($this->db, 'rollback')) {
                    $this->db->rollback();
                }
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Ürün silme hatası (ID: $product_id): " . $e->getMessage());
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
            $product_id = intval($product_id);
            
            if ($product_id <= 0) {
                return false;
            }
            
            $data = ['is_featured' => $is_featured ? 1 : 0];
            $result = $this->db->update('product_models', $data, ['id' => $product_id]);
            return $result !== false;
            
        } catch (Exception $e) {
            error_log("Ürün durumu güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürün oluşturma metodu
     * 
     * @param array $product_data Ürün verileri
     * @return int|false Yeni ürün ID'si veya false
     */
    public function createProduct($product_data) {
        try {
            // Gerekli alanları kontrol et
            $required_fields = ['name'];
            foreach ($required_fields as $field) {
                if (empty($product_data[$field])) {
                    throw new Exception("Gerekli alan eksik: $field");
                }
            }
            
            
            // Ürün verisini hazırla
            $insert_data = [
                'name' => trim($product_data['name']),
                'description' => $product_data['description'] ?? '',
                'features' => $product_data['features'] ?? '',
                'is_featured' => isset($product_data['is_featured']) ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Transaction başlat
            if (method_exists($this->db, 'beginTransaction')) {
                $this->db->beginTransaction();
            }
            
            try {
                // Ürün modelini oluştur
                $result = $this->db->insert('product_models', $insert_data);
                
                if (!$result || (is_array($result) && empty($result))) {
                    throw new Exception("Ürün oluşturulamadı");
                }
                
                // Insert sonucu array ise ID'yi çıkar
                $product_id = is_array($result) ? (isset($result['id']) ? $result['id'] : $result[0]['id'] ?? false) : $result;
                
                if (!$product_id) {
                    throw new Exception("Ürün ID'si alınamadı");
                }
                
                // Kategorileri ekle
                if (!empty($product_data['category_ids'])) {
                    $this->addProductCategories(intval($product_id), $product_data['category_ids']);
                }
                
                // Cinsiyetleri ekle
                if (!empty($product_data['gender_ids'])) {
                    $this->addProductGenders(intval($product_id), $product_data['gender_ids']);
                }
                
                // Görselleri ekle
                if (!empty($product_data['images'])) {
                    $this->addProductImages(intval($product_id), $product_data['images']);
                }
                
                // Transaction commit
                if (method_exists($this->db, 'commit')) {
                    $this->db->commit();
                }
                
                return $product_id;
                
            } catch (Exception $e) {
                // Transaction rollback
                if (method_exists($this->db, 'rollback')) {
                    $this->db->rollback();
                }
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Ürün oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürün güncelleme metodu
     * 
     * @param int $product_id Ürün ID
     * @param array $product_data Ürün verileri
     * @return bool Başarı durumu
     */
    public function updateProduct($product_id, $product_data) {
        try {
            $product_id = intval($product_id);
            
            if ($product_id <= 0) {
                return false;
            }
            
            // Ürün verisini hazırla
            $update_data = [];
            
            if (isset($product_data['name'])) {
                $update_data['name'] = trim($product_data['name']);
            }
            
            if (isset($product_data['description'])) {
                $update_data['description'] = $product_data['description'];
            }
            
            if (isset($product_data['features'])) {
                $update_data['features'] = $product_data['features'];
            }
            
            
            if (isset($product_data['is_featured'])) {
                $update_data['is_featured'] = $product_data['is_featured'] ? 1 : 0;
            }
            
            if (empty($update_data)) {
                return true; // Güncellenecek veri yok
            }
            
            // Transaction başlat
            if (method_exists($this->db, 'beginTransaction')) {
                $this->db->beginTransaction();
            }
            
            try {
                // Ana ürün bilgilerini güncelle
                $result = $this->db->update('product_models', $update_data, ['id' => $product_id]);
                
                // Kategorileri güncelle
                if (isset($product_data['category_ids'])) {
                    $this->updateProductCategories($product_id, $product_data['category_ids']);
                }
                
                // Cinsiyetleri güncelle
                if (isset($product_data['gender_ids'])) {
                    $this->updateProductGenders($product_id, $product_data['gender_ids']);
                }
                
                // Transaction commit
                if (method_exists($this->db, 'commit')) {
                    $this->db->commit();
                }
                
                return $result !== false;
                
            } catch (Exception $e) {
                // Transaction rollback
                if (method_exists($this->db, 'rollback')) {
                    $this->db->rollback();
                }
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Ürün güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürüne kategoriler ekle
     * 
     * @param int $product_id Ürün ID
     * @param array $category_ids Kategori ID'leri
     * @return bool Başarı durumu
     */
    private function addProductCategories($product_id, $category_ids) {
        try {
            foreach ($category_ids as $category_id) {
                $this->db->insert('product_categories', [
                    'product_id' => $product_id,
                    'category_id' => intval($category_id)
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Ürün kategorileri ekleme hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ürüne cinsiyetler ekle
     * 
     * @param int $product_id Ürün ID
     * @param array $gender_ids Cinsiyet ID'leri
     * @return bool Başarı durumu
     */
    private function addProductGenders($product_id, $gender_ids) {
        try {
            foreach ($gender_ids as $gender_id) {
                $this->db->insert('product_genders', [
                    'product_id' => $product_id,
                    'gender_id' => intval($gender_id)
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Ürün cinsiyetleri ekleme hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ürüne görseller ekle
     * 
     * @param int $product_id Ürün ID
     * @param array $images Görsel verileri
     * @return bool Başarı durumu
     */
    private function addProductImages($product_id, $images) {
        try {
            foreach ($images as $index => $image) {
                $this->db->insert('product_images', [
                    'model_id' => $product_id,
                    'image_url' => $image['url'],
                    'is_primary' => ($index === 0) ? 1 : 0, // İlk resim birincil
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Ürün görselleri ekleme hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ürün kategorilerini güncelle
     * 
     * @param int $product_id Ürün ID
     * @param array $category_ids Yeni kategori ID'leri
     * @return bool Başarı durumu
     */
    private function updateProductCategories($product_id, $category_ids) {
        try {
            // Eski kategorileri sil
            $this->db->delete('product_categories', ['product_id' => $product_id]);
            
            // Yeni kategorileri ekle
            if (!empty($category_ids)) {
                $this->addProductCategories($product_id, $category_ids);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Ürün kategorileri güncelleme hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Ürün cinsiyetlerini güncelle
     * 
     * @param int $product_id Ürün ID
     * @param array $gender_ids Yeni cinsiyet ID'leri
     * @return bool Başarı durumu
     */
    private function updateProductGenders($product_id, $gender_ids) {
        try {
            // Eski cinsiyetleri sil
            $this->db->delete('product_genders', ['product_id' => $product_id]);
            
            // Yeni cinsiyetleri ekle
            if (!empty($gender_ids)) {
                $this->addProductGenders($product_id, $gender_ids);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Ürün cinsiyetleri güncelleme hatası: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Toplu ürün silme metodu
     * 
     * @param array $product_ids Ürün ID'leri
     * @return array Başarı ve hata bilgileri
     */
    public function deleteMultipleProducts($product_ids) {
        $results = [
            'success' => [],
            'failed' => []
        ];
        
        foreach ($product_ids as $product_id) {
            if ($this->deleteProduct($product_id)) {
                $results['success'][] = $product_id;
            } else {
                $results['failed'][] = $product_id;
            }
        }
        
        return $results;
    }
    
    /**
     * Toplu durum güncelleme metodu
     * 
     * @param array $product_ids Ürün ID'leri
     * @param bool $is_featured Öne çıkan durumu
     * @return array Başarı ve hata bilgileri
     */
    public function updateMultipleProductStatus($product_ids, $is_featured) {
        $results = [
            'success' => [],
            'failed' => []
        ];
        
        foreach ($product_ids as $product_id) {
            if ($this->updateProductStatus($product_id, $is_featured)) {
                $results['success'][] = $product_id;
            } else {
                $results['failed'][] = $product_id;
            }
        }
        
        return $results;
    }
    
    /**
     * Ürün varyantı ekleme metodu
     * 
     * @param int $model_id Ürün model ID
     * @param array $variant_data Varyant verileri
     * @return int|false Yeni varyant ID'si veya false
     */
    public function addProductVariant($model_id, $variant_data) {
        try {
            
            $insert_data = [
                'model_id' => intval($model_id),
                'size' => $variant_data['size'] ?? '',
                'color' => $variant_data['color'] ?? '',
                'stock_quantity' => intval($variant_data['stock_quantity'] ?? 0),
                'sku' => $variant_data['sku'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->db->insert('product_variants', $insert_data);
            
            // Insert sonucu array ise ID'yi çıkar, değilse direkt döndür
            if (is_array($result)) {
                return isset($result['id']) ? $result['id'] : (isset($result[0]['id']) ? $result[0]['id'] : false);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Ürün varyantı ekleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürün varyantı güncelleme metodu
     * 
     * @param int $variant_id Varyant ID
     * @param array $variant_data Varyant verileri
     * @return bool Başarı durumu
     */
    public function updateProductVariant($variant_id, $variant_data) {
        try {
            $update_data = [];
            
            if (isset($variant_data['size'])) {
                $update_data['size'] = $variant_data['size'];
            }
            
            if (isset($variant_data['color'])) {
                $update_data['color'] = $variant_data['color'];
            }
            
            
            if (isset($variant_data['stock_quantity'])) {
                $update_data['stock_quantity'] = intval($variant_data['stock_quantity']);
            }
            
            if (isset($variant_data['sku'])) {
                $update_data['sku'] = $variant_data['sku'];
            }
            
            if (empty($update_data)) {
                return true;
            }
            
            $result = $this->db->update('product_variants', $update_data, ['id' => intval($variant_id)]);
            
            return $result !== false;
            
        } catch (Exception $e) {
            error_log("Ürün varyantı güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürün varyantı silme metodu
     * 
     * @param int $variant_id Varyant ID
     * @return bool Başarı durumu
     */
    public function deleteProductVariant($variant_id) {
        try {
            $result = $this->db->delete('product_variants', ['id' => intval($variant_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Ürün varyantı silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * OTOMATIK CACHE INVALIDATION SISTEMI
     * Ürün değişikliklerinde ilgili cache'leri otomatik temizler
     * 
     * @param int $product_id Değişen ürün ID'si
     */
    private function invalidateProductCaches($product_id = null) {
        try {
            // Admin product listelerini temizle
            autoCache()->autoInvalidate('admin_products_*');
            
            // Eğer belirli bir ürün ID'si varsa, o ürüne özel cache'leri de temizle
            if ($product_id) {
                autoCache()->autoInvalidate("product_detail_{$product_id}_*");
                autoCache()->autoInvalidate("product_variants_{$product_id}");
                autoCache()->autoInvalidate("product_images_{$product_id}");
            }
            
            // Genel ürün cache'lerini temizle
            autoCache()->autoInvalidate('product_stats_*');
            autoCache()->autoInvalidate('category_product_counts_*');
            autoCache()->autoInvalidate('recent_products_*');
            
            // API cache'lerini temizle
            autoCache()->autoInvalidate('api_products_*');
            autoCache()->autoInvalidate('products_for_api_*');
            
            error_log("Cache invalidation tamamlandı - Product ID: " . ($product_id ?: 'ALL'));
            
        } catch (Exception $e) {
            error_log("Cache invalidation hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Tüm ürün cache'lerini temizle (manuel kullanım için)
     */
    public function clearAllProductCaches() {
        $this->invalidateProductCaches();
    }
}

// Singleton örneği
function product_management_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new ProductManagementService();
    }
    
    return $instance;
}
