<?php
/**
 * API Bootstrap File - Tüm API dosyalarında dahil edilecek
 * 
 * Session yönetimi, güvenlik kontrolleri ve API yapılandırmaları için
 */

// Session yönetimini başlat
require_once __DIR__ . '/../config/session.php';

// Temel servisleri yükle
require_once __DIR__ . '/../services/AuthService.php';

// Global AuthService örneği oluştur
$authService = new AuthService();

// Session güvenlik kontrollerini yap
$authService->checkSessionSecurity();

// Kullanıcı giriş durumunu kontrol et
$is_logged_in = $authService->isLoggedIn();
$current_user = $is_logged_in ? $authService->getCurrentUser() : null;

// API yanıt formatı için yardımcı fonksiyonlar
function api_success($data = [], $message = 'Success') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function api_error($message = 'Error', $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// API için yetki kontrolü
function api_require_login() {
    global $is_logged_in;
    
    if (!$is_logged_in) {
        api_error('Unauthorized', 401);
    }
}