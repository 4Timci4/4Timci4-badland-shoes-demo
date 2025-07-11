<?php
// Start output buffering
ob_start();

require_once '../services/AuthService.php';
$authService = new AuthService();

// Giriş kontrolü
if (!$authService->isLoggedIn()) {
    // HTMX istekleri için 401 Unauthorized hatası döndür
    header('HTTP/1.1 401 Unauthorized');
    echo '<p>Bu içeriği görüntülemek için giriş yapmalısınız.</p>';
    exit;
}

// Kullanıcı bilgilerini al
$currentUser = $authService->getCurrentUser();
$user = $currentUser;
$user_profile = $authService->getUserProfile($currentUser['id']);

// Aktif tab kontrolü
$active_tab = $_GET['tab'] ?? 'profile';

// Güvenlik sabiti
define('IS_PROFILE_PAGE', true);

// İlgili view dosyasını include et
if ($active_tab === 'profile') {
    if (file_exists('../views/profile/profile-form.php')) {
        include '../views/profile/profile-form.php';
    } else {
        echo '<p>Profil formu yüklenemedi.</p>';
    }
} elseif ($active_tab === 'favorites') {
    if (file_exists('../views/profile/favorites.php')) {
        include '../views/profile/favorites.php';
    } else {
        echo '<p>Favoriler yüklenemedi.</p>';
    }
} else {
    echo '<p>Geçersiz sekme.</p>';
}

// End output buffering and flush buffer
ob_end_flush();
?>