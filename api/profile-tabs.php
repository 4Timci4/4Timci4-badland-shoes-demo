<?php
// Start output buffering
ob_start();

// API bootstrap dosyasını dahil et (session kontrolü, yetkilendirme vb.)
require_once __DIR__ . '/api_bootstrap.php';

// Giriş kontrolü - try/catch bloğu ile hata yönetimi
try {
    // Oturum kontrolü
    api_require_login();
    
    // Kullanıcı bilgilerini al (api_bootstrap.php'den gelen $current_user değişkeni)
    if (!isset($current_user) || !$current_user) {
        throw new Exception('Kullanıcı bilgileri alınamadı', 401);
    }
    
    $user = $current_user;
    $user_profile = $authService->getUserProfile($current_user['id']);
    
    // Aktif tab kontrolü
    $active_tab = $_GET['tab'] ?? 'profile';
    
    // Favoriler tabı için ek kontrol
    if ($active_tab === 'favorites' && (!isset($current_user['id']) || !$current_user['id'])) {
        throw new Exception('Favorileri görüntülemek için giriş yapmalısınız', 401);
    }
    
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
} catch (Exception $e) {
    // Hata durumunda 401 Unauthorized veya başka bir hata kodu döndür
    $code = $e->getCode() ?: 400;
    api_error($e->getMessage(), $code);
}

// End output buffering and flush buffer
ob_end_flush();
?>