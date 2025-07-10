<?php
/**
 * Varyant API Endpoint
 * AJAX isteklerini karşılayan API dosyası
 */

// JSON response için header ayarla
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// CSRF koruması için
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Gerekli dosyaları dahil et
require_once 'config/auth.php';
require_once '../config/database.php';
require_once '../services/VariantService.php';

// Admin kontrolü
check_admin_auth();

try {
    // POST verilerini al
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['action'])) {
        throw new Exception('Geçersiz istek');
    }
    
    $action = $data['action'];
    $variant_service = variant_service();
    
    switch ($action) {
        case 'add':
            // Yeni varyant ekleme
            if (!isset($data['data'])) {
                throw new Exception('Varyant verileri eksik');
            }
            
            $variant_data = $data['data'];
            
            // Validation
            if (empty($variant_data['model_id']) || empty($variant_data['color_id']) ||
                empty($variant_data['size_id'])) {
                throw new Exception('Gerekli alanlar eksik');
            }
            
            // Aynı kombinasyonun var olup olmadığını kontrol et
            $existing_variants = $variant_service->getProductVariants($variant_data['model_id']);
            foreach ($existing_variants as $existing) {
                if ($existing['color_id'] == $variant_data['color_id'] && 
                    $existing['size_id'] == $variant_data['size_id']) {
                    throw new Exception('Bu renk/beden kombinasyonu zaten mevcut');
                }
            }
            
            $result = $variant_service->createVariant($variant_data);
            
            if ($result === true) {
                // Basit varyant verisi oluştur (manual join yapmaya gerek yok)
                $colors = $variant_service->getAllColors();
                $sizes = $variant_service->getAllSizes();
                
                // Renk ve beden bilgilerini bul
                $color_info = null;
                $size_info = null;
                
                foreach ($colors as $color) {
                    if ($color['id'] == $variant_data['color_id']) {
                        $color_info = $color;
                        break;
                    }
                }
                
                foreach ($sizes as $size) {
                    if ($size['id'] == $variant_data['size_id']) {
                        $size_info = $size;
                        break;
                    }
                }
                
                // SKU oluştur
                $timestamp = time();
                $sku = "PRD{$variant_data['model_id']}-C{$variant_data['color_id']}-S{$variant_data['size_id']}-{$timestamp}";
                
                // Mock varyant verisi oluştur (tabloya eklemek için)
                $new_variant = [
                    'id' => $timestamp, // Temporary ID for frontend
                    'model_id' => $variant_data['model_id'],
                    'color_id' => $variant_data['color_id'],
                    'size_id' => $variant_data['size_id'],
                    'sku' => $sku,
                    'stock_quantity' => $variant_data['stock_quantity'] ?? 0,
                    'is_active' => $variant_data['is_active'] ?? true,
                    'color_name' => $color_info ? $color_info['name'] : 'Bilinmeyen Renk',
                    'color_hex' => $color_info ? $color_info['hex_code'] : '#cccccc',
                    'size_value' => $size_info ? $size_info['size_value'] : 'Bilinmeyen Beden',
                    'size_type' => $size_info ? $size_info['size_type'] : ''
                ];
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Varyant başarıyla eklendi',
                    'variant' => $new_variant
                ]);
            } else {
                // VariantService'den string error mesajı geldi
                throw new Exception(is_string($result) ? $result : 'Varyant eklenemedi');
            }
            break;
            
        case 'update':
            // Varyant güncelleme
            if (!isset($data['variant_id']) || !isset($data['data'])) {
                throw new Exception('Varyant ID veya verileri eksik');
            }
            
            $variant_id = intval($data['variant_id']);
            $update_data = $data['data'];
            
            // Boolean değerini doğru formata çevir
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
            // Varyant silme
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
            // Toplu güncelleme
            if (!isset($data['product_id']) || !isset($data['data'])) {
                throw new Exception('Ürün ID veya veriler eksik');
            }
            
            $product_id = intval($data['product_id']);
            $update_data = $data['data'];
            
            // Ürünün tüm varyantlarını getir
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
            // Toplu silme
            if (!isset($data['product_id'])) {
                throw new Exception('Ürün ID eksik');
            }
            
            $product_id = intval($data['product_id']);
            
            // Ürünün tüm varyantlarını getir
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
            // Varyantları getir (sayfa yenileme olmadan)
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
            // Sadece stok güncelleme
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
