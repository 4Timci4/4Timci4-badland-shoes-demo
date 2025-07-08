<?php
/**
 * AJAX Image Upload Handler
 * Supabase entegrasyonlu görsel yönetimi endpoint'i
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// CORS preflight için
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/auth.php';
require_once '../../config/database.php';
require_once '../../services/Product/ProductImageService.php';

// Admin kontrolü
try {
    check_admin_auth();
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkilendirme hatası']);
    exit;
}

// Service instance
$imageService = new ProductImageService();

// Action'a göre işlem yap
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        
        case 'upload_images':
            handleImageUpload();
            break;
            
        case 'set_primary':
            handleSetPrimary();
            break;
            
        case 'delete_image':
            handleDeleteImage();
            break;
            
        case 'get_images':
            handleGetImages();
            break;
            
        case 'reorder_images':
            handleReorderImages();
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Image Upload AJAX Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
}

/**
 * Görsel yükleme işlemi
 */
function handleImageUpload() {
    global $imageService;
    
    $product_id = intval($_POST['product_id'] ?? 0);
    $color_id = !empty($_POST['color_id']) && $_POST['color_id'] !== 'default' ? intval($_POST['color_id']) : null;
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Ürün ID gerekli']);
        return;
    }
    
    if (empty($_FILES['images'])) {
        echo json_encode(['success' => false, 'message' => 'Yüklenecek görsel seçilmedi']);
        return;
    }
    
    // Dosya upload'ı
    $upload_result = $imageService->uploadProductImages($product_id, $color_id, $_FILES['images'], [
        'max_images' => 10,
        'generate_thumbnail' => true,
        'generate_webp' => true,
        'max_width' => 1200,
        'max_height' => 1200,
        'quality' => 90
    ]);
    
    if ($upload_result['success']) {
        // Güncellenmiş görsel listesini getir
        $updated_images = $imageService->getProductImagesByColor($product_id);
        
        echo json_encode([
            'success' => true,
            'message' => $upload_result['uploaded_count'] . ' görsel başarıyla yüklendi',
            'images' => $updated_images[$color_id ?? 'default'] ?? [],
            'imagesByColor' => $updated_images,
            'upload_details' => $upload_result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Görsel yükleme hatası',
            'errors' => $upload_result['errors'] ?? []
        ]);
    }
}

/**
 * Ana görsel belirleme
 */
function handleSetPrimary() {
    global $imageService;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $image_id = intval($input['image_id'] ?? 0);
    
    if (!$image_id) {
        echo json_encode(['success' => false, 'message' => 'Görsel ID gerekli']);
        return;
    }
    
    $result = $imageService->setPrimaryImage($image_id);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Ana görsel güncellendi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ana görsel güncellenirken hata oluştu'
        ]);
    }
}

/**
 * Görsel silme
 */
function handleDeleteImage() {
    global $imageService;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $image_id = intval($input['image_id'] ?? 0);
    
    if (!$image_id) {
        echo json_encode(['success' => false, 'message' => 'Görsel ID gerekli']);
        return;
    }
    
    $result = $imageService->deleteImage($image_id);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Görsel silindi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Görsel silinirken hata oluştu'
        ]);
    }
}

/**
 * Görselleri getirme
 */
function handleGetImages() {
    global $imageService;
    
    $product_id = intval($_GET['product_id'] ?? 0);
    
    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Ürün ID gerekli']);
        return;
    }
    
    $images_by_color = $imageService->getProductImagesByColor($product_id);
    
    echo json_encode([
        'success' => true,
        'imagesByColor' => $images_by_color
    ]);
}

/**
 * Görsel sıralama
 */
function handleReorderImages() {
    global $imageService;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $order_data = $input['order_data'] ?? [];
    
    if (empty($order_data)) {
        echo json_encode(['success' => false, 'message' => 'Sıralama verisi gerekli']);
        return;
    }
    
    $result = $imageService->reorderImages($order_data);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Görsel sıralaması güncellendi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Sıralama güncellenirken hata oluştu'
        ]);
    }
}
?>