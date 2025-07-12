<?php

require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../services/Product/FavoriteService.php';

try {
    api_require_login();

    $userId = $current_user['id'];

    try {
        $userExists = $authService->getUserProfile($userId);
        if (!$userExists) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Kullanıcı bilgileri bulunamadı. Lütfen tekrar giriş yapın.',
                'redirect' => 'logout.php'
            ]);
            error_log("Favorites API: User ID $userId not found in database");
            exit;
        }
    } catch (Exception $e) {
        error_log("Favorites API - User check error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Kullanıcı bilgileri doğrulanamadı'
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Sadece POST istekleri kabul edilir'
        ]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['action']) || !isset($input['variantId'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Gerekli parametreler eksik'
        ]);
        exit;
    }

    $action = $input['action'];
    $variantId = intval($input['variantId']);
    $colorId = isset($input['colorId']) ? intval($input['colorId']) : null;

    $favoriteService = new FavoriteService();

    switch ($action) {
        case 'add':
            $result = $favoriteService->addFavorite($userId, $variantId, $colorId);
            break;

        case 'remove':
            $result = $favoriteService->removeFavorite($userId, $variantId);
            break;

        case 'check':
            $isFavorite = $favoriteService->isFavorite($userId, $variantId);
            $result = [
                'success' => true,
                'is_favorite' => $isFavorite
            ];
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Geçersiz işlem'
            ]);
            exit;
    }

    echo json_encode($result);

} catch (Exception $e) {
    error_log("Favorites API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sunucu hatası oluştu'
    ]);
}
?>