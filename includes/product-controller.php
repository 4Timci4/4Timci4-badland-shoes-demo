<?php
// Ürün ID'sini al
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Veritabanı bağlantısı
require_once 'config/database.php';
require_once 'services/Product/ProductImageService.php';

// Renk slug'ını URL'den al
$selected_color_slug = isset($_GET['color']) ? trim($_GET['color']) : '';

// Renk slug'ları için helper fonksiyonlar
function createColorSlug($colorName) {
    $slug = mb_strtolower($colorName, 'UTF-8');
    $slug = str_replace(['ı', 'İ', 'ş', 'Ş', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'], 
                       ['i', 'i', 's', 's', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'], $slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    return preg_replace('/-+/', '-', trim($slug, '-'));
}

function findColorIdBySlug($slug, $colors) {
    foreach ($colors as $color) {
        if (createColorSlug($color['name']) === $slug) {
            return $color['id'];
        }
    }
    return null;
}

// Veritabanından ürün bilgilerini çek
$product_data_result = get_product_model($product_id);
$product_data = $product_data_result ? $product_data_result[0] : null;

// Eğer ürün bulunamazsa geri yönlendir
if (!$product_data) {
    header("Location: products.php");
    exit;
}

// Ürün verileri bulunduktan sonra diğer verileri çek - Sadece aktif varyantları al
$product_variants = get_product_variants($product_id, true); // Sadece aktif varyantlar
$all_colors = get_colors(); // Performans için renkleri bir kez çek
$all_sizes = get_sizes();   // Performans için bedenleri bir kez çek

// Yeni Image Service'i kullan
$productImageService = productImageService();
$product_images_by_color = $productImageService->getProductImagesByColors($product_id);
$product_images = $productImageService->getProductImages($product_id); // Tüm resimler (geriye uyumluluk için)

// Ürün bilgilerini al
$product = $product_data;

// Ürün özelliklerini hazırla
$features = !empty($product['features']) ? explode("\n", $product['features']) : [];

// Renkleri hazırla (Optimize Edilmiş)
$colors = [];
$product_color_ids = array_unique(array_column($product_variants, 'color_id'));
$all_colors_map = array_column($all_colors, null, 'id'); // Renkleri ID'ye göre haritala

foreach ($product_color_ids as $color_id) {
    if (isset($all_colors_map[$color_id])) {
        $colors[] = $all_colors_map[$color_id];
    }
}

// Seçilen renk ID'sini bul
$selected_color_id = null;
if (!empty($selected_color_slug)) {
    $selected_color_id = findColorIdBySlug($selected_color_slug, $colors);
}

// Eğer seçilen renk bulunamazsa veya hiç renk seçilmemişse, ilk rengi varsayılan yap
if (!$selected_color_id && !empty($colors)) {
    $selected_color_id = $colors[0]['id'];
}

// Seçilen renk bilgilerini al
$selected_color = null;
if ($selected_color_id) {
    $selected_color = $all_colors_map[$selected_color_id] ?? null;
}

// Seçilen renge göre görselleri filtrele
$current_images = [];
if ($selected_color_id && isset($product_images_by_color[$selected_color_id])) {
    $current_images = $product_images_by_color[$selected_color_id];
    // Sort by sort_order
    usort($current_images, function($a, $b) {
        return ($a['sort_order'] ?? 999) - ($b['sort_order'] ?? 999);
    });
} elseif (isset($product_images_by_color['default'])) {
    // Varsayılan görseller varsa onları kullan
    $current_images = $product_images_by_color['default'];
    usort($current_images, function($a, $b) {
        return ($a['sort_order'] ?? 999) - ($b['sort_order'] ?? 999);
    });
} elseif (!empty($product_images_by_color)) {
    // Hiç varsayılan yoksa ilk rengin görsellerini kullan
    $current_images = reset($product_images_by_color);
    usort($current_images, function($a, $b) {
        return ($a['sort_order'] ?? 999) - ($b['sort_order'] ?? 999);
    });
}

// Bedenleri hazırla (Optimize Edilmiş)
$sizes = [];
$product_size_ids = array_unique(array_column($product_variants, 'size_id'));
$all_sizes_map = array_column($all_sizes, null, 'id'); // Bedenleri ID'ye göre haritala

foreach ($product_size_ids as $size_id) {
    if (isset($all_sizes_map[$size_id])) {
        $sizes[] = $all_sizes_map[$size_id];
    }
}
// Bedenleri değere göre sırala
usort($sizes, function($a, $b) {
    return strnatcmp($a['size_value'], $b['size_value']);
});

// Benzer ürünleri getir - SADECE aynı kategoriden
$similar_products = [];

// Aynı kategoriden ürünleri getir
$category_slug = isset($product['category_slug']) ? $product['category_slug'] : null;
if ($category_slug) {
    // Daha fazla ürün getir (20) çünkü filtreleme sonrası azalabilir
    $category_products = get_product_models(20, 0, $category_slug, null);
    
    foreach($category_products as $p) {
        // Sadece farklı ürünleri ekle (mevcut ürünü hariç tut)
        if ($p['id'] != $product_id) {
            $similar_products[] = $p;
        }
    }
    
    // En fazla 5 ürün göster
    $similar_products = array_slice($similar_products, 0, 5);
}
?>
