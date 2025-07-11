<?php
/**
 * Profile Tabs AJAX API
 * Tab içeriklerini AJAX ile getirmek için endpoint
 * Session yönetimi kaldırıldı - Profil özelliği çalışmamaktadır.
 */

// JSON response header
header('Content-Type: application/json');

// AJAX isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Session kaldırıldı - Profil özelliği devre dışı
http_response_code(503);
echo json_encode([
    'error' => 'Session yönetimi kaldırıldı - Profil özelliği çalışmamaktadır',
    'redirect' => '/login.php?reason=session_disabled'
]);
exit;
?>