<?php
// Session konfigürasyonunu dahil et
require_once 'config/session.php';
start_session_safely();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$auth_service->logoutUser();

header('Location: index.php');
exit();