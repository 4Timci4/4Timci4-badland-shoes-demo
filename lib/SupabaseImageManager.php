<?php

require_once __DIR__ . '/SupabaseClient.php';
require_once __DIR__ . '/SecurityManager.php';

class SupabaseImageManager
{
    private static $instance = null;
    private $supabaseClient;
    private $bucket;

    private const DEFAULT_QUALITY = 85;
    private const MAX_WIDTH = 1920;
    private const MAX_HEIGHT = 1080;
    private const THUMBNAIL_WIDTH = 300;
    private const THUMBNAIL_HEIGHT = 300;

    private const SUPPORTED_FORMATS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private $temp_dir;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->supabaseClient = new SupabaseClient(SUPABASE_URL, SUPABASE_KEY);
        $this->bucket = 'product-images';
        $this->temp_dir = sys_get_temp_dir() . '/bandland_temp_images/';

        if (!file_exists($this->temp_dir)) {
            mkdir($this->temp_dir, 0755, true);
        }
    }

    private function isGDAvailable()
    {
        return extension_loaded('gd') && function_exists('imagecreatefromjpeg');
    }

    public function checkSystemRequirements()
    {
        $requirements = [
            'gd_extension' => extension_loaded('gd'),
            'imagecreatefromjpeg' => function_exists('imagecreatefromjpeg'),
            'imagecreatefrompng' => function_exists('imagecreatefrompng'),
            'imagewebp' => function_exists('imagewebp'),
            'curl' => extension_loaded('curl'),
            'fileinfo' => extension_loaded('fileinfo')
        ];

        error_log("System Requirements Check: " . json_encode($requirements));
        return $requirements;
    }

    public function uploadAndOptimize($file, $options = [])
    {
        $options = array_merge([
            'generate_thumbnail' => true,
            'generate_webp' => true,
            'quality' => self::DEFAULT_QUALITY,
            'max_width' => self::MAX_WIDTH,
            'max_height' => self::MAX_HEIGHT,
            'prefix' => ''
        ], $options);

        $requirements = $this->checkSystemRequirements();
        $gdAvailable = $requirements['gd_extension'] && $requirements['imagecreatefromjpeg'];

        if (!$gdAvailable) {
            error_log("GD extension not available. Uploading original file only.");
            $options['generate_thumbnail'] = false;
            $options['generate_webp'] = false;
        }

        $security = SecurityManager::getInstance();
        $validation_errors = $security->validateFileUpload($file, self::SUPPORTED_FORMATS);

        if (!empty($validation_errors)) {
            return ['success' => false, 'errors' => $validation_errors];
        }

        try {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $options['prefix'] . uniqid() . '_' . time();
            $original_filename = $filename . '.' . $extension;

            $temp_original_path = $this->temp_dir . $original_filename;
            if (!move_uploaded_file($file['tmp_name'], $temp_original_path)) {
                return ['success' => false, 'errors' => ['Dosya yüklenemedi.']];
            }

            $image_info = getimagesize($temp_original_path);
            if ($image_info === false) {
                unlink($temp_original_path);
                return ['success' => false, 'errors' => ['Geçersiz resim dosyası.']];
            }

            $result = ['success' => true];

            $original_result = $this->uploadToSupabase($temp_original_path, 'original/' . $original_filename);
            if ($original_result) {
                $result['original'] = array_merge($original_result, [
                    'filename' => $original_filename,
                    'width' => $image_info[0],
                    'height' => $image_info[1],
                    'size' => filesize($temp_original_path)
                ]);
            }

            if ($gdAvailable) {
                $optimized_result = $this->optimizeImage($temp_original_path, $filename, $extension, $options);
                if ($optimized_result) {
                    $result['optimized'] = $optimized_result;
                }

                if ($options['generate_thumbnail']) {
                    $thumbnail_result = $this->generateThumbnail($temp_original_path, $filename, $extension);
                    if ($thumbnail_result) {
                        $result['thumbnail'] = $thumbnail_result;
                    }
                }

                if ($options['generate_webp'] && function_exists('imagewebp')) {
                    $webp_result = $this->generateWebP($temp_original_path, $filename);
                    if ($webp_result) {
                        $result['webp'] = $webp_result;
                    }
                }
            } else {
                $result['optimized'] = $result['original'];
                error_log("GD extension not available. Using original file as optimized version.");
            }

            if (file_exists($temp_original_path)) {
                unlink($temp_original_path);
            }

            return $result;

        } catch (Exception $e) {
            error_log("Supabase image upload error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Resim işleme hatası: ' . $e->getMessage()]];
        }
    }

    private function optimizeImage($source_path, $filename, $extension, $options)
    {
        try {
            $optimized_filename = $filename . '_optimized.' . $extension;
            $temp_optimized_path = $this->temp_dir . $optimized_filename;

            $source_image = $this->loadImage($source_path, $extension);
            if (!$source_image) {
                return false;
            }

            $original_width = imagesx($source_image);
            $original_height = imagesy($source_image);

            $new_dimensions = $this->calculateDimensions(
                $original_width,
                $original_height,
                $options['max_width'],
                $options['max_height']
            );

            $optimized_image = imagecreatetruecolor($new_dimensions['width'], $new_dimensions['height']);

            if ($extension === 'png') {
                imagealphablending($optimized_image, false);
                imagesavealpha($optimized_image, true);
                $transparent = imagecolorallocatealpha($optimized_image, 255, 255, 255, 127);
                imagefill($optimized_image, 0, 0, $transparent);
            }

            imagecopyresampled(
                $optimized_image,
                $source_image,
                0,
                0,
                0,
                0,
                $new_dimensions['width'],
                $new_dimensions['height'],
                $original_width,
                $original_height
            );

            $success = $this->saveImage($optimized_image, $temp_optimized_path, $extension, $options['quality']);

            imagedestroy($source_image);
            imagedestroy($optimized_image);

            if ($success) {
                $upload_result = $this->uploadToSupabase($temp_optimized_path, 'optimized/' . $optimized_filename);

                $file_size = 0;
                if (file_exists($temp_optimized_path)) {
                    $file_size = filesize($temp_optimized_path);
                    unlink($temp_optimized_path);
                }

                if ($upload_result) {
                    return array_merge($upload_result, [
                        'filename' => $optimized_filename,
                        'width' => $new_dimensions['width'],
                        'height' => $new_dimensions['height'],
                        'size' => $file_size
                    ]);
                }
            }

            return false;

        } catch (Exception $e) {
            error_log("Image optimization error: " . $e->getMessage());
            return false;
        }
    }

    private function generateThumbnail($source_path, $filename, $extension)
    {
        try {
            $thumbnail_filename = $filename . '_thumb.' . $extension;
            $temp_thumbnail_path = $this->temp_dir . $thumbnail_filename;

            $source_image = $this->loadImage($source_path, $extension);
            if (!$source_image) {
                return false;
            }

            $original_width = imagesx($source_image);
            $original_height = imagesy($source_image);

            $crop_size = min($original_width, $original_height);
            $crop_x = ($original_width - $crop_size) / 2;
            $crop_y = ($original_height - $crop_size) / 2;

            $thumbnail = imagecreatetruecolor(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);

            if ($extension === 'png') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefill($thumbnail, 0, 0, $transparent);
            }

            imagecopyresampled(
                $thumbnail,
                $source_image,
                0,
                0,
                $crop_x,
                $crop_y,
                self::THUMBNAIL_WIDTH,
                self::THUMBNAIL_HEIGHT,
                $crop_size,
                $crop_size
            );

            $success = $this->saveImage($thumbnail, $temp_thumbnail_path, $extension, self::DEFAULT_QUALITY);

            imagedestroy($source_image);
            imagedestroy($thumbnail);

            if ($success) {
                $upload_result = $this->uploadToSupabase($temp_thumbnail_path, 'thumbnails/' . $thumbnail_filename);

                $file_size = 0;
                if (file_exists($temp_thumbnail_path)) {
                    $file_size = filesize($temp_thumbnail_path);
                    unlink($temp_thumbnail_path);
                }

                if ($upload_result) {
                    return array_merge($upload_result, [
                        'filename' => $thumbnail_filename,
                        'width' => self::THUMBNAIL_WIDTH,
                        'height' => self::THUMBNAIL_HEIGHT,
                        'size' => $file_size
                    ]);
                }
            }

            return false;

        } catch (Exception $e) {
            error_log("Thumbnail generation error: " . $e->getMessage());
            return false;
        }
    }

    private function generateWebP($source_path, $filename)
    {
        try {
            if (!function_exists('imagewebp')) {
                return false;
            }

            $webp_filename = $filename . '.webp';
            $temp_webp_path = $this->temp_dir . $webp_filename;

            $image_info = getimagesize($source_path);
            $mime_type = $image_info['mime'];

            switch ($mime_type) {
                case 'image/jpeg':
                    $source_image = imagecreatefromjpeg($source_path);
                    break;
                case 'image/png':
                    $source_image = imagecreatefrompng($source_path);
                    break;
                case 'image/gif':
                    $source_image = imagecreatefromgif($source_path);
                    break;
                default:
                    return false;
            }

            if (!$source_image) {
                return false;
            }

            $success = imagewebp($source_image, $temp_webp_path, self::DEFAULT_QUALITY);
            imagedestroy($source_image);

            if ($success) {
                $upload_result = $this->uploadToSupabase($temp_webp_path, 'webp/' . $webp_filename);

                $file_size = 0;
                if (file_exists($temp_webp_path)) {
                    $file_size = filesize($temp_webp_path);
                    unlink($temp_webp_path);
                }

                if ($upload_result) {
                    return array_merge($upload_result, [
                        'filename' => $webp_filename,
                        'size' => $file_size
                    ]);
                }
            }

            return false;

        } catch (Exception $e) {
            error_log("WebP generation error: " . $e->getMessage());
            return false;
        }
    }

    private function uploadToSupabase($file_path, $storage_path)
    {
        try {
            $file_content = file_get_contents($file_path);
            if ($file_content === false) {
                return false;
            }

            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_path);
                finfo_close($finfo);
            } else {
                $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $mime_types = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp'
                ];
                $mime_type = $mime_types[$extension] ?? 'application/octet-stream';
            }

            $project_id = 'rfxleyiyvpygdpdbnmib';
            $storage_url = "https://{$project_id}.supabase.co/storage/v1";

            $upload_url = "{$storage_url}/object/{$this->bucket}/{$storage_path}";

            error_log("Supabase upload URL: " . $upload_url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $upload_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $file_content);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . SUPABASE_KEY,
                'Content-Type: ' . $mime_type,
                'apikey: ' . SUPABASE_KEY
            ]);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error) {
                error_log("cURL Error: " . $curl_error);
            }

            if ($http_code === 200 || $http_code === 201) {
                $public_url = $storage_url . '/object/public/' . $this->bucket . '/' . $storage_path;

                return [
                    'url' => $public_url,
                    'path' => $storage_path,
                    'bucket' => $this->bucket
                ];
            } else {
                error_log("Supabase upload failed: HTTP $http_code - $response");
                return false;
            }

        } catch (Exception $e) {
            error_log("Supabase upload error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteFromSupabase($storage_path)
    {
        try {
            $project_id = 'rfxleyiyvpygdpdbnmib';
            $storage_url = "https://{$project_id}.supabase.co/storage/v1";

            $delete_url = "{$storage_url}/object/{$this->bucket}/{$storage_path}";

            error_log("Supabase delete URL: " . $delete_url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $delete_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . SUPABASE_KEY,
                'apikey: ' . SUPABASE_KEY
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $http_code === 200;

        } catch (Exception $e) {
            error_log("Supabase delete error: " . $e->getMessage());
            return false;
        }
    }

    private function loadImage($path, $extension)
    {
        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'png':
                return imagecreatefrompng($path);
            case 'gif':
                return imagecreatefromgif($path);
            case 'webp':
                return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false;
            default:
                return false;
        }
    }

    private function saveImage($image, $path, $extension, $quality = self::DEFAULT_QUALITY)
    {
        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image, $path, $quality);
            case 'png':
                $png_quality = round((100 - $quality) / 10);
                return imagepng($image, $path, $png_quality);
            case 'gif':
                return imagegif($image, $path);
            case 'webp':
                return function_exists('imagewebp') ? imagewebp($image, $path, $quality) : false;
            default:
                return false;
        }
    }

    private function calculateDimensions($original_width, $original_height, $max_width, $max_height)
    {
        $ratio = min($max_width / $original_width, $max_height / $original_height);

        if ($ratio > 1) {
            $ratio = 1;
        }

        return [
            'width' => round($original_width * $ratio),
            'height' => round($original_height * $ratio)
        ];
    }
}

function supabaseImageManager()
{
    return SupabaseImageManager::getInstance();
}
?>