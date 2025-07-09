<?php
/**
 * Product Image Service
 * 
 * Ürün resimlerinin veritabanı ve dosya sisteminde yönetilmesi
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/../../lib/SupabaseImageManager.php';

class ProductImageService {
    private $db;
    private $imageManager;
    
    public function __construct() {
        $this->db = database();
        $this->imageManager = SupabaseImageManager::getInstance();
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
            $count_conditions = ['model_id' => intval($model_id)];
            if ($color_id !== null && $color_id !== '') {
                $count_conditions['color_id'] = intval($color_id);
            } else {
                $count_conditions['color_id'] = null;
            }
            
            $existing_count = $this->db->count('product_images', $count_conditions);
            
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
                
                // Resim yükle ve optimize et (Supabase Storage)
                $upload_result = $this->imageManager->uploadAndOptimize($file, array_merge([
                    'prefix' => $prefix,
                    'generate_thumbnail' => true,
                    'generate_webp' => true,
                    'max_width' => 1200,
                    'max_height' => 1200,
                    'quality' => 90
                ], $options));
                
                if ($upload_result['success']) {
                    // Image URL belirle - önce optimized, sonra original kontrol et
                    $image_url = null;
                    if (isset($upload_result['optimized']['url']) && !empty($upload_result['optimized']['url'])) {
                        $image_url = $upload_result['optimized']['url'];
                    } elseif (isset($upload_result['original']['url']) && !empty($upload_result['original']['url'])) {
                        $image_url = $upload_result['original']['url'];
                    }
                    
                    // URL varsa veritabanına kaydet
                    if ($image_url) {
                        $is_primary_image = ($existing_count === 0);

                        if ($is_primary_image) {
                             // Bu modele ait diğer tüm resimlerin "is_primary" işaretini kaldır
                            $this->db->update(
                                'product_images',
                                ['is_primary' => false],
                                ['model_id' => intval($model_id)]
                            );
                        }

                        $image_data = [
                            'model_id' => intval($model_id),
                            'color_id' => $color_id ? intval($color_id) : null,
                            'image_url' => $image_url,
                            'alt_text' => $this->generateAltText($model_id, $color_id),
                            'sort_order' => intval($this->getNextSortOrder($model_id, $color_id)),
                            'is_primary' => $is_primary_image
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
                        $errors[] = "Resim URL'si alınamadı: " . $file['name'];
                        error_log("Upload result but no URL: " . json_encode($upload_result));
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
            
            // Bu modele ait diğer tüm resimlerin "is_primary" işaretini kaldır
            $this->db->update(
                'product_images',
                ['is_primary' => false],
                ['model_id' => $image['model_id']]
            );
            
            // Seçilen resmi primary yap
            $result = $this->db->update('product_images', ['is_primary' => true], ['id' => $image_id]);
            return !empty($result);
            
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
            $result = $this->db->delete('product_images', $conditions);
            return !empty($result);
            
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
            // Conditions'ı güvenli şekilde hazırla
            $conditions = ['model_id' => intval($model_id)];
            
            if ($color_id !== null && $color_id !== '') {
                $conditions['color_id'] = intval($color_id);
            } else {
                // IS NULL koşulu için özel bir koşul ekliyoruz
                // NOT.is.null operator'ü ile tersini alarak
                $conditions['color_id'] = ['IS', null];
            }
            
            // Mevcut resimleri sıralı olarak al
            $options = [
                'order' => 'sort_order DESC',
                'limit' => 1
            ];
            
            $result = $this->db->select('product_images', $conditions, 'sort_order', $options);
            
            // Eğer sonuç varsa, en yüksek sort_order + 1 döndür
            if (!empty($result) && isset($result[0]['sort_order'])) {
                return intval($result[0]['sort_order']) + 1;
            }
            
            return 1;
            
        } catch (Exception $e) {
            error_log("ProductImageService::getNextSortOrder - " . $e->getMessage());
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
     * Resim dosyalarını sil (Supabase Storage'dan)
     */
    private function deleteImageFiles($image) {
        try {
            // Sadece mevcut image_url kolonunu kullan
            if (!empty($image['image_url'])) {
                $storage_path = $this->extractStoragePath($image['image_url']);
                if ($storage_path) {
                    $this->imageManager->deleteFromSupabase($storage_path);
                }
            }
            
        } catch (Exception $e) {
            error_log("ProductImageService::deleteImageFiles - " . $e->getMessage());
        }
    }
    
    /**
     * Supabase URL'den storage path çıkar
     */
    private function extractStoragePath($url) {
        // Supabase public URL formatı:
        // https://xxx.supabase.co/storage/v1/object/public/bucket-name/path/to/file.jpg
        if (strpos($url, '/storage/v1/object/public/') !== false) {
            $parts = explode('/storage/v1/object/public/', $url);
            if (count($parts) > 1) {
                // bucket-name/path/to/file.jpg formatından sadece path/to/file.jpg kısmını al
                $bucket_and_path = $parts[1];
                $path_parts = explode('/', $bucket_and_path, 2);
                return count($path_parts) > 1 ? $path_parts[1] : null;
            }
        }
        return null;
    }
    
    /**
     * Upload edilen dosyaları temizle (hata durumında - Supabase Storage'dan)
     */
    private function cleanupUploadedFiles($upload_result) {
        try {
            $storage_paths = [];
            
            if (isset($upload_result['original']['path'])) {
                $storage_paths[] = $upload_result['original']['path'];
            }
            if (isset($upload_result['optimized']['path'])) {
                $storage_paths[] = $upload_result['optimized']['path'];
            }
            if (isset($upload_result['thumbnail']['path'])) {
                $storage_paths[] = $upload_result['thumbnail']['path'];
            }
            if (isset($upload_result['webp']['path'])) {
                $storage_paths[] = $upload_result['webp']['path'];
            }
            
            // Supabase Storage'dan sil
            foreach ($storage_paths as $storage_path) {
                if ($storage_path) {
                    $this->imageManager->deleteFromSupabase($storage_path);
                }
            }
            
        } catch (Exception $e) {
            error_log("ProductImageService::cleanupUploadedFiles - " . $e->getMessage());
        }
    }
    
    /**
     * Resim dosyalarının varlığını kontrol et (Supabase için her zaman true döndür)
     */
    private function validateImageFiles($image) {
        // Supabase Storage için dosya varlığı kontrolü gerekmez
        // URL'ler her zaman erişilebilir olmalı
        return !empty($image['image_url']);
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
