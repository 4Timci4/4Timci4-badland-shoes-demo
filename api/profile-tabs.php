<?php
// Start output buffering
ob_start();

// API bootstrap dosyasını dahil et (session kontrolü, yetkilendirme vb.)
require_once __DIR__ . '/api_bootstrap.php';

// Giriş kontrolü
api_require_login();

// Kullanıcı bilgilerini al (api_bootstrap.php'den gelen $current_user değişkeni)
$user = $current_user;
$user_profile = $authService->getUserProfile($current_user['id']);

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