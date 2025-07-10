<?php
/**
 * Profile Tabs AJAX API
 * Tab içeriklerini AJAX ile getirmek için endpoint
 */

require_once '../config/session.php';
start_session_safely();
require_once '../services/AuthService.php';

// JSON response header
header('Content-Type: application/json');

// AJAX isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$auth_service = auth_service();

// Session kontrolü
if (!$auth_service->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'redirect' => '/login.php?reason=no_session']);
    exit;
}

$user = $auth_service->getCurrentUser();
if (!$user || empty($user['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid user', 'redirect' => '/login.php?reason=invalid_user']);
    exit;
}

$tab = $_GET['tab'] ?? 'profile';

try {
    ob_start();
    
    if ($tab === 'favorites') {
        // Favorites tab için $user değişkeninin varlığını doğrula
        if (!isset($user) || !is_array($user) || empty($user['id'])) {
            throw new Exception('User data not available for favorites');
        }
        include '../views/profile/favorites.php';
    } else {
        // Profile tab içeriği
        $user_profile = $auth_service->getUserProfile($user['id']);
        if (!$user_profile) {
            throw new Exception('User profile not found');
        }
        include '../views/profile/profile-form.php';
    }
    
    $content = ob_get_clean();
    
    // Boş content kontrolü
    if (empty($content)) {
        throw new Exception('Empty content generated for tab: ' . $tab);
    }
    
    echo json_encode([
        'success' => true,
        'content' => $content,
        'tab' => $tab
    ]);
    
} catch (Exception $e) {
    // Buffer temizle
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    error_log("Profile tabs API error: " . $e->getMessage() . " | Tab: " . $tab . " | User ID: " . ($user['id'] ?? 'not set'));
    http_response_code(500);
    echo json_encode([
        'error' => 'İçerik yüklenirken bir hata oluştu',
        'debug' => $e->getMessage(),
        'tab' => $tab
    ]);
}
?>