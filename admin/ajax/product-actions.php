<?php
/**
 * AJAX Ürün İşlemleri
 * Ürün durumu değiştirme ve silme işlemleri için AJAX endpoint
 */

// Hata raporlamayı kapat
error_reporting(0);
ini_set('display_errors', 0);

// JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// AJAX kontrolü
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Yalnızca POST istekleri kabul edilir']);
    exit;
}

try {
    // Auth kontrolü
    require_once '../config/auth.php';
    check_admin_auth();

    // Veritabanı bağlantısı
    require_once '../../config/database.php';
    require_once '../../services/ProductService.php';

    // CSRF token kontrolü
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        echo json_encode(['success' => false, 'message' => 'Güvenlik hatası. Lütfen tekrar deneyin.']);
        exit;
    }

    // Action parametresi
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz ürün ID']);
        exit;
    }

    switch ($action) {
        case 'toggle_featured':
            $is_featured = $_POST['is_featured'] === 'true';
            
            if (update_product_status($product_id, $is_featured)) {
                $status_text = $is_featured ? 'öne çıkarıldı' : 'normal duruma getirildi';
                echo json_encode([
                    'success' => true,
                    'message' => "Ürün başarıyla {$status_text}."
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ürün durumu güncellenirken bir hata oluştu.'
                ]);
            }
            break;

        case 'delete':
            if (delete_product($product_id)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Ürün başarıyla silindi.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ürün silinirken bir hata oluştu.'
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Geçersiz işlem'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Product Actions AJAX Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.'
    ]);
}

?>
