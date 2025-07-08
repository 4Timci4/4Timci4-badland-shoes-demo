<?php
/**
 * Image Upload AJAX Handler
 * Resim yükleme ve yönetim işlemleri için AJAX endpoint
 */

require_once '../config/auth.php';
check_admin_auth();

require_once '../../config/database.php';
require_once '../../services/Product/ProductImageService.php';
require_once '../includes/product-edit-controller.php';

// JSON response header
header('Content-Type: application/json');

// GET istekleri için
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_images' && isset($_GET['product_id'])) {
        $product_id = intval($_GET['product_id']);
        $productImageService = productImageService();
        $imagesByColor = $productImageService->getProductImagesByColors($product_id);
        
        echo json_encode([
            'success' => true,
            'imagesByColor' => $imagesByColor
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'Invalid GET request']);
    exit;
}

// POST istekleri için
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verisi mi JSON verisi mi kontrol et
    $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    
    if ($isJson) {
        // JSON verisi
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
            exit;
        }
        
        $_POST = $data;
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'upload_images':
            handle_upload_images();
            break;
            
        case 'set_primary':
            handle_set_primary();
            break;
            
        case 'delete_image':
            handle_delete_image();
            break;
            
        case 'reorder_images':
            handle_reorder_images();
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            exit;
    }
}

/**
 * Resim yükleme işlemi
 */
function handle_upload_images() {
    if (!isset($_POST['product_id']) || !isset($_FILES['images'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $product_id = intval($_POST['product_id']);
    $color_id = !empty($_POST['color_id']) ? intval($_POST['color_id']) : null;
    
    $productImageService = productImageService();
    $result = $productImageService->uploadProductImages($product_id, $color_id, $_FILES['images']);
    
    if ($result['success']) {
        // Yükleme başarılı - yeni resimleri getir
        $imagesByColor = $productImageService->getProductImagesByColors($product_id);
        
        echo json_encode([
            'success' => true,
            'message' => "Başarıyla {$result['uploaded_count']} resim yüklendi.",
            'imagesByColor' => $imagesByColor,
            'images' => $imagesByColor[$color_id ?? 'default'] ?? []
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => implode('<br>', $result['errors'])
        ]);
    }
    exit;
}

/**
 * Ana resim belirleme
 */
function handle_set_primary() {
    if (!isset($_POST['image_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing image_id']);
        exit;
    }
    
    $image_id = intval($_POST['image_id']);
    $productImageService = productImageService();
    $result = $productImageService->setPrimaryImage($image_id);
    
    echo json_encode(['success' => $result]);
    exit;
}

/**
 * Resim silme
 */
function handle_delete_image() {
    if (!isset($_POST['image_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing image_id']);
        exit;
    }
    
    $image_id = intval($_POST['image_id']);
    $productImageService = productImageService();
    $result = $productImageService->deleteImage($image_id);
    
    echo json_encode(['success' => $result]);
    exit;
}

/**
 * Resimleri yeniden sıralama
 */
function handle_reorder_images() {
    if (!isset($_POST['order_data']) || !is_array($_POST['order_data'])) {
        echo json_encode(['success' => false, 'error' => 'Missing or invalid order_data']);
        exit;
    }
    
    $productImageService = productImageService();
    $result = $productImageService->reorderImages($_POST['order_data']);
    
    echo json_encode(['success' => $result]);
    exit;
}
