<?php
session_start();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$auth_service->logoutUser();

header('Location: index.php');
exit();