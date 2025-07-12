<?php


class ImageManager
{
    private static $instance = null;


    private const DEFAULT_QUALITY = 85;
    private const MAX_WIDTH = 1920;
    private const MAX_HEIGHT = 1080;
    private const THUMBNAIL_WIDTH = 300;
    private const THUMBNAIL_HEIGHT = 300;


    private const SUPPORTED_FORMATS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];


    private $upload_dir;
    private $upload_url;


    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    private function __construct()
    {
        $this->upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploads/';
        $this->upload_url = '/assets/images/uploads/';


        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }


        $subdirs = ['original', 'optimized', 'thumbnails', 'webp'];
        foreach ($subdirs as $subdir) {
            $dir = $this->upload_dir . $subdir . '/';
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
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


        require_once __DIR__ . '/SecurityManager.php';
        $security = SecurityManager::getInstance();
        $validation_errors = $security->validateFileUpload($file, self::SUPPORTED_FORMATS);

        if (!empty($validation_errors)) {
            return ['success' => false, 'errors' => $validation_errors];
        }

        try {

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $options['prefix'] . uniqid() . '_' . time();
            $original_filename = $filename . '.' . $extension;


            $original_path = $this->upload_dir . 'original/' . $original_filename;
            if (!move_uploaded_file($file['tmp_name'], $original_path)) {
                return ['success' => false, 'errors' => ['Dosya yüklenemedi.']];
            }


            $image_info = getimagesize($original_path);
            if ($image_info === false) {
                unlink($original_path);
                return ['success' => false, 'errors' => ['Geçersiz resim dosyası.']];
            }

            $result = [
                'success' => true,
                'original' => [
                    'filename' => $original_filename,
                    'url' => $this->upload_url . 'original/' . $original_filename,
                    'path' => $original_path,
                    'width' => $image_info[0],
                    'height' => $image_info[1],
                    'size' => filesize($original_path)
                ]
            ];


            $optimized_result = $this->optimizeImage($original_path, $filename, $extension, $options);
            if ($optimized_result) {
                $result['optimized'] = $optimized_result;
            }


            if ($options['generate_thumbnail']) {
                $thumbnail_result = $this->generateThumbnail($original_path, $filename, $extension);
                if ($thumbnail_result) {
                    $result['thumbnail'] = $thumbnail_result;
                }
            }


            if ($options['generate_webp'] && function_exists('imagewebp')) {
                $webp_result = $this->generateWebP($original_path, $filename);
                if ($webp_result) {
                    $result['webp'] = $webp_result;
                }
            }

            return $result;

        } catch (Exception $e) {
            error_log("Image upload error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Resim işleme hatası: ' . $e->getMessage()]];
        }
    }


    private function optimizeImage($source_path, $filename, $extension, $options)
    {
        try {
            $optimized_filename = $filename . '_optimized.' . $extension;
            $optimized_path = $this->upload_dir . 'optimized/' . $optimized_filename;


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


            $success = $this->saveImage($optimized_image, $optimized_path, $extension, $options['quality']);

            imagedestroy($source_image);
            imagedestroy($optimized_image);

            if ($success) {
                return [
                    'filename' => $optimized_filename,
                    'url' => $this->upload_url . 'optimized/' . $optimized_filename,
                    'path' => $optimized_path,
                    'width' => $new_dimensions['width'],
                    'height' => $new_dimensions['height'],
                    'size' => filesize($optimized_path)
                ];
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
            $thumbnail_path = $this->upload_dir . 'thumbnails/' . $thumbnail_filename;

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

            $success = $this->saveImage($thumbnail, $thumbnail_path, $extension, self::DEFAULT_QUALITY);

            imagedestroy($source_image);
            imagedestroy($thumbnail);

            if ($success) {
                return [
                    'filename' => $thumbnail_filename,
                    'url' => $this->upload_url . 'thumbnails/' . $thumbnail_filename,
                    'path' => $thumbnail_path,
                    'width' => self::THUMBNAIL_WIDTH,
                    'height' => self::THUMBNAIL_HEIGHT,
                    'size' => filesize($thumbnail_path)
                ];
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
            $webp_path = $this->upload_dir . 'webp/' . $webp_filename;


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


            $success = imagewebp($source_image, $webp_path, self::DEFAULT_QUALITY);
            imagedestroy($source_image);

            if ($success) {
                return [
                    'filename' => $webp_filename,
                    'url' => $this->upload_url . 'webp/' . $webp_filename,
                    'path' => $webp_path,
                    'size' => filesize($webp_path)
                ];
            }

            return false;

        } catch (Exception $e) {
            error_log("WebP generation error: " . $e->getMessage());
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


    public function generateResponsiveHTML($image_data, $alt_text = '', $classes = '', $lazy_load = true)
    {
        if (!$image_data || !isset($image_data['optimized'])) {
            return '';
        }

        $optimized = $image_data['optimized'];
        $webp = $image_data['webp'] ?? null;
        $thumbnail = $image_data['thumbnail'] ?? null;

        $html = '<picture class="' . htmlspecialchars($classes) . '">';


        if ($webp) {
            $html .= '<source srcset="' . htmlspecialchars($webp['url']) . '" type="image/webp">';
        }


        $img_attributes = [
            'src' => $lazy_load && $thumbnail ? $thumbnail['url'] : $optimized['url'],
            'alt' => htmlspecialchars($alt_text),
            'width' => $optimized['width'],
            'height' => $optimized['height']
        ];

        if ($lazy_load) {
            $img_attributes['loading'] = 'lazy';
            $img_attributes['data-src'] = $optimized['url'];
            if ($thumbnail) {
                $img_attributes['class'] = 'lazy-load';
            }
        }

        $html .= '<img';
        foreach ($img_attributes as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }
        $html .= '>';

        $html .= '</picture>';

        return $html;
    }


    public function deleteImage($filename_without_extension)
    {
        $deleted_files = [];
        $directories = ['original', 'optimized', 'thumbnails', 'webp'];

        foreach ($directories as $dir) {
            $dir_path = $this->upload_dir . $dir . '/';
            $files = glob($dir_path . $filename_without_extension . '*');

            foreach ($files as $file) {
                if (file_exists($file) && unlink($file)) {
                    $deleted_files[] = $file;
                }
            }
        }

        return $deleted_files;
    }


    public function getLazyLoadJS()
    {
        return "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.classList.remove('lazy-load');
                                img.classList.add('lazy-loaded');
                                observer.unobserve(img);
                            }
                        }
                    });
                });
                
                document.querySelectorAll('img.lazy-load').forEach(img => {
                    imageObserver.observe(img);
                });
            } else {
                
                document.querySelectorAll('img.lazy-load').forEach(img => {
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy-load');
                        img.classList.add('lazy-loaded');
                    }
                });
            }
        });
        </script>
        
        <style>
        img.lazy-load {
            opacity: 0.6;
            transition: opacity 0.3s;
        }
        
        img.lazy-loaded {
            opacity: 1;
        }
        </style>
        ";
    }


    public function cleanupOldFiles($days = 30)
    {
        $cutoff_time = time() - ($days * 24 * 60 * 60);
        $deleted_count = 0;
        $directories = ['original', 'optimized', 'thumbnails', 'webp'];

        foreach ($directories as $dir) {
            $dir_path = $this->upload_dir . $dir . '/';
            $files = glob($dir_path . '*');

            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoff_time) {
                    if (unlink($file)) {
                        $deleted_count++;
                    }
                }
            }
        }

        return $deleted_count;
    }
}


function imageManager()
{
    return ImageManager::getInstance();
}
