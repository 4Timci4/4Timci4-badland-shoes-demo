<?php
/**
 * Product Image Service
 * 
 * Ürün resimlerinin veritabanı ve dosya sisteminde yönetilmesi
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/../../lib/ImageManager.php';

class ProductImageService {
    private $db;
    private $imageManager;
    
    public function __construct() {
        $this->db = database();
        $this->imageManager = ImageManager::getInstance();
    }
    
    /**
     * Ürün için resim yükle
     * 
     * @param int $model_id Ürün model ID'si
     * @param int|null $color_id Renk ID (opsiyonel)
     * @param array $files Upload edilen dosyalar ($_FILES formatında)
     * @param array $options Upload seçenekleri
     * @return array Sonuç ve yüklenen resim bilgileri
     */
    public function uploadProductImages($model_id, $color_id = null, $files = [], $options = []) {
        $results = [];
        $errors = [];
        
        try {
            // Model varlığını kontrol et
            $model = $this->db->select('product_models', ['id' => intval($model_id)], '*', ['limit' => 1]);
            if (empty($model)) {
                return ['success' => false, 'errors' => ['Geçersiz ürün modeli.']];
            }
            
            // Renk kontrolü (eğer belirtilmişse)
            if ($color_id !== null) {
                $color = $this->db->select('colors', ['id' => intval($color_id)], '*', ['limit' => 1]);
                if (empty($color)) {
                    return ['success' => false, 'errors' => ['Geçersiz renk seçimi.']];
                }
            }
            
            // Çoklu dosya yükleme desteği
            if (isset($files['name']) && is_array($files['name'])) {
                // Çoklu dosya formatını tek dosya formatına dönüştür
                $file_list = [];
                $file_count = count($files['name']);
                
                for ($i = 0; $i < $file_count; $i++) {
                    if (!empty($files['name'][$i])) {
                        $file_list[] = [
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error' => $files['error'][$i],
                            'size' => $files['size'][$i]
                        ];
                    }
                }
            } elseif (isset($files['name']) && !is_array($files['name'])) {
                // Tek dosya
                $file_list = [$files];
            } else {
                return ['success' => false, 'errors' => ['Yüklenecek dosya bulunamadı.']];
            }
            
            // Mevcut resim sayısını kontrol et
            $existing_count = $this->db->count('product_images', [
                'model_id' => $model_id,
                'color_id' => $color_id
            ]);
            
            $max_images = $options['max_images'] ?? 10;
            if ($existing_count + count($file_list) > $max_images) {
                return ['success' => false, 'errors' => ["Maksimum {$max_images} resim yükleyebilirsiniz."]];
            }
            
            foreach ($file_list as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = "Dosya yüklenirken hata: " . $file['name'];
                    continue;
                }
                
                // Prefix oluştur
                $prefix = "product_{$model_id}";
                if ($color_id) {
                    $prefix .= "_color_{$color_id}";
                }
                $prefix .= "_";
                
                // Resim yükle ve optimize et
                $upload_result = $this->imageManager->uploadAndOptimize($file, array_merge([
                    'prefix' => $prefix,
                    'generate_thumbnail' => true,
                    'generate_webp' => true,
                    'max_width' => 1200,
                    'max_height' => 1200,
                    'quality' => 90
                ], $options));
                
                if ($upload_result['success']) {
                    // Veritabanına kaydet
                    $image_data = [
                        'model_id' => $model_id,
                        'color_id' => $color_id,
                        'image_url' => $upload_result['optimized']['url'],
                        'original_url' => $upload_result['original']['url'],
                        'thumbnail_url' => $upload_result['thumbnail']['url'] ?? null,
                        'webp_url' => $upload_result['webp']['url'] ?? null,
                        'alt_text' => $this->generateAltText($model_id, $color_id),
                        'file_size' => $upload_result['optimized']['size'],
                        'width' => $upload_result['optimized']['width'],
                        'height' => $upload_result['optimized']['height'],
                        'sort_order' => $this->getNextSortOrder($model_id, $color_id),
                        'is_primary' => $existing_count === 0 // İlk resim otomatik primary
                    ];
                    
                    $db_result = $this->db->insert('product_images', $image_data, ['returning' => true]);
                    
                    if (!empty($db_result)) {
                        $results[] = array_merge($upload_result, [
                            'db_id' => is_array($db_result) && isset($db_result[0]['id']) ? $db_result[0]['id'] : null,
                            'image_data' => $image_data
                        ]);
                    } else {
                        $errors[] = "Veritabanına kayıt hatası: " . $file['name'];
                        // Yüklenen dosyaları temizle
                        $this->cleanupUploadedFiles($upload_result);
                    }
                } else {
                    $errors = array_merge($errors, $upload_result['errors'] ?? ['Bilinmeyen resim yükleme hatası']);
                }
            }
            
            return [
                'success' => !empty($results),
                'results' => $results,
                'errors' => $errors,
                'uploaded_count' => count($results),
                'error_count' => count($errors)
            ];
            
        } catch (Exception $e) {
            error_log("ProductImageService::uploadProductImages - " . $e->getMessage());
            return ['success' => false, 'errors' => ['Resim yükleme sırasında hata oluştu.']];
        }
    }
    
    /**
     * Ürün resimlerini getir
     * 
     * @param int $model_id Ürün model ID'si
     * @param int|null $color_id Renk ID (opsiyonel)
     * @param bool $primary_only Sadece primary resmi getir
     * @return array Ürün resimleri
     */
    public function getProductImages($model_id, $color_id = null, $primary_only = false) {
        try {
            $conditions = ['model_id' => intval($model_id)];
            
            if ($color_id !== null) {
                $conditions['color_id'] = intval($color_id);
            }
            
            if ($primary_only) {
                $conditions['is_primary'] = true;
            }
            
            $images = $this->db->select('product_images', $conditions, '*', [
                'order' => 'sort_order ASC, created_at ASC'
            ]);
            
            // Resim URL'lerini kontrol et ve eksik olanları temizle
            return array_filter($images, [$this, 'validateImageFiles']);
            
        } catch (Exception $e) {
            error_log("ProductImageService::getProductImages - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ürün için tüm renklerdeki resimleri getir (gruplu)
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Renk ID'sine göre gruplandırılmış resimler
     */
    public function getProductImagesByColors($model_id) {
        try {
            $images = $this->db->select('product_images', ['model_id' => intval($model_id)], '*', [
                'order' => 'color_id ASC, sort_order ASC, created_at ASC'
            ]);
            
            $grouped = [];
            foreach ($images as $image) {
                $color_key = $image['color_id'] ?? 'default';
                if (!isset($grouped[$color_key])) {
                    $grouped[$color_key] = [];
                }
                $grouped[$color_key][] = $image;
            }
            
            return $grouped;
            
        } catch (Exception $e) {
            error_log("ProductImageService::getProductImagesByColors - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ürün için tüm renklerdeki resimleri getir (gruplu) - Alias metod
     *
     * @param int $model_id Ürün model ID'si
     * @return array Renk ID'sine göre gruplandırılmış resimler
     */
    public function getProductImagesByColor($model_id) {
        return $this->getProductImagesByColors($model_id);
    }
    
    /**
     * Primary resmi ayarla
     * 
     * @param int $image_id Resim ID'si
     * @return bool Başarı durumu
     */
    public function setPrimaryImage($image_id) {
        try {
            // Mevcut resmi getir
            $image = $this->db->select('product_images', ['id' => intval($image_id)], '*', ['limit' => 1]);
            if (empty($image)) {
                return false;
            }
            
            $image = $image[0];
            
            // Önce aynı model ve renkteki tüm resimleri primary olmaktan çıkar
            $conditions = ['model_id' => $image['model_id']];
            if ($image['color_id']) {
                $conditions['color_id'] = $image['color_id'];
            }
            
            $this->db->update('product_images', ['is_primary' => false], $conditions);
            
            // Seçilen resmi primary yap
            return $this->db->update('product_images', ['is_primary' => true], ['id' => $image_id]);
            
        } catch (Exception $e) {
            error_log("ProductImageService::setPrimaryImage - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Resimleri yeniden sırala
     * 
     * @param array $order_data [['id' => 1, 'sort_order' => 1], ...]
     * @return bool Başarı durumu
     */
    public function reorderImages($order_data) {
        try {
            foreach ($order_data as $item) {
                if (isset($item['id']) && isset($item['sort_order'])) {
                    $this->db->update('product_images', 
                        ['sort_order' => intval($item['sort_order'])], 
                        ['id' => intval($item['id'])]
                    );
                }
            }
            return true;
            
        } catch (Exception $e) {
            error_log("ProductImageService::reorderImages - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Resim sil
     * 
     * @param int $image_id Resim ID'si
     * @return bool Başarı durumu
     */
    public function deleteImage($image_id) {
        try {
            // Resim bilgilerini getir
            $image = $this->db->select('product_images', ['id' => intval($image_id)], '*', ['limit' => 1]);
            if (empty($image)) {
                return false;
            }
            
            $image = $image[0];
            
            // Dosyaları sil
            $this->deleteImageFiles($image);
            
            // Veritabanından sil
            $result = $this->db->delete('product_images', ['id' => $image_id]);
            
            // Eğer primary resim silindiyse, başka bir resmi primary yap
            if ($image['is_primary']) {
                $this->ensurePrimaryImage($image['model_id'], $image['color_id']);
            }
            
            return !empty($result);
            
        } catch (Exception $e) {
            error_log("ProductImageService::deleteImage - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürün için tüm resimleri sil
     * 
     * @param int $model_id Ürün model ID'si
     * @param int|null $color_id Renk ID (opsiyonel)
     * @return bool Başarı durumu
     */
    public function deleteAllProductImages($model_id, $color_id = null) {
        try {
            $conditions = ['model_id' => intval($model_id)];
            if ($color_id !== null) {
                $conditions['color_id'] = intval($color_id);
            }
            
            // Önce resimleri getir
            $images = $this->db->select('product_images', $conditions);
            
            // Dosyaları sil
            foreach ($images as $image) {
                $this->deleteImageFiles($image);
            }
            
            // Veritabanından sil
            return $this->db->delete('product_images', $conditions);
            
        } catch (Exception $e) {
            error_log("ProductImageService::deleteAllProductImages - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Alt text oluştur
     */
    private function generateAltText($model_id, $color_id = null) {
        try {
            // Ürün adını getir
            $model = $this->db->select('product_models', ['id' => $model_id], 'name', ['limit' => 1]);
            $model_name = !empty($model) ? $model[0]['name'] : 'Ürün';
            
            $alt_text = $model_name;
            
            // Renk bilgisini ekle
            if ($color_id) {
                $color = $this->db->select('colors', ['id' => $color_id], 'name', ['limit' => 1]);
                if (!empty($color)) {
                    $alt_text .= ' - ' . $color[0]['name'];
                }
            }
            
            return $alt_text;
            
        } catch (Exception $e) {
            return 'Ürün Resmi';
        }
    }
    
    /**
     * Sonraki sıra numarasını getir
     */
    private function getNextSortOrder($model_id, $color_id = null) {
        try {
            $conditions = ['model_id' => $model_id];
            if ($color_id) {
                $conditions['color_id'] = $color_id;
            }
            
            $result = $this->db->executeRawSql(
                "SELECT MAX(sort_order) as max_order FROM product_images WHERE model_id = ? " . 
                ($color_id ? "AND color_id = ?" : ""),
                $color_id ? [$model_id, $color_id] : [$model_id]
            );
            
            if (!empty($result)) {
                return (intval($result[0]['max_order']) ?? 0) + 1;
            }
            
            return 1;
            
        } catch (Exception $e) {
            return 1;
        }
    }
    
    /**
     * Primary resim varlığını kontrol et ve gerekirse ata
     */
    private function ensurePrimaryImage($model_id, $color_id = null) {
        try {
            $conditions = ['model_id' => $model_id];
            if ($color_id) {
                $conditions['color_id'] = $color_id;
            }
            
            // Primary resim var mı kontrol et
            $primary_exists = $this->db->count('product_images', array_merge($conditions, ['is_primary' => true]));
            
            if ($primary_exists === 0) {
                // İlk resmi primary yap
                $first_image = $this->db->select('product_images', $conditions, '*', [
                    'order' => 'sort_order ASC',
                    'limit' => 1
                ]);
                
                if (!empty($first_image)) {
                    $this->db->update('product_images', ['is_primary' => true], ['id' => $first_image[0]['id']]);
                }
            }
            
        } catch (Exception $e) {
            error_log("ProductImageService::ensurePrimaryImage - " . $e->getMessage());
        }
    }
    
    /**
     * Resim dosyalarını sil
     */
    private function deleteImageFiles($image) {
        try {
            // URL'den dosya yolunu çıkar
            $paths_to_delete = [];
            
            if ($image['image_url']) {
                $paths_to_delete[] = $_SERVER['DOCUMENT_ROOT'] . $image['image_url'];
            }
            if ($image['original_url']) {
                $paths_to_delete[] = $_SERVER['DOCUMENT_ROOT'] . $image['original_url'];
            }
            if ($image['thumbnail_url']) {
                $paths_to_delete[] = $_SERVER['DOCUMENT_ROOT'] . $image['thumbnail_url'];
            }
            if ($image['webp_url']) {
                $paths_to_delete[] = $_SERVER['DOCUMENT_ROOT'] . $image['webp_url'];
            }
            
            foreach ($paths_to_delete as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            
        } catch (Exception $e) {
            error_log("ProductImageService::deleteImageFiles - " . $e->getMessage());
        }
    }
    
    /**
     * Upload edilen dosyaları temizle (hata durumunda)
     */
    private function cleanupUploadedFiles($upload_result) {
        try {
            $paths_to_clean = [];
            
            if (isset($upload_result['original']['path'])) {
                $paths_to_clean[] = $upload_result['original']['path'];
            }
            if (isset($upload_result['optimized']['path'])) {
                $paths_to_clean[] = $upload_result['optimized']['path'];
            }
            if (isset($upload_result['thumbnail']['path'])) {
                $paths_to_clean[] = $upload_result['thumbnail']['path'];
            }
            if (isset($upload_result['webp']['path'])) {
                $paths_to_clean[] = $upload_result['webp']['path'];
            }
            
            foreach ($paths_to_clean as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            
        } catch (Exception $e) {
            error_log("ProductImageService::cleanupUploadedFiles - " . $e->getMessage());
        }
    }
    
    /**
     * Resim dosyalarının varlığını kontrol et
     */
    private function validateImageFiles($image) {
        $main_file = $_SERVER['DOCUMENT_ROOT'] . $image['image_url'];
        return file_exists($main_file);
    }
    
    /**
     * Responsive HTML oluştur
     * 
     * @param array $image Resim verisi
     * @param string $size Boyut ('thumbnail', 'medium', 'large')
     * @param array $attributes Ek HTML attributları
     * @return string HTML
     */
    public function generateImageHTML($image, $size = 'medium', $attributes = []) {
        $default_attributes = [
            'alt' => $image['alt_text'] ?? '',
            'loading' => 'lazy',
            'class' => 'product-image'
        ];
        
        $attributes = array_merge($default_attributes, $attributes);
        
        // Boyuta göre URL seç
        switch ($size) {
            case 'thumbnail':
                $src = $image['thumbnail_url'] ?: $image['image_url'];
                break;
            case 'large':
                $src = $image['original_url'] ?: $image['image_url'];
                break;
            default:
                $src = $image['image_url'];
        }
        
        $html = '<picture>';
        
        // WebP desteği
        if ($image['webp_url']) {
            $html .= '<source srcset="' . htmlspecialchars($image['webp_url']) . '" type="image/webp">';
        }
        
        $html .= '<img src="' . htmlspecialchars($src) . '"';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $html .= '>';
        
        $html .= '</picture>';
        
        return $html;
    }
}

// Global service instance
function productImageService() {
    static $instance = null;
    if ($instance === null) {
        $instance = new ProductImageService();
    }
    return $instance;
}