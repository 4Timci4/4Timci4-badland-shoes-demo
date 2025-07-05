<?php
/**
 * Özellik Servisi (Renkler & Bedenler)
 * 
 * Bu dosya, ürün özelliklerine (renk, beden) erişim sağlayan servisi içerir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/SupabaseClient.php';

/**
 * Özellik servis sınıfı
 * 
 * Renkler ve bedenlerle ilgili tüm veritabanı işlemlerini içerir
 */
class AttributeService {
    private $supabase;
    
    /**
     * AttributeService sınıfını başlatır
     */
    public function __construct() {
        $this->supabase = supabase();
    }
    
    // =================== RENK YÖNETİMİ ===================
    
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
     * Yeni renk oluşturma metodu
     * 
     * @param array $data Renk verileri (name, hex_code)
     * @return bool Başarı durumu
     */
    public function createColor($data) {
        try {
            $response = $this->supabase->request('colors', 'POST', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Renk oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Renk güncelleme metodu
     * 
     * @param int $color_id Renk ID
     * @param array $data Güncellenecek veriler
     * @return bool Başarı durumu
     */
    public function updateColor($color_id, $data) {
        try {
            $response = $this->supabase->request('colors?id=eq.' . intval($color_id), 'PATCH', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Renk güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Renk silme metodu
     * 
     * @param int $color_id Renk ID
     * @return bool Başarı durumu
     */
    public function deleteColor($color_id) {
        try {
            // Bu rengi kullanan varyant var mı kontrol et
            $variants_response = $this->supabase->request('product_variants?select=id&color_id=eq.' . intval($color_id) . '&limit=1');
            $variants = $variants_response['body'] ?? [];
            
            if (!empty($variants)) {
                return false; // Rengi kullanan varyant varsa silinemez
            }
            
            $response = $this->supabase->request('colors?id=eq.' . intval($color_id), 'DELETE');
            return !empty($response);
        } catch (Exception $e) {
            error_log("Renk silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Renk ID'ye göre renk getirme metodu
     * 
     * @param int $color_id Renk ID
     * @return array|null Renk veya bulunamazsa boş dizi
     */
    public function getColorById($color_id) {
        try {
            $response = $this->supabase->request('colors?id=eq.' . intval($color_id) . '&limit=1');
            $result = $response['body'] ?? [];
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Renk getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    // =================== BEDEN YÖNETİMİ ===================
    
    /**
     * Tüm bedenleri getiren metod (sıralı)
     * 
     * @return array Bedenler
     */
    public function getAllSizes() {
        try {
            $response = $this->supabase->request('sizes?select=*&order=id.asc');
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Bedenleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Yeni beden oluşturma metodu
     * 
     * @param array $data Beden verileri (name -> size_value)
     * @return bool Başarı durumu
     */
    public function createSize($data) {
        try {
            // name field'ını size_value'ya çevir
            if (isset($data['name'])) {
                $data['size_value'] = $data['name'];
                unset($data['name']);
            }
            
            // size_type default değeri
            if (!isset($data['size_type'])) {
                $data['size_type'] = 'EU';
            }
            
            $response = $this->supabase->request('sizes', 'POST', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Beden oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Beden güncelleme metodu
     * 
     * @param int $size_id Beden ID
     * @param array $data Güncellenecek veriler
     * @return bool Başarı durumu
     */
    public function updateSize($size_id, $data) {
        try {
            // name field'ını size_value'ya çevir
            if (isset($data['name'])) {
                $data['size_value'] = $data['name'];
                unset($data['name']);
            }
            
            $response = $this->supabase->request('sizes?id=eq.' . intval($size_id), 'PATCH', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Beden güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Beden silme metodu
     * 
     * @param int $size_id Beden ID
     * @return bool Başarı durumu
     */
    public function deleteSize($size_id) {
        try {
            // Bu bedeni kullanan varyant var mı kontrol et
            $variants_response = $this->supabase->request('product_variants?select=id&size_id=eq.' . intval($size_id) . '&limit=1');
            $variants = $variants_response['body'] ?? [];
            
            if (!empty($variants)) {
                return false; // Bedeni kullanan varyant varsa silinemez
            }
            
            $response = $this->supabase->request('sizes?id=eq.' . intval($size_id), 'DELETE');
            return !empty($response);
        } catch (Exception $e) {
            error_log("Beden silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Beden ID'ye göre beden getirme metodu
     * 
     * @param int $size_id Beden ID
     * @return array|null Beden veya bulunamazsa boş dizi
     */
    public function getSizeById($size_id) {
        try {
            $response = $this->supabase->request('sizes?id=eq.' . intval($size_id) . '&limit=1');
            $result = $response['body'] ?? [];
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Beden getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Beden sıralama güncelleme metodu
     * 
     * @param array $order_data Array of [id => order] pairs
     * @return bool Başarı durumu
     */
    public function updateSizeOrder($order_data) {
        try {
            foreach ($order_data as $size_id => $order) {
                $this->supabase->request('sizes?id=eq.' . intval($size_id), 'PATCH', [
                    'display_order' => intval($order)
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Beden sıralama güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    // =================== YARDIMCI METODLAR ===================
    
    /**
     * Renk kullanım sayısını getir
     * 
     * @param int $color_id Renk ID
     * @return int Kullanım sayısı
     */
    public function getColorUsageCount($color_id) {
        try {
            $response = $this->supabase->request('product_variants?select=id&color_id=eq.' . intval($color_id));
            return count($response['body'] ?? []);
        } catch (Exception $e) {
            error_log("Renk kullanım sayısı getirme hatası: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Beden kullanım sayısını getir
     * 
     * @param int $size_id Beden ID
     * @return int Kullanım sayısı
     */
    public function getSizeUsageCount($size_id) {
        try {
            $response = $this->supabase->request('product_variants?select=id&size_id=eq.' . intval($size_id));
            return count($response['body'] ?? []);
        } catch (Exception $e) {
            error_log("Beden kullanım sayısı getirme hatası: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Renkleri kullanım sayılarıyla getir
     * 
     * @return array Renkler ve kullanım sayıları
     */
    public function getColorsWithUsageCounts() {
        try {
            $colors = $this->getAllColors();
            
            foreach ($colors as &$color) {
                $color['usage_count'] = $this->getColorUsageCount($color['id']);
            }
            
            return $colors;
        } catch (Exception $e) {
            error_log("Renkler ve kullanım sayıları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bedenleri kullanım sayılarıyla getir
     * 
     * @return array Bedenler ve kullanım sayıları
     */
    public function getSizesWithUsageCounts() {
        try {
            $sizes = $this->getAllSizes();
            
            foreach ($sizes as &$size) {
                $size['usage_count'] = $this->getSizeUsageCount($size['id']);
            }
            
            return $sizes;
        } catch (Exception $e) {
            error_log("Bedenler ve kullanım sayıları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// AttributeService sınıfı singleton örneği
function attribute_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new AttributeService();
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
    return attribute_service()->getAllColors();
}

/**
 * Tüm bedenleri getiren fonksiyon
 * 
 * @return array Bedenler
 */
function get_all_sizes() {
    return attribute_service()->getAllSizes();
}

/**
 * Renk oluşturma fonksiyonu
 * 
 * @param array $data Renk verileri
 * @return bool Başarı durumu
 */
function create_color($data) {
    return attribute_service()->createColor($data);
}

/**
 * Beden oluşturma fonksiyonu
 * 
 * @param array $data Beden verileri
 * @return bool Başarı durumu
 */
function create_size($data) {
    return attribute_service()->createSize($data);
}

/**
 * Renk güncelleme fonksiyonu
 * 
 * @param int $color_id Renk ID
 * @param array $data Güncellenecek veriler
 * @return bool Başarı durumu
 */
function update_color($color_id, $data) {
    return attribute_service()->updateColor($color_id, $data);
}

/**
 * Beden güncelleme fonksiyonu
 * 
 * @param int $size_id Beden ID
 * @param array $data Güncellenecek veriler
 * @return bool Başarı durumu
 */
function update_size($size_id, $data) {
    return attribute_service()->updateSize($size_id, $data);
}

/**
 * Renk silme fonksiyonu
 * 
 * @param int $color_id Renk ID
 * @return bool Başarı durumu
 */
function delete_color($color_id) {
    return attribute_service()->deleteColor($color_id);
}

/**
 * Beden silme fonksiyonu
 * 
 * @param int $size_id Beden ID
 * @return bool Başarı durumu
 */
function delete_size($size_id) {
    return attribute_service()->deleteSize($size_id);
}
