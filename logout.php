<?php
require_once 'services/AuthService.php';
$authService = new AuthService();

// Güvenlik kontrolü: Sadece doğrudan kullanıcı eylemi ile logout olmalı
$isDirectRequest = true;

// Referer kontrolü
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    
    // Eğer referer profile.php ise ve POST değilse, otomatik yönlendirme olabilir
    if (strpos($referer, 'user/profile.php') !== false && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $isDirectRequest = false;
    }
}

// Oturum açıksa ve doğrudan istek ise logout yap
if ($isDirectRequest && $authService->isLoggedIn()) {
    $authService->logout();
    $validLogout = true;
} else {
    $validLogout = false;
}

// Login sayfasına yönlendir
header('Location: login.php' . ($validLogout ? '?logout=success' : ''));
exit();