<?php
/**
 * Favoriler API
 * 
 * Bu dosya, favori ekleme/kaldırma işlemleri için AJAX isteklerini işler.
 * Session yönetimi kaldırıldı - Favori özelliği çalışmamaktadır.
 */

header('Content-Type: application/json');

// Session kaldırıldı - Favori özelliği devre dışı
http_response_code(503);
echo json_encode([
    'success' => false,
    'message' => 'Session yönetimi kaldırıldı - Favori özelliği çalışmamaktadır',
    'redirect' => 'login.php'
]);
exit;