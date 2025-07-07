<?php
/**
 * Cinsiyet Servisi
 * 
 * Bu dosya, cinsiyet verilerine erişim sağlayan servisi içerir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/SupabaseClient.php';

/**
 * Cinsiyet servisi
 * 
 * Cinsiyetlerle ilgili tüm veritabanı işlemlerini içerir
 */
class GenderService {
    private $supabase;
    
    /**
     * GenderService sınıfını başlatır
     */
    public function __construct() {
        $this->supabase = supabase();
    }
    
    /**
     * Tüm cinsiyetleri getiren metod
     * 
     * @return array Cinsiyetler
     */
    public function getAllGenders() {
        try {
            $query = 'genders?select=*&order=name.asc';
            
            $response = $this->supabase->request($query);
            return $response['body'] ?? [];
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
            $query = [
                'select' => '*',
                'slug' => 'eq.' . $slug,
                'limit' => 1
            ];
            
            $result = $this->supabase->request('/rest/v1/genders?' . http_build_query($query));
            
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
            $response = $this->supabase->request('genders?id=eq.' . intval($gender_id) . '&limit=1');
            $result = $response['body'] ?? [];
            
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
            $query = 'product_genders?select=gender_id,genders(id,name,slug)&product_id=eq.' . intval($product_id);
            $response = $this->supabase->request($query);
            $gender_relations = $response['body'] ?? [];
            
            $genders = [];
            foreach ($gender_relations as $relation) {
                if (isset($relation['genders'])) {
                    $genders[] = $relation['genders'];
                }
            }
            
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
            $query = 'product_genders?select=gender_id&product_id=eq.' . intval($product_id);
            $response = $this->supabase->request($query);
            $gender_relations = $response['body'] ?? [];
            
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
            $delete_response = $this->supabase->request('product_genders?product_id=eq.' . intval($product_id), 'DELETE');
            
            // Yeni ilişkileri ekle
            foreach ($gender_ids as $gender_id) {
                $data = [
                    'product_id' => intval($product_id),
                    'gender_id' => intval($gender_id)
                ];
                
                $this->supabase->request('product_genders', 'POST', $data);
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
            $response = $this->supabase->request('genders', 'POST', $data);
            
            // Response'da body varsa ve boş değilse başarılı
            if (isset($response['body']) && !empty($response['body'])) {
                return true;
            }
            
            return false;
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
            $response = $this->supabase->request('genders?id=eq.' . intval($gender_id), 'PATCH', $data);
            return !empty($response);
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
            $products_response = $this->supabase->request('product_genders?select=product_id&gender_id=eq.' . intval($gender_id) . '&limit=1');
            $products = $products_response['body'] ?? [];
            
            if (!empty($products)) {
                return false; // Cinsiyete ait ürün varsa silinemez
            }
            
            $response = $this->supabase->request('genders?id=eq.' . intval($gender_id), 'DELETE');
            return !empty($response);
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
