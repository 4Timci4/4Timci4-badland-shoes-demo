<?php
// Ürün ID'sini al
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Veritabanı bağlantısı
require_once 'config/database.php';

// Veritabanından ürün bilgilerini çek
$product_data_result = get_product_model($product_id);
$product_data = $product_data_result ? $product_data_result[0] : null;

// Eğer ürün bulunamazsa geri yönlendir
if (!$product_data) {
    header("Location: products.php");
    exit;
}

// Ürün verileri bulunduktan sonra diğer verileri çek
$product_variants = get_product_variants($product_id);
$product_images = get_product_images($product_id);
$all_colors = get_colors(); // Performans için renkleri bir kez çek
$all_sizes = get_sizes();   // Performans için bedenleri bir kez çek

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

// Benzer ürünleri getir
$category_slug = isset($product['category_slug']) ? $product['category_slug'] : null;
$similar_products = [];

if ($category_slug) {
    // Mevcut ürün hariç, aynı kategoriden 5 ürün getir (4 tane göstermek için)
    $all_products = get_product_models(5, 0, $category_slug, null);
    
    foreach($all_products as $p) {
        if (count($similar_products) >= 4) break;
        if ($p['id'] != $product_id) {
            $similar_products[] = $p;
        }
    }
}
?>
