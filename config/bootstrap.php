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

        header('Location: /login.php');
        exit;
    }
}


if (!isset($_SERVER['REQUEST_URI']) || !strpos($_SERVER['REQUEST_URI'], 'login.php')) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'] ?? '/';
}