<?php



require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../services/Product/ProductApiService.php';

try {

    $productApiService = new ProductApiService();


    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 9;
    $sort = $_GET['sort'] ?? 'created_at-desc';


    $categories = [];
    if (isset($_GET['categories'])) {
        $categories = is_array($_GET['categories']) ? $_GET['categories'] : [$_GET['categories']];
    }


    $genders = [];
    if (isset($_GET['genders'])) {
        $genders = is_array($_GET['genders']) ? $_GET['genders'] : [$_GET['genders']];
    }


    $params = [
        'page' => $page,
        'limit' => $limit,
        'sort' => $sort,
        'categories' => $categories,
        'genders' => $genders
    ];


    $result = $productApiService->getProductsForApi($params);


    echo json_encode($result);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Ürünler yüklenirken bir hata oluştu: ' . $e->getMessage()
    ]);
}
