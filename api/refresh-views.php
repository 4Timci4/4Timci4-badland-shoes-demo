<?php
/**
 * Materialized View Refresh Endpoint
 *
 * This script is called by a Supabase database webhook to refresh materialized views asynchronously.
 * It is protected by a secret key to prevent unauthorized access.
 */

// API bootstrap dosyasını dahil et (session kontrolü, yetkilendirme vb.)
require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../services/Product/ProductManagementService.php';

// Get the secret key from the request header
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$secret_key = str_replace('Bearer ', '', $auth_header);

// Verify the secret key
if (!defined('WEBHOOK_SECRET_KEY') || $secret_key !== WEBHOOK_SECRET_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Perform the refresh
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