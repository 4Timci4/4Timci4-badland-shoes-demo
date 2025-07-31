<?php



require_once __DIR__ . '/session.php';


require_once __DIR__ . '/../services/AuthService.php';


$authService = new AuthService();


$authService->checkSessionSecurity();


$isLoggedIn = $authService->isLoggedIn();
$currentUser = $isLoggedIn ? $authService->getCurrentUser() : null;


function requireLogin()
{
    global $authService, $isLoggedIn;

    if (!$isLoggedIn) {

        header('Location: /login');
        exit;
    }
}


if (!isset($_SERVER['REQUEST_URI']) || !strpos($_SERVER['REQUEST_URI'], 'login.php')) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'] ?? '/';
}

// Bakım Modu Kontrolü
require_once __DIR__ . '/../services/SettingsService.php';
$settingsService = new SettingsService();
$maintenance_mode = $settingsService->getSiteSetting('maintenance_mode', 'false');

if ($maintenance_mode === 'true') {
    $is_maintenance_page = defined('MAINTENANCE_PAGE') && MAINTENANCE_PAGE;
    $is_admin_page = (strpos($_SERVER['REQUEST_URI'], '/admin') !== false);
    $is_login_page = (strpos($_SERVER['REQUEST_URI'], '/login.php') !== false);
    $is_admin_logged_in = (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true);

    if (!$is_maintenance_page && !$is_admin_page && !$is_login_page && !$is_admin_logged_in) {
        header('Location: /maintenance');
        exit;
    }
}