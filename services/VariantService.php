<?php
/**
 * Varyant Servisi
 * 
 * Bu dosya, ürün varyantlarına (renk/beden kombinasyonları) erişim sağlayan servisi içerir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/DatabaseFactory.php';

/**
 * Varyant servis sınıfı
 * 
 * Ürün varyantlarıyla ilgili tüm veritabanı işlemlerini içerir
 */
class VariantService {
    private $db;
    
    /**
     * VariantService sınıfını başlatır
     */
    public function __construct() {
        $this->db = database();
    }
    
    /**
     * Tüm renkleri getiren metod
     * 
     * @return array Renkler
     */
    public function getAllColors() {
        try {
            return $this->db->select('colors', [], '*', ['order' => 'name ASC']);
        } catch (Exception $e) {
            error_log("Renkleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Tüm bedenleri getiren metod
     * 
     * @return array Bedenler
     */
    public function getAllSizes() {
        try {
            return $this->db->select('sizes', [], '*', ['order' => 'size_value ASC']);
        } catch (Exception $e) {
            error_log("Bedenleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Belirli bir ürün modeline ait varyantları getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün varyantları (renk ve beden bilgileriyle)
     */
    public function getProductVariants($model_id) {
        try {
            // Supabase için basit select kullan (JOIN işlemini manuel yapalım)
            $variants = $this->db->select('product_variants', 
                ['model_id' => intval($model_id)], 
                '*',
                ['order' => 'id ASC']);
            
            // Manual join işlemi
            foreach ($variants as &$variant) {
                // Renk bilgisini al
                if (!empty($variant['color_id'])) {
                    $colors = $this->db->select('colors', ['id' => $variant['color_id']], '*', ['limit' => 1]);
                    if (!empty($colors)) {
                        $variant['color_name'] = $colors[0]['name'];
                        $variant['color_hex'] = $colors[0]['hex_code'];
                    }
                }
                
                // Beden bilgisini al
                if (!empty($variant['size_id'])) {
                    $sizes = $this->db->select('sizes', ['id' => $variant['size_id']], '*', ['limit' => 1]);
                    if (!empty($sizes)) {
                        $variant['size_value'] = $sizes[0]['size_value'];
                        $variant['size_type'] = $sizes[0]['size_type'];
                    }
                }
            }
            
            return $variants;
        } catch (Exception $e) {
            error_log("Ürün varyantları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Yeni varyant oluşturma metodu
     * 
     * @param array $data Varyant verileri
     * @return bool|string Başarı durumu veya hata mesajı
     */
    public function createVariant($data) {
        try {
            // Varyant zaten var mı kontrol et
            if (!empty($data['model_id']) && !empty($data['color_id']) && !empty($data['size_id'])) {
                $existing = $this->db->select('product_variants', [
                    'model_id' => intval($data['model_id']),
                    'color_id' => intval($data['color_id']),
                    'size_id' => intval($data['size_id'])
                ], 'id', ['limit' => 1]);
                
                if (!empty($existing)) {
                    throw new Exception("Bu model, renk ve beden kombinasyonu zaten mevcut");
                }
            }
            
            // SKU otomatik oluştur (eğer belirtilmemişse)
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSKU($data['model_id'], $data['color_id'], $data['size_id']);
            }
            
            // Veriyi ekle ve yeni ID'yi geri al
            $new_variant_array = $this->db->insert('product_variants', $data, ['returning' => 'representation']);

            if ($new_variant_array && !empty($new_variant_array)) {
                $new_variant_id = $new_variant_array[0]['id'] ?? null;
                if ($new_variant_id) {
                    // Yeni eklenen varyantın tüm bilgilerini getir
                    return $this->getVariantById($new_variant_id);
                }
            }
            
            return false;

        } catch (Exception $e) {
            $error_message = $e->getMessage();
            
            // Duplicate key hatası için özel mesaj
            if (strpos($error_message, 'duplicate key') !== false || strpos($error_message, '23505') !== false) {
                throw new Exception("Bu model, renk ve beden kombinasyonu zaten mevcut");
            }
            
            error_log("Varyant oluşturma hatası: " . $error_message);
            throw $e; // Hataları yeniden fırlat
        }
    }
    
    /**
     * Varyant güncelleme metodu
     * 
     * @param int $variant_id Varyant ID
     * @param array $data Güncellenecek veriler
     * @return bool Başarı durumu
     */
    public function updateVariant($variant_id, $data) {
        try {
            $result = $this->db->update('product_variants', $data, ['id' => intval($variant_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Varyant güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Varyant silme metodu
     * 
     * @param int $variant_id Varyant ID
     * @return bool Başarı durumu
     */
    public function deleteVariant($variant_id) {
        try {
            $result = $this->db->delete('product_variants', ['id' => intval($variant_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Varyant silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toplu varyant oluşturma metodu
     * 
     * @param int $model_id Ürün model ID
     * @param array $variants Varyant dizisi
     * @return array Başarılı/başarısız sonuçlar
     */
    public function createBulkVariants($model_id, $variants) {
        $results = [];
        
        foreach ($variants as $variant) {
            $variant['model_id'] = $model_id;
            $result = $this->createVariant($variant);
            $results[] = [
                'variant' => $variant,
                'success' => $result
            ];
        }
        
        return $results;
    }
    
    /**
     * SKU otomatik oluşturma metodu
     * 
     * @param int $model_id Model ID
     * @param int $color_id Renk ID  
     * @param int $size_id Beden ID
     * @return string Oluşturulan SKU
     */
    private function generateSKU($model_id, $color_id, $size_id) {
        $timestamp = time();
        return "PRD{$model_id}-C{$color_id}-S{$size_id}-{$timestamp}";
    }
    
    /**
     * Stok güncelleme metodu
     * 
     * @param int $variant_id Varyant ID
     * @param int $quantity Yeni stok miktarı
     * @return bool Başarı durumu
     */
    public function updateStock($variant_id, $quantity) {
        try {
            $data = [
                'stock_quantity' => intval($quantity),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->updateVariant($variant_id, $data);
        } catch (Exception $e) {
            error_log("Stok güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Belirli bir varyantın bilgilerini getiren metod
     * 
     * @param int $variant_id Varyant ID
     * @return array|null Varyant bilgisi
     */
    public function getVariantById($variant_id) {
        try {
            // PostgREST'in "embedding" özelliğini kullanarak doğru select sorgusu oluştur
            $select_query = '*,colors(name,hex_code),sizes(size_value,size_type)';
            
            $variants = $this->db->select(
                'product_variants',
                ['id' => intval($variant_id)],
                $select_query,
                ['limit' => 1]
            );
            
            if (!empty($variants)) {
                $variant = $variants[0];
                // Gömülü verileri ana nesneye taşı
                if (isset($variant['colors'])) {
                    $variant['color_name'] = $variant['colors']['name'];
                    $variant['color_hex'] = $variant['colors']['hex_code'];
                    unset($variant['colors']);
                }
                if (isset($variant['sizes'])) {
                    $variant['size_value'] = $variant['sizes']['size_value'];
                    $variant['size_type'] = $variant['sizes']['size_type'];
                    unset($variant['sizes']);
                }
                return $variant;
            }

            return null;
        } catch (Exception $e) {
            error_log("Varyant getirme hatası: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Ürün modelinin toplam stok miktarını getiren metod
     * 
     * @param int $model_id Model ID
     * @return int Toplam stok
     */
    public function getTotalStock($model_id) {
        try {
            $variants = $this->db->select('product_variants', 
                ['model_id' => intval($model_id), 'is_active' => 1], 
                'stock_quantity'
            );
            
            $total_stock = 0;
            foreach ($variants as $variant) {
                $total_stock += intval($variant['stock_quantity'] ?? 0);
            }
            
            return $total_stock;
        } catch (Exception $e) {
            error_log("Toplam stok getirme hatası: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Bir ürün modelinin mevcut renklerini getiren metod
     * 
     * @param int $model_id Model ID
     * @return array Mevcut renkler
     */
    public function getProductColors($model_id) {
        try {
            $variants = $this->db->select('product_variants', 
                ['model_id' => intval($model_id), 'is_active' => 1], 
                'color_id'
            );
            
            $color_ids = array_unique(array_column($variants, 'color_id'));
            
            if (empty($color_ids)) {
                return [];
            }
            
            $colors = $this->db->select('colors', ['id' => ['IN', $color_ids]], '*', ['order' => 'name ASC']);
            
            return $colors;
        } catch (Exception $e) {
            error_log("Ürün renkleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bir ürün modelinin mevcut bedenlerini getiren metod
     * 
     * @param int $model_id Model ID
     * @return array Mevcut bedenler
     */
    public function getProductSizes($model_id) {
        try {
            $variants = $this->db->select('product_variants',
                ['model_id' => intval($model_id), 'is_active' => 1],
                'size_id'
            );
            
            $size_ids = array_unique(array_column($variants, 'size_id'));
            
            if (empty($size_ids)) {
                return [];
            }
            
            $sizes = $this->db->select('sizes', ['id' => ['IN', $size_ids]], '*', ['order' => 'size_value ASC']);
            
            return $sizes;
        } catch (Exception $e) {
            error_log("Ürün bedenleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Yeni varyant eklemek için bir alias metodu.
     * createVariant metodunu çağırır.
     */
    public function addVariant($model_id, $color_id, $size_id, $stock_quantity, $is_active) {
        $data = [
            'model_id' => $model_id,
            'color_id' => $color_id,
            'size_id' => $size_id,
            'stock_quantity' => $stock_quantity,
            'is_active' => $is_active
        ];
        return $this->createVariant($data);
    }
}

// VariantService sınıfı singleton örneği
function variant_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new VariantService();
    }
    
    return $instance;
}

// Geriye uyumluluk için fonksiyonlar
/**
 * Tüm renkleri getiren fonksiyon
 * 
 * @return array Renkler
 */
function get_all_colors() {
    return variant_service()->getAllColors();
}

/**
 * Tüm bedenleri getiren fonksiyon
 * 
 * @return array Bedenler
 */
function get_all_sizes() {
    return variant_service()->getAllSizes();
}

/**
 * Ürün varyantlarını getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array Ürün varyantları
 */
function get_product_variants_with_details($model_id) {
    return variant_service()->getProductVariants($model_id);
}

/**
 * Ürün toplam stok miktarını getiren fonksiyon
 * 
 * @param int $model_id Model ID
 * @return int Toplam stok
 */
function get_product_total_stock($model_id) {
    return variant_service()->getTotalStock($model_id);
}

/**
 * Ürün renklerini getiren fonksiyon
 * 
 * @param int $model_id Model ID
 * @return array Mevcut renkler
 */
function get_product_colors($model_id) {
    return variant_service()->getProductColors($model_id);
}

/**
 * Ürün bedenlerini getiren fonksiyon
 * 
 * @param int $model_id Model ID
 * @return array Mevcut bedenler
 */
function get_product_sizes($model_id) {
    return variant_service()->getProductSizes($model_id);
}
