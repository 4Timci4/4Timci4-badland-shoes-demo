<?php
/**
 * Cinsiyet Servisi
 * 
 * Bu dosya, cinsiyet verilerine erişim sağlayan servisi içerir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/DatabaseFactory.php';

/**
 * Cinsiyet servisi
 * 
 * Cinsiyetlerle ilgili tüm veritabanı işlemlerini içerir
 */
class GenderService {
    private $db;
    
    /**
     * GenderService sınıfını başlatır
     */
    public function __construct() {
        $this->db = database();
    }
    
    /**
     * Tüm cinsiyetleri getiren metod
     * 
     * @return array Cinsiyetler
     */
    public function getAllGenders() {
        try {
            return $this->db->select('genders', [], '*', ['order' => 'id ASC']);
        } catch (Exception $e) {
            error_log("Tüm cinsiyetleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Belirli bir cinsiyeti slug'a göre getiren metod
     * 
     * @param string $slug Cinsiyet slug'ı
     * @return array|null Cinsiyet veya bulunamazsa boş dizi
     */
    public function getGenderBySlug($slug) {
        try {
            $result = $this->db->select('genders', ['slug' => $slug], '*', ['limit' => 1]);
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Cinsiyet getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cinsiyet ID'ye göre cinsiyet getirme metodu
     * 
     * @param int $gender_id Cinsiyet ID
     * @return array|null Cinsiyet veya bulunamazsa boş dizi
     */
    public function getGenderById($gender_id) {
        try {
            $result = $this->db->select('genders', ['id' => intval($gender_id)], '*', ['limit' => 1]);
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Cinsiyet getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ürün ID'sine göre cinsiyet getirme metodu
     * 
     * @param int $product_id Ürün ID
     * @return array Cinsiyet bilgileri
     */
    public function getProductGenders($product_id) {
        try {
            $genders = $this->db->selectWithJoins('product_genders', [
                [
                    'type' => 'INNER',
                    'table' => 'genders',
                    'condition' => 'product_genders.gender_id = genders.id'
                ]
            ], ['product_genders.product_id' => intval($product_id)], 'genders.id, genders.name, genders.slug');
            
            return $genders;
        } catch (Exception $e) {
            error_log("Ürün cinsiyetleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ürün ID'sine göre cinsiyet ID'lerini getirme metodu
     * 
     * @param int $product_id Ürün ID
     * @return array Cinsiyet ID'leri
     */
    public function getProductGenderIds($product_id) {
        try {
            $gender_relations = $this->db->select('product_genders', ['product_id' => intval($product_id)], 'gender_id');
            return array_column($gender_relations, 'gender_id');
        } catch (Exception $e) {
            error_log("Ürün cinsiyet ID'leri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ürüne cinsiyet ekleme/güncelleme metodu
     * 
     * @param int $product_id Ürün ID
     * @param array $gender_ids Cinsiyet ID'leri
     * @return bool Başarı durumu
     */
    public function updateProductGenders($product_id, $gender_ids) {
        try {
            // Önce eski ilişkileri sil
            $this->db->delete('product_genders', ['product_id' => intval($product_id)]);
            
            // Yeni ilişkileri ekle
            foreach ($gender_ids as $gender_id) {
                $data = [
                    'product_id' => intval($product_id),
                    'gender_id' => intval($gender_id)
                ];
                
                $this->db->insert('product_genders', $data);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Ürün cinsiyetleri güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Yeni cinsiyet oluşturma metodu
     * 
     * @param array $data Cinsiyet verileri
     * @return bool Başarı durumu
     */
    public function createGender($data) {
        try {
            $result = $this->db->insert('genders', $data, ['returning' => true]);
            return !empty($result);
        } catch (Exception $e) {
            error_log("GenderService::createGender - Exception: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cinsiyet güncelleme metodu
     * 
     * @param int $gender_id Cinsiyet ID
     * @param array $data Güncellenecek veriler
     * @return bool Başarı durumu
     */
    public function updateGender($gender_id, $data) {
        try {
            $result = $this->db->update('genders', $data, ['id' => intval($gender_id)]);
            return !empty($result);
        } catch (Exception $e) {
            error_log("Cinsiyet güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cinsiyet silme metodu
     * 
     * @param int $gender_id Cinsiyet ID
     * @return bool Başarı durumu
     */
    public function deleteGender($gender_id) {
        try {
            // Önce bu cinsiyete ait ürün var mı kontrol et
            $product_count = $this->db->count('product_genders', ['gender_id' => intval($gender_id)]);
            
            if ($product_count > 0) {
                return false; // Cinsiyete ait ürün varsa silinemez
            }
            
            $result = $this->db->delete('genders', ['id' => intval($gender_id)]);
            return !empty($result);
        } catch (Exception $e) {
            error_log("Cinsiyet silme hatası: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Slug oluşturma metodu
     * 
     * @param string $text Dönüştürülecek metin
     * @return string Slug
     */
    public function generateSlug($text) {
        // Türkçe karakterleri dönüştür
        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'I', 'O', 'S', 'U'];
        $text = str_replace($turkish, $english, $text);
        
        // Küçük harfe dönüştür ve sadece alfanumerik karakterleri bırak
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
}

// GenderService sınıfı singleton örneği
function gender_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new GenderService();
    }
    
    return $instance;
}

// Geriye uyumluluk için fonksiyonlar
/**
 * Tüm cinsiyetleri getiren fonksiyon
 * 
 * @return array Cinsiyetler
 */
function get_genders() {
    return gender_service()->getAllGenders();
}

/**
 * Belirli bir cinsiyeti slug'a göre getiren fonksiyon
 * 
 * @param string $slug Cinsiyet slug'ı
 * @return array|null Cinsiyet veya bulunamazsa null
 */
function get_gender_by_slug($slug) {
    return gender_service()->getGenderBySlug($slug);
}

/**
 * Ürün ID'sine göre cinsiyet getiren fonksiyon
 * 
 * @param int $product_id Ürün ID
 * @return array Cinsiyet bilgileri
 */
function get_product_genders($product_id) {
    return gender_service()->getProductGenders($product_id);
}
