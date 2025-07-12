<?php



require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../services/Product/ProductManagementService.php';


$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$secret_key = str_replace('Bearer ', '', $auth_header);


if (!defined('WEBHOOK_SECRET_KEY') || $secret_key !== WEBHOOK_SECRET_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}


try {
    $productManagementService = new ProductManagementService();
    $success = $productManagementService->refreshMaterializedViews();

    if ($success) {
        http_response_code(200);
        echo json_encode(['message' => 'Materialized views refreshed successfully.']);
    } else {
        throw new Exception('Refresh command failed.');
    }
} catch (Exception $e) {
    error_log('Webhook Refresh Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to refresh materialized views.']);
}