<?php

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

require_once 'config/auth.php';
require_once '../config/database.php';
require_once '../services/VariantService.php';

check_admin_auth();

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['action'])) {
        throw new Exception('Geçersiz istek');
    }

    $action = $data['action'];
    $variant_service = variant_service();

    switch ($action) {
        case 'add':
            if (!isset($data['data'])) {
                throw new Exception('Varyant verileri eksik');
            }

            $variant_data = $data['data'];

            if (
                empty($variant_data['model_id']) || empty($variant_data['color_id']) ||
                empty($variant_data['size_id'])
            ) {
                throw new Exception('Gerekli alanlar eksik');
            }

            $existing_variants = $variant_service->getProductVariants($variant_data['model_id']);
            foreach ($existing_variants as $existing) {
                if (
                    $existing['color_id'] == $variant_data['color_id'] &&
                    $existing['size_id'] == $variant_data['size_id']
                ) {
                    throw new Exception('Bu renk/beden kombinasyonu zaten mevcut');
                }
            }

            $new_variant = $variant_service->createVariant($variant_data);

            if ($new_variant) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Varyant başarıyla eklendi',
                    'variant' => $new_variant
                ]);
            } else {
                throw new Exception('Varyant eklenemedi veya zaten mevcut.');
            }
            break;

        case 'update':
            if (!isset($data['variant_id']) || !isset($data['data'])) {
                throw new Exception('Varyant ID veya verileri eksik');
            }

            $variant_id = intval($data['variant_id']);
            $update_data = $data['data'];

            if (isset($update_data['is_active'])) {
                $update_data['is_active'] = $update_data['is_active'] === true || $update_data['is_active'] === 'true' || $update_data['is_active'] === 1;
            }


            if (isset($update_data['stock_quantity']) && $update_data['stock_quantity'] < 0) {
                throw new Exception('Stok miktarı negatif olamaz');
            }

            $result = $variant_service->updateVariant($variant_id, $update_data);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Varyant başarıyla güncellendi']);
            } else {
                throw new Exception('Varyant güncellenemedi');
            }
            break;

        case 'delete':
            if (!isset($data['variant_id'])) {
                throw new Exception('Varyant ID eksik');
            }

            $variant_id = intval($data['variant_id']);
            $result = $variant_service->deleteVariant($variant_id);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Varyant başarıyla silindi']);
            } else {
                throw new Exception('Varyant silinemedi');
            }
            break;

        case 'bulk_update':
            if (!isset($data['product_id']) || !isset($data['data'])) {
                throw new Exception('Ürün ID veya veriler eksik');
            }

            $product_id = intval($data['product_id']);
            $update_data = $data['data'];

            $variants = $variant_service->getProductVariants($product_id);

            $updated_count = 0;
            foreach ($variants as $variant) {
                $result = $variant_service->updateVariant($variant['id'], $update_data);
                if ($result) {
                    $updated_count++;
                }
            }

            if ($updated_count > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => "$updated_count varyant başarıyla güncellendi"
                ]);
            } else {
                throw new Exception('Hiçbir varyant güncellenemedi');
            }
            break;

        case 'bulk_delete':
            if (!isset($data['product_id'])) {
                throw new Exception('Ürün ID eksik');
            }

            $product_id = intval($data['product_id']);

            $variants = $variant_service->getProductVariants($product_id);

            $deleted_count = 0;
            foreach ($variants as $variant) {
                $result = $variant_service->deleteVariant($variant['id']);
                if ($result) {
                    $deleted_count++;
                }
            }

            if ($deleted_count > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => "$deleted_count varyant başarıyla silindi"
                ]);
            } else {
                throw new Exception('Hiçbir varyant silinemedi');
            }
            break;

        case 'get_variants':
            if (!isset($data['product_id'])) {
                throw new Exception('Ürün ID eksik');
            }

            $product_id = intval($data['product_id']);
            $variants = $variant_service->getProductVariants($product_id);
            $total_stock = $variant_service->getTotalStock($product_id);

            echo json_encode([
                'success' => true,
                'variants' => $variants,
                'total_stock' => $total_stock
            ]);
            break;

        case 'update_stock':
            if (!isset($data['variant_id']) || !isset($data['stock_quantity'])) {
                throw new Exception('Varyant ID veya stok miktarı eksik');
            }

            $variant_id = intval($data['variant_id']);
            $stock_quantity = intval($data['stock_quantity']);

            if ($stock_quantity < 0) {
                throw new Exception('Stok miktarı negatif olamaz');
            }

            $result = $variant_service->updateStock($variant_id, $stock_quantity);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Stok başarıyla güncellendi']);
            } else {
                throw new Exception('Stok güncellenemedi');
            }
            break;

        default:
            throw new Exception('Geçersiz işlem');
    }

} catch (Exception $e) {
    error_log("Variant API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
