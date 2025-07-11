<?php
/**
 * Bootstrap File - Tüm sayfalarda dahil edilecek
 * 
 * Session yönetimi, güvenlik kontrolleri ve temel yapılandırmalar için
 */

// Session yönetimini başlat
require_once __DIR__ . '/session.php';

// Temel servisleri yükle
require_once __DIR__ . '/../services/AuthService.php';

// Global AuthService örneği oluştur
$authService = new AuthService();

// Session güvenlik kontrollerini yap
$authService->checkSessionSecurity();

// Kullanıcı giriş durumunu kontrol et (opsiyonel)
$isLoggedIn = $authService->isLoggedIn();
$currentUser = $isLoggedIn ? $authService->getCurrentUser() : null;

// Sayfa erişim kontrolü için yardımcı fonksiyon
function requireLogin() {
    global $authService, $isLoggedIn;
    
    if (!$isLoggedIn) {
        // Kullanıcı giriş yapmamışsa login sayfasına yönlendir
        header('Location: /login.php');
        exit;
    }
}

// Şu anki sayfayı session'a kaydet (geri dönüş için)
if (!isset($_SERVER['REQUEST_URI']) || !strpos($_SERVER['REQUEST_URI'], 'login.php')) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'] ?? '/';
}