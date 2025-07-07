<?php
/**
 * Ürün API
 * 
 * Bu dosya, ürün listesi için AJAX isteklerini işler.
 * Filtreleme, sıralama ve sayfalama desteği sunar.
 */

header('Content-Type: application/json');

// Veritabanı bağlantısı ve gerekli servisleri dahil et
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ProductService.php';
require_once __DIR__ . '/../services/CategoryService.php';
require_once __DIR__ . '/../services/GenderService.php';

try {
    // Parametreleri al
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 9;
    $sort = $_GET['sort'] ?? 'created_at-desc';
    
    // Kategori filtreleri
    $categories = [];
    if (isset($_GET['categories'])) {
        $categories = is_array($_GET['categories']) ? $_GET['categories'] : [$_GET['categories']];
    }
    
    // Cinsiyet filtreleri
    $genders = [];
    if (isset($_GET['genders'])) {
        $genders = is_array($_GET['genders']) ? $_GET['genders'] : [$_GET['genders']];
    }
    
    // Parametreleri topla
    $params = [
        'page' => $page,
        'limit' => $limit,
        'sort' => $sort,
        'categories' => $categories,
        'genders' => $genders
    ];
    
    // Optimize edilmiş ürün getirme fonksiyonunu çağır
    $result = get_products_for_api($params);
    
    // Sonuçları JSON olarak döndür
    echo json_encode($result);
    
} catch (Exception $e) {
    // Hata durumunda hata mesajı döndür
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Ürünler yüklenirken bir hata oluştu: ' . $e->getMessage()
    ]);
}
