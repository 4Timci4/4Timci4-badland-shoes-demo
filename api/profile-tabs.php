<?php

ob_start();


require_once __DIR__ . '/api_bootstrap.php';


try {

    api_require_login();


    if (!isset($current_user) || !$current_user) {
        throw new Exception('Kullanıcı bilgileri alınamadı', 401);
    }

    $user = $current_user;
    $user_profile = $authService->getUserProfile($current_user['id']);


    $active_tab = $_GET['tab'] ?? 'profile';


    if ($active_tab === 'favorites' && (!isset($current_user['id']) || !$current_user['id'])) {
        throw new Exception('Favorileri görüntülemek için giriş yapmalısınız', 401);
    }


    define('IS_PROFILE_PAGE', true);


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

    $code = $e->getCode() ?: 400;
    api_error($e->getMessage(), $code);
}


ob_end_flush();
?>