<?php



require_once __DIR__ . '/api_bootstrap.php';
require_once '../services/CategoryService.php';
require_once '../services/Product/ProductApiService.php';
require_once '../services/GenderService.php';
require_once '../services/SettingsService.php';


$settingsService = new SettingsService();
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 12;
$category_filters = isset($_GET['categories']) ? (is_array($_GET['categories']) ? $_GET['categories'] : [$_GET['categories']]) : [];
$gender_filters = isset($_GET['genders']) ? (is_array($_GET['genders']) ? $_GET['genders'] : [$_GET['genders']]) : [];
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : 'created_at-desc';
$featured_filter = isset($_GET['featured']) ? (bool) $_GET['featured'] : null;


$category_service = category_service();
$product_api_service = product_api_service();
$gender_service = gender_service();


$products_result = $product_api_service->getProductsForApi([
    'page' => $page,
    'limit' => $limit,
    'categories' => $category_filters,
    'genders' => $gender_filters,
    'sort' => $sort_filter,
    'featured' => $featured_filter
]);

$products = $products_result['products'];
$total_products = $products_result['total'];
$total_pages = isset($products_result['pages']) ? $products_result['pages'] : ceil($total_products / $limit);

// Infinite scroll için ürün HTML'ini oluştur
ob_start();
if (!empty($products)) {
    foreach ($products as $product) {
        echo '<div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 group h-full flex flex-col">';
        echo '    <div class="relative overflow-hidden bg-gray-100 aspect-square">';
        echo '        <img src="' . htmlspecialchars($product['primary_image'] ?? 'assets/images/placeholder.svg') . '" alt="' . htmlspecialchars($product['name']) . '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" onerror="if(this.src !== \'assets/images/placeholder.svg\') this.src = \'assets/images/placeholder.svg\';">';
        if ($product['is_featured']) {
            echo '        <span class="absolute top-3 left-3 bg-primary text-white text-xs px-2 py-1 rounded">Öne Çıkan</span>';
        }
        echo '        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">';
        echo '            <a href="product-details.php?id=' . $product['id'] . '" class="w-10 h-10 bg-white rounded-full hover:bg-primary hover:text-white transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100" title="Ürün Detayı"><i class="fas fa-eye"></i></a>';
        echo '        </div>';
        echo '    </div>';
        echo '    <div class="p-4 text-center flex flex-col flex-grow">';
        echo '        <h3 class="text-lg font-medium text-secondary mb-3 min-h-[3.5rem] flex items-center justify-center">';
        echo '            <a href="product-details.php?id=' . $product['id'] . '" class="text-inherit hover:text-primary transition-colors line-clamp-2">' . htmlspecialchars($product['name']) . '</a>';
        echo '        </h3>';
        echo '        <div class="flex flex-wrap gap-1 justify-center mt-auto">';
        if (!empty($product['category_names'])) {
            foreach ($product['category_names'] as $category_name) {
                echo '            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"><i class="fas fa-tag text-xs mr-1"></i>' . htmlspecialchars($category_name) . '</span>';
            }
        }
        if (!empty($product['gender_names'])) {
            foreach ($product['gender_names'] as $gender_name) {
                echo '            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full"><i class="fas fa-venus-mars text-xs mr-1"></i>' . htmlspecialchars($gender_name) . '</span>';
            }
        }
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
    }
}
$products_html = ob_get_clean();

// Ürün sayısı HTML'i
ob_start();
echo '<strong>' . number_format($total_products) . '</strong> ürün bulundu';
$count_html = ob_get_clean();

// Filtre sayılarını hesapla
$filter_counts = $product_api_service->getFilterCounts([
    'categories' => $category_filters,
    'genders' => $gender_filters,
    'featured' => $featured_filter
]);

// Infinite scroll için gerekli bilgileri döndür
header('Content-Type: application/json');
echo json_encode([
    'products_html' => $products_html,
    'count_html' => $count_html,
    'filter_counts' => $filter_counts,
    'current_page' => $page,
    'total_pages' => $total_pages,
    'total_products' => $total_products,
    'has_more' => $page < $total_pages,
    'products_count' => count($products)
]);