<?php
header('Content-Type: application/json');

require_once '../config/bootstrap.php';

require_once __DIR__ . '/../services/VariantService.php';

// Gerekli parametreleri al
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$colorId = isset($_GET['color_id']) ? (int)$_GET['color_id'] : 0;

if (!$productId || !$colorId) {
    http_response_code(400);
    echo json_encode(['error' => 'Eksik parametre: product_id ve color_id gereklidir.']);
    exit;
}

try {
    $variantService = new VariantService();
    $variants = $variantService->getVariantsByProductAndColor($productId, $colorId);
    
    echo json_encode($variants);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Sunucu hatası: ' . $e->getMessage()]);
}
