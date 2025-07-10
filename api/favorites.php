<?php
/**
 * Favoriler API
 * 
 * Bu dosya, favori ekleme/kaldırma işlemleri için AJAX isteklerini işler.
 */

header('Content-Type: application/json');

// Oturum ve gerekli servisleri yükle
session_start();
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/Product/FavoriteService.php';

// Kullanıcı giriş yapmış mı kontrol et
$auth_service = auth_service();
$user = $auth_service->getCurrentUser();

if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Bu işlemi gerçekleştirmek için giriş yapmalısınız',
        'redirect' => 'login.php'
    ]);
    exit;
}

// Favori servisini başlat
$favorite_service = favorite_service();

try {
    // POST metodu ile gelen istekleri işle
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;
        
        if ($variant_id <= 0) {
            throw new Exception('Geçersiz varyant ID');
        }
        
        switch ($action) {
            case 'add':
                $color_id = isset($_POST['color_id']) ? intval($_POST['color_id']) : null;
                $result = $favorite_service->addFavorite($user['id'], $variant_id, $color_id);
                echo json_encode($result);
                break;
                
            case 'remove':
                $result = $favorite_service->removeFavorite($user['id'], $variant_id);
                echo json_encode($result);
                break;
                
            case 'check':
                $isFavorite = $favorite_service->isFavorite($user['id'], $variant_id);
                echo json_encode([
                    'success' => true,
                    'is_favorite' => $isFavorite
                ]);
                break;
                
            default:
                throw new Exception('Geçersiz işlem');
        }
    } 
    // GET metodu ile gelen istekleri işle (favori durumu kontrolü)
    else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['variant_id'])) {
        $variant_id = intval($_GET['variant_id']);
        
        if ($variant_id <= 0) {
            throw new Exception('Geçersiz varyant ID');
        }
        
        $isFavorite = $favorite_service->isFavorite($user['id'], $variant_id);
        echo json_encode([
            'success' => true,
            'is_favorite' => $isFavorite
        ]);
    }
    // Favori listesini getir
    else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['list'])) {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        
        $favorites = $favorite_service->getFavorites($user['id'], $limit, $offset);
        echo json_encode([
            'success' => true,
            'data' => $favorites
        ]);
    }
    else {
        throw new Exception('Geçersiz istek');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}