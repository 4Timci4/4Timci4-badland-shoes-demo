<?php
/**
 * Product Edit Data Layer
 * Data fetching ve preparation
 */

function get_product_edit_data($product_id) {
    // Gerekli servisleri dahil et (Proje kökünden - daha sağlam yol)
    require_once dirname(__DIR__, 2) . '/services/CategoryService.php';
    require_once dirname(__DIR__, 2) . '/services/GenderService.php';
    require_once dirname(__DIR__, 2) . '/services/VariantService.php';
    require_once dirname(__DIR__, 2) . '/services/Product/ProductImageService.php';

    // Ürün bilgilerini getir
    $product_data = get_product_model($product_id);
    if (empty($product_data) || !isset($product_data[0])) {
        return null;
    }
    
    $product = $product_data[0];
    
    // Kategorileri ve cinsiyetleri getir (Optimize edilmiş)
    $categories = category_service()->getCategoriesWithProductCountsOptimized(true);
    $genders = gender_service()->getAllGenders();
    
    // Varyant verilerini getir
    $variants = variant_service()->getProductVariants($product_id);
    $all_colors = variant_service()->getAllColors();
    $all_sizes = variant_service()->getAllSizes();
    $total_stock = variant_service()->getTotalStock($product_id);
    
    // Ürün görsellerini getir
    $imageService = new ProductImageService();
    $productImages = $imageService->getProductImages($product_id);
    $productImagesByColor = $imageService->getProductImagesByColor($product_id);
    
    // Her renk için görsel sayısını hesapla
    $imageCountsByColor = [];
    foreach ($productImagesByColor as $colorId => $images) {
        $imageCountsByColor[$colorId] = count($images);
    }
    
    // Mevcut kategorileri al
    $selected_categories = get_selected_categories($product_id);
    
    // Mevcut cinsiyetleri al
    $selected_genders = get_selected_genders($product_id);
    
    return [
        'product' => $product,
        'categories' => $categories,
        'genders' => $genders,
        'variants' => $variants,
        'all_colors' => $all_colors,
        'all_sizes' => $all_sizes,
        'total_stock' => $total_stock,
        'productImages' => $productImages,
        'productImagesByColor' => $productImagesByColor,
        'imageCountsByColor' => $imageCountsByColor,
        'selected_categories' => $selected_categories,
        'selected_genders' => $selected_genders
    ];
}

function get_selected_categories($product_id) {
    $selected_categories = [];
    try {
        $db = database();
        $category_relations = $db->select('product_categories', ['product_id' => $product_id], ['category_id']);
        $selected_categories = array_column($category_relations, 'category_id');
    } catch (Exception $e) {
        error_log("Error fetching product categories: " . $e->getMessage());
    }
    return $selected_categories;
}

function get_selected_genders($product_id) {
    $selected_genders = [];
    try {
        $db = database();
        $gender_relations = $db->select('product_genders', ['product_id' => $product_id], ['gender_id']);
        $selected_genders = array_column($gender_relations, 'gender_id');
    } catch (Exception $e) {
        error_log("Error fetching product genders: " . $e->getMessage());
    }
    return $selected_genders;
}

function prepare_page_data($product) {
    $product_name = !empty($product) && isset($product['name']) ? htmlspecialchars($product['name']) : 'Bilinmeyen Ürün';
    
    return [
        'page_title' => 'Ürün Düzenle: ' . $product_name,
        'breadcrumb_items' => [
            ['title' => 'Ürün Yönetimi', 'url' => 'products.php', 'icon' => 'fas fa-box'],
            ['title' => $product_name, 'url' => '#', 'icon' => 'fas fa-tag'],
            ['title' => 'Düzenle', 'url' => '#', 'icon' => 'fas fa-edit']
        ]
    ];
}
?>
