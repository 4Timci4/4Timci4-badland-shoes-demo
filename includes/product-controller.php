<?php



require_once 'config/database.php';
require_once 'services/Product/ProductApiService.php';
require_once 'services/AuthService.php';
require_once 'services/Product/FavoriteService.php';



$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    header("Location: products.php");
    exit;
}

$db = database();
$product_api_service = product_api_service();
$authService = new AuthService();
$favoriteService = new FavoriteService();


$is_logged_in = $authService->isLoggedIn();
$current_user = $is_logged_in ? $authService->getCurrentUser() : null;
$favorite_variant_ids = [];
if ($is_logged_in) {
    $favorite_variant_ids = $favoriteService->getFavoriteVariantIds($current_user['id']);
}

$product_data = $db->select('product_details_view', ['id' => ['eq', $product_id]]);
if (empty($product_data)) {
    header("Location: products.php");
    exit;
}

$product = $product_data[0];


$product['categories'] = is_string($product['categories']) ? json_decode($product['categories'], true) : ($product['categories'] ?? []);
$product['genders'] = is_string($product['genders']) ? json_decode($product['genders'], true) : ($product['genders'] ?? []);
$product['variants'] = is_string($product['variants']) ? json_decode($product['variants'], true) : ($product['variants'] ?? []);
$product['images'] = is_string($product['images']) ? json_decode($product['images'], true) : ($product['images'] ?? []);





$selected_color_slug = isset($_GET['color']) ? trim($_GET['color']) : '';
$all_colors = [];
$variants_by_color_id = [];
if (!empty($product['variants'])) {
    foreach ($product['variants'] as $variant) {
        $variants_by_color_id[$variant['color_id']][] = $variant;
    }
    $color_ids = array_keys($variants_by_color_id);
    if (!empty($color_ids)) {
        $all_colors = $db->select('colors', ['id' => ['in', $color_ids]]);
    }
}

function createColorSlug($colorName)
{
    $slug = strtolower($colorName ?? '');
    $slug = str_replace(['ı', 'İ', 'ş', 'Ş', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'], ['i', 'i', 's', 's', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'], $slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    return preg_replace('/-+/', '-', trim($slug, '-'));
}

$selected_color_id = null;
if (!empty($selected_color_slug)) {
    foreach ($all_colors as $color) {
        if (createColorSlug($color['name']) === $selected_color_slug) {
            $selected_color_id = $color['id'];
            break;
        }
    }
}

if (!$selected_color_id && !empty($all_colors)) {
    $selected_color_id = $all_colors[0]['id'];
}


$current_images = [];
if (!empty($product['images'])) {
    foreach ($product['images'] as $image) {
        if ($image['color_id'] === $selected_color_id) {
            $current_images[] = $image;
        }
    }
    if (empty($current_images)) {
        foreach ($product['images'] as $image) {
            if ($image['is_primary']) {
                $current_images[] = $image;
            }
        }
    }
    if (empty($current_images) && isset($product['images'][0])) {
        $current_images[] = $product['images'][0];
    }
}


$available_sizes = [];
if (!empty($product['variants'])) {

    $all_size_ids = array_unique(array_column($product['variants'], 'size_id'));

    if (!empty($all_size_ids)) {
        $available_sizes = $db->select('sizes', ['id' => ['in', $all_size_ids]]);

        usort($available_sizes, fn($a, $b) => strnatcmp($a['size_value'], $b['size_value']));
    }
}


$features = !empty($product['features']) ? explode("\n", $product['features']) : [];


$similar_products = $product_api_service->getSimilarProducts($product['id'], 5);

?>