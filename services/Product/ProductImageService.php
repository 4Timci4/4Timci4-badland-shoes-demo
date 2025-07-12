<?php

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/../../lib/SupabaseImageManager.php';

class ProductImageService
{
    private $db;
    private $imageManager;

    public function __construct()
    {
        $this->db = database();
        $this->imageManager = SupabaseImageManager::getInstance();
    }


    public function uploadProductImages($model_id, $color_id = null, $files = [], $options = [])
    {
        $results = [];
        $errors = [];

        try {

            $model = $this->db->select('product_models', ['id' => intval($model_id)], '*', ['limit' => 1]);
            if (empty($model)) {
                return ['success' => false, 'errors' => ['Geçersiz ürün modeli.']];
            }


            if ($color_id !== null) {
                $color = $this->db->select('colors', ['id' => intval($color_id)], '*', ['limit' => 1]);
                if (empty($color)) {
                    return ['success' => false, 'errors' => ['Geçersiz renk seçimi.']];
                }
            }


            if (isset($files['name']) && is_array($files['name'])) {

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

                $file_list = [$files];
            } else {
                return ['success' => false, 'errors' => ['Yüklenecek dosya bulunamadı.']];
            }


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


                $prefix = "product_{$model_id}";
                if ($color_id) {
                    $prefix .= "_color_{$color_id}";
                }
                $prefix .= "_";


                $upload_result = $this->imageManager->uploadAndOptimize($file, array_merge([
                    'prefix' => $prefix,
                    'generate_thumbnail' => true,
                    'generate_webp' => true,
                    'max_width' => 1200,
                    'max_height' => 1200,
                    'quality' => 90
                ], $options));

                if ($upload_result['success']) {

                    $image_url = null;
                    if (isset($upload_result['optimized']['url']) && !empty($upload_result['optimized']['url'])) {
                        $image_url = $upload_result['optimized']['url'];
                    } elseif (isset($upload_result['original']['url']) && !empty($upload_result['original']['url'])) {
                        $image_url = $upload_result['original']['url'];
                    }


                    if ($image_url) {
                        $is_primary_image = ($existing_count === 0);

                        if ($is_primary_image) {

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


    public function getProductImages($model_id, $color_id = null, $primary_only = false)
    {
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


            return array_filter($images, [$this, 'validateImageFiles']);

        } catch (Exception $e) {
            error_log("ProductImageService::getProductImages - " . $e->getMessage());
            return [];
        }
    }


    public function getProductImagesByColors($model_id)
    {
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


    public function getProductImagesByColor($model_id)
    {
        return $this->getProductImagesByColors($model_id);
    }


    public function setPrimaryImage($image_id)
    {
        try {

            $image = $this->db->select('product_images', ['id' => intval($image_id)], '*', ['limit' => 1]);
            if (empty($image)) {
                return false;
            }

            $image = $image[0];


            $this->db->update(
                'product_images',
                ['is_primary' => false],
                ['model_id' => $image['model_id']]
            );


            $result = $this->db->update('product_images', ['is_primary' => true], ['id' => $image_id]);
            return !empty($result);

        } catch (Exception $e) {
            error_log("ProductImageService::setPrimaryImage - " . $e->getMessage());
            return false;
        }
    }


    public function reorderImages($order_data)
    {
        try {
            foreach ($order_data as $item) {
                if (isset($item['id']) && isset($item['sort_order'])) {
                    $this->db->update(
                        'product_images',
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


    public function deleteImage($image_id)
    {
        try {

            $image = $this->db->select('product_images', ['id' => intval($image_id)], '*', ['limit' => 1]);
            if (empty($image)) {
                return false;
            }

            $image = $image[0];


            $this->deleteImageFiles($image);


            $result = $this->db->delete('product_images', ['id' => $image_id]);


            if ($image['is_primary']) {
                $this->ensurePrimaryImage($image['model_id'], $image['color_id']);
            }

            return !empty($result);

        } catch (Exception $e) {
            error_log("ProductImageService::deleteImage - " . $e->getMessage());
            return false;
        }
    }


    public function deleteAllProductImages($model_id, $color_id = null)
    {
        try {
            $conditions = ['model_id' => intval($model_id)];
            if ($color_id !== null) {
                $conditions['color_id'] = intval($color_id);
            }


            $images = $this->db->select('product_images', $conditions);


            foreach ($images as $image) {
                $this->deleteImageFiles($image);
            }


            $result = $this->db->delete('product_images', $conditions);
            return !empty($result);

        } catch (Exception $e) {
            error_log("ProductImageService::deleteAllProductImages - " . $e->getMessage());
            return false;
        }
    }


    private function generateAltText($model_id, $color_id = null)
    {
        try {

            $model = $this->db->select('product_models', ['id' => $model_id], 'name', ['limit' => 1]);
            $model_name = !empty($model) ? $model[0]['name'] : 'Ürün';

            $alt_text = $model_name;


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


    private function getNextSortOrder($model_id, $color_id = null)
    {
        try {

            $conditions = ['model_id' => intval($model_id)];

            if ($color_id !== null && $color_id !== '') {
                $conditions['color_id'] = intval($color_id);
            } else {


                $conditions['color_id'] = ['IS', null];
            }


            $options = [
                'order' => 'sort_order DESC',
                'limit' => 1
            ];

            $result = $this->db->select('product_images', $conditions, 'sort_order', $options);


            if (!empty($result) && isset($result[0]['sort_order'])) {
                return intval($result[0]['sort_order']) + 1;
            }

            return 1;

        } catch (Exception $e) {
            error_log("ProductImageService::getNextSortOrder - " . $e->getMessage());
            return 1;
        }
    }


    private function ensurePrimaryImage($model_id, $color_id = null)
    {
        try {
            $conditions = ['model_id' => $model_id];
            if ($color_id) {
                $conditions['color_id'] = $color_id;
            }


            $primary_exists = $this->db->count('product_images', array_merge($conditions, ['is_primary' => true]));

            if ($primary_exists === 0) {

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


    private function deleteImageFiles($image)
    {
        try {

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


    private function extractStoragePath($url)
    {


        if (strpos($url, '/storage/v1/object/public/') !== false) {
            $parts = explode('/storage/v1/object/public/', $url);
            if (count($parts) > 1) {

                $bucket_and_path = $parts[1];
                $path_parts = explode('/', $bucket_and_path, 2);
                return count($path_parts) > 1 ? $path_parts[1] : null;
            }
        }
        return null;
    }


    private function cleanupUploadedFiles($upload_result)
    {
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


            foreach ($storage_paths as $storage_path) {
                if ($storage_path) {
                    $this->imageManager->deleteFromSupabase($storage_path);
                }
            }

        } catch (Exception $e) {
            error_log("ProductImageService::cleanupUploadedFiles - " . $e->getMessage());
        }
    }


    private function validateImageFiles($image)
    {


        return !empty($image['image_url']);
    }


    public function generateImageHTML($image, $size = 'medium', $attributes = [])
    {
        $default_attributes = [
            'alt' => $image['alt_text'] ?? '',
            'loading' => 'lazy',
            'class' => 'product-image'
        ];

        $attributes = array_merge($default_attributes, $attributes);


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


function productImageService()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new ProductImageService();
    }
    return $instance;
}
