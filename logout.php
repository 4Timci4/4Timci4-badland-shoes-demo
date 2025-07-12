<?php

require_once 'services/AuthService.php';
$authService = new AuthService();


$authService->startSession();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit();
}


$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (empty($submittedToken) || empty($sessionToken) || !hash_equals($sessionToken, $submittedToken)) {


    header('Location: index.php');
    exit();
}


if ($authService->isLoggedIn()) {
    $authService->logout();
}


unset($_SESSION['csrf_token']);


header('Location: login.php?logout=success');
exit();