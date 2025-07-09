<?php
// Bu dosya, AJAX istekleri için yalnızca ürün listesi, sayfalama ve ürün sayısı HTML'ini döndürür.

// Gerekli servisleri ve ayarları dahil et
require_once '../config/database.php'; // Ensure adapter is loaded
require_once '../services/CategoryService.php';
require_once '../services/Product/ProductApiService.php';
require_once '../services/GenderService.php';

// Parametreleri al
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 9;
$category_filters = isset($_GET['categories']) ? (is_array($_GET['categories']) ? $_GET['categories'] : [$_GET['categories']]) : [];
$gender_filters = isset($_GET['genders']) ? (is_array($_GET['genders']) ? $_GET['genders'] : [$_GET['genders']]) : [];
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : 'created_at-desc';
$featured_filter = isset($_GET['featured']) ? (bool)$_GET['featured'] : null;

// Servisleri başlat
$category_service = category_service();
$product_api_service = product_api_service();
$gender_service = gender_service();

// Ürünleri doğrudan slug'lar ile al
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

// Response için HTML oluştur
ob_start();

// 1. Ürün Grid HTML'i
if (!empty($products)) {
    foreach ($products as $product) {
        // Bu kısım products.php'deki ürün kartı ile aynı olmalı
        echo '<div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 group h-full flex flex-col">';
        echo '    <div class="relative overflow-hidden bg-gray-100 aspect-square">';
        echo '        <img src="' . htmlspecialchars($product['primary_image'] ?? 'assets/images/placeholder.svg') . '" alt="' . htmlspecialchars($product['name']) . '" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">';
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
        echo '        <div class="text-xl font-bold text-secondary mb-3">₺ ' . number_format($product['base_price'], 2) . '</div>';
        echo '        <div class="flex flex-wrap gap-1 justify-center mt-auto">';
        if (!empty($product['categories'])) {
            foreach ($product['categories'] as $category) {
                echo '            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"><i class="fas fa-tag text-xs mr-1"></i>' . htmlspecialchars($category['name']) . '</span>';
            }
        }
        if (!empty($product['genders'])) {
            foreach ($product['genders'] as $gender) {
                echo '            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full"><i class="fas fa-venus-mars text-xs mr-1"></i>' . htmlspecialchars($gender['name']) . '</span>';
            }
        }
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
    }
} else {
    echo '<div class="col-span-full text-center py-12">';
    echo '    <div class="text-gray-500 mb-4"><i class="fas fa-search text-4xl"></i></div>';
    echo '    <h3 class="text-xl font-semibold text-gray-700 mb-2">Ürün Bulunamadı</h3>';
    echo '    <p class="text-gray-600 mb-4">Aradığınız kriterlere uygun ürün bulunamadı.</p>';
    echo '    <a href="products.php" class="bg-primary text-white px-6 py-2 rounded hover:bg-primary-dark transition-colors">Tüm Ürünler</a>';
    echo '</div>';
}
$products_html = ob_get_clean();

// 2. Sayfalama HTML'i
ob_start();
if ($total_pages > 1) {
    $start_page = max(1, $page - 2);
    $end_page = min($total_pages, $page + 2);

    if ($end_page - $start_page < 4 && $total_pages > 5) {
        if ($start_page === 1) {
            $end_page = min(5, $total_pages);
        } else if ($end_page === $total_pages) {
            $start_page = max(1, $total_pages - 4);
        }
    }

    if ($page > 1) {
        echo '<a href="?page=' . ($page - 1) . '&' . http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) . '" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors"><i class="fas fa-chevron-left"></i></a>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        echo '<a href="?page=' . $i . '&' . http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) . '" class="px-4 py-2 rounded transition-all ' . ($i === $page ? 'bg-primary text-white' : 'text-gray-600 hover:bg-primary hover:text-white') . '">' . $i . '</a>';
    }

    if ($page < $total_pages) {
        echo '<a href="?page=' . ($page + 1) . '&' . http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) . '" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors"><i class="fas fa-chevron-right"></i></a>';
    }
}
$pagination_html = ob_get_clean();


// 3. Ürün Sayısı HTML'i
ob_start();
echo '<strong>' . number_format($total_products) . '</strong> ürün bulundu';
$count_html = ob_get_clean();

// JSON olarak yanıtı gönder
header('Content-Type: application/json');
echo json_encode([
    'products_html' => $products_html,
    'pagination_html' => $pagination_html,
    'count_html' => $count_html
]);