<?php
require_once 'services/AuthService.php';
$authService = new AuthService();

$authService->logout();

header('Location: login.php');
exit();