<?php
/**
 * Varyant Servisi
 * 
 * Bu dosya, ürün varyantlarına (renk/beden kombinasyonları) erişim sağlayan servisi içerir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/SupabaseClient.php';

/**
 * Varyant servis sınıfı
 * 
 * Ürün varyantlarıyla ilgili tüm veritabanı işlemlerini içerir
 */
class VariantService {
    private $supabase;
    
    /**
     * VariantService sınıfını başlatır
     */
    public function __construct() {
        $this->supabase = supabase();
    }
    
    /**
     * Tüm renkleri getiren metod
     * 
     * @return array Renkler
     */
    public function getAllColors() {
        try {
            $response = $this->supabase->request('colors?select=*&order=name.asc');
            return $response['body'] ?? [];
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
            $response = $this->supabase->request('sizes?select=*&order=size_value.asc');
            return $response['body'] ?? [];
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
            // REST API ile varyantları çek
            $variants_query = [
                'select' => '*',
                'model_id' => 'eq.' . $model_id,
                'order' => 'id.asc'
            ];
            
            $variants_response = $this->supabase->request('product_variants?' . http_build_query($variants_query));
            $variants = $variants_response['body'] ?? [];
            
            if (empty($variants)) {
                return [];
            }
            
            // Her varyant için renk ve beden bilgilerini ekle
            foreach ($variants as &$variant) {
                // Renk bilgisi
                if ($variant['color_id']) {
                    $color_query = [
                        'select' => 'id,name,hex_code',
                        'id' => 'eq.' . $variant['color_id'],
                        'limit' => 1
                    ];
                    $color_response = $this->supabase->request('colors?' . http_build_query($color_query));
                    $color_result = $color_response['body'] ?? [];
                    
                    if (!empty($color_result)) {
                        $variant['color_name'] = $color_result[0]['name'];
                        $variant['color_hex'] = $color_result[0]['hex_code'];
                    }
                }
                
                // Beden bilgisi
                if ($variant['size_id']) {
                    $size_query = [
                        'select' => 'id,size_value,size_type',
                        'id' => 'eq.' . $variant['size_id'],
                        'limit' => 1
                    ];
                    $size_response = $this->supabase->request('sizes?' . http_build_query($size_query));
                    $size_result = $size_response['body'] ?? [];
                    
                    if (!empty($size_result)) {
                        $variant['size_value'] = $size_result[0]['size_value'];
                        $variant['size_type'] = $size_result[0]['size_type'];
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
     * @return bool Başarı durumu
     */
    public function createVariant($data) {
        try {
            // SKU otomatik oluştur (eğer belirtilmemişse)
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSKU($data['model_id'], $data['color_id'], $data['size_id']);
            }
            
            $response = $this->supabase->request('product_variants', 'POST', $data);
            return !empty($response['body']);
        } catch (Exception $e) {
            error_log("Varyant oluşturma hatası: " . $e->getMessage());
            return false;
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
            $response = $this->supabase->request('product_variants?id=eq.' . intval($variant_id), 'PATCH', $data);
            return !empty($response);
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
            $response = $this->supabase->request('product_variants?id=eq.' . intval($variant_id), 'DELETE');
            return !empty($response);
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
            // REST API ile varyantı çek
            $variant_query = [
                'select' => '*',
                'id' => 'eq.' . $variant_id,
                'limit' => 1
            ];
            
            $variant_response = $this->supabase->request('product_variants?' . http_build_query($variant_query));
            $variant_result = $variant_response['body'] ?? [];
            
            if (empty($variant_result)) {
                return null;
            }
            
            $variant = $variant_result[0];
            
            // Renk bilgisi ekle
            if ($variant['color_id']) {
                $color_query = [
                    'select' => 'name,hex_code',
                    'id' => 'eq.' . $variant['color_id'],
                    'limit' => 1
                ];
                $color_response = $this->supabase->request('colors?' . http_build_query($color_query));
                $color_result = $color_response['body'] ?? [];
                
                if (!empty($color_result)) {
                    $variant['color_name'] = $color_result[0]['name'];
                    $variant['color_hex'] = $color_result[0]['hex_code'];
                }
            }
            
            // Beden bilgisi ekle
            if ($variant['size_id']) {
                $size_query = [
                    'select' => 'size_value,size_type',
                    'id' => 'eq.' . $variant['size_id'],
                    'limit' => 1
                ];
                $size_response = $this->supabase->request('sizes?' . http_build_query($size_query));
                $size_result = $size_response['body'] ?? [];
                
                if (!empty($size_result)) {
                    $variant['size_value'] = $size_result[0]['size_value'];
                    $variant['size_type'] = $size_result[0]['size_type'];
                }
            }
            
            return $variant;
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
            // REST API ile aktif varyantları çek
            $variants_query = [
                'select' => 'stock_quantity',
                'model_id' => 'eq.' . $model_id,
                'is_active' => 'eq.true'
            ];
            
            $variants_response = $this->supabase->request('product_variants?' . http_build_query($variants_query));
            $variants = $variants_response['body'] ?? [];
            
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
            // Aktif varyantları çek
            $variants_query = [
                'select' => 'color_id',
                'model_id' => 'eq.' . $model_id,
                'is_active' => 'eq.true'
            ];
            
            $variants_response = $this->supabase->request('product_variants?' . http_build_query($variants_query));
            $variants = $variants_response['body'] ?? [];
            
            $color_ids = array_unique(array_column($variants, 'color_id'));
            $colors = [];
            
            foreach ($color_ids as $color_id) {
                if (!$color_id) continue;
                
                $color_query = [
                    'select' => 'id,name,hex_code',
                    'id' => 'eq.' . $color_id,
                    'limit' => 1
                ];
                
                $color_response = $this->supabase->request('colors?' . http_build_query($color_query));
                $color_result = $color_response['body'] ?? [];
                
                if (!empty($color_result)) {
                    $colors[] = $color_result[0];
                }
            }
            
            // İsme göre sırala
            usort($colors, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            
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
            // Aktif varyantları çek
            $variants_query = [
                'select' => 'size_id',
                'model_id' => 'eq.' . $model_id,
                'is_active' => 'eq.true'
            ];
            
            $variants_response = $this->supabase->request('product_variants?' . http_build_query($variants_query));
            $variants = $variants_response['body'] ?? [];
            
            $size_ids = array_unique(array_column($variants, 'size_id'));
            $sizes = [];
            
            foreach ($size_ids as $size_id) {
                if (!$size_id) continue;
                
                $size_query = [
                    'select' => 'id,size_value,size_type',
                    'id' => 'eq.' . $size_id,
                    'limit' => 1
                ];
                
                $size_response = $this->supabase->request('sizes?' . http_build_query($size_query));
                $size_result = $size_response['body'] ?? [];
                
                if (!empty($size_result)) {
                    $sizes[] = $size_result[0];
                }
            }
            
            // Beden değerine göre sırala
            usort($sizes, function($a, $b) {
                return intval($a['size_value']) - intval($b['size_value']);
            });
            
            return $sizes;
        } catch (Exception $e) {
            error_log("Ürün bedenleri getirme hatası: " . $e->getMessage());
            return [];
        }
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
