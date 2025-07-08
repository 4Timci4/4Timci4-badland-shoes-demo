<?php
/**
 * Product Edit Controller
 * Form processing ve business logic
 */

function handle_product_edit_form($product_id) {
    if (!$_POST) {
        return null;
    }

    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
        return false;
    }
    
    $action = $_POST['action'] ?? 'update_product';
    
    // Resim yükleme işlemini kontrol et
    if ($action === 'upload_images') {
        return handle_image_upload($product_id);
    }

    // Form verilerini al
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $base_price = floatval($_POST['base_price'] ?? 0);
    $category_ids = $_POST['category_ids'] ?? []; // Çoklu kategori seçimi
    $gender_ids = $_POST['gender_ids'] ?? []; // Çoklu cinsiyet seçimi
    $is_featured = isset($_POST['is_featured']) ? true : false;
    $features = trim($_POST['features'] ?? '');
    
    // Validation
    $errors = validate_product_form_data($name, $description, $base_price, $category_ids);
    
    if (empty($errors)) {
        try {
            $success = update_product_data($product_id, $name, $description, $base_price, $is_featured, $features, $category_ids, $gender_ids);
            
            if ($success) {
                set_flash_message('success', 'Ürün başarıyla güncellendi.');
                
                // Devam et mi yoksa listeye dön mü?
                if (isset($_POST['save_and_continue'])) {
                    header('Location: product-edit.php?id=' . $product_id);
                } else {
                    header('Location: products.php');
                }
                exit;
            } else {
                throw new Exception('Supabase güncelleme işlemi başarısız');
            }
            
        } catch (Exception $e) {
            error_log("Product update error: " . $e->getMessage());
            $errors[] = 'Ürün güncellenirken bir hata oluştu: ' . $e->getMessage();
        }
    }
    
    // Hataları flash message olarak sakla
    if (!empty($errors)) {
        set_flash_message('error', implode('<br>', $errors));
    }
    
    return false;
}

function validate_product_form_data($name, $description, $base_price, $category_ids) {
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Ürün adı zorunludur.';
    }
    
    if (empty($description)) {
        $errors[] = 'Ürün açıklaması zorunludur.';
    }
    
    if ($base_price <= 0) {
        $errors[] = 'Geçerli bir fiyat giriniz.';
    }
    
    if (empty($category_ids) || !is_array($category_ids)) {
        $errors[] = 'En az bir kategori seçiniz.';
    }
    
    return $errors;
}

/**
 * Resim yükleme işlemlerini yönet
 */
function handle_image_upload($product_id) {
    // Gerekli servisi yükle
    require_once __DIR__ . '/../../services/Product/ProductImageService.php';
    $productImageService = productImageService();
    
    $color_id = !empty($_POST['color_id']) ? intval($_POST['color_id']) : null;
    
    if (!empty($_FILES['product_images']['name'][0])) {
        $result = $productImageService->uploadProductImages($product_id, $color_id, $_FILES['product_images']);
        
        if ($result['success']) {
            set_flash_message('success', "Başarıyla {$result['uploaded_count']} resim yüklendi.");
            if (!empty($result['errors'])) {
                set_flash_message('error', implode('<br>', $result['errors']));
            }
        } else {
            set_flash_message('error', implode('<br>', $result['errors']));
        }
    } else {
        set_flash_message('error', 'Lütfen yüklenecek resim seçin.');
    }
    
    // Aynı sayfada kal
    header('Location: product-edit.php?id=' . $product_id);
    exit;
}

/**
 * AJAX resim işlemleri için handler
 */
function handle_ajax_image_actions() {
    if (!isset($_POST['action']) || !isset($_POST['product_id']) || !isset($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit;
    }
    
    if (!verify_csrf_token($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'error' => 'Security validation failed']);
        exit;
    }
    
    $product_id = intval($_POST['product_id']);
    $action = $_POST['action'];
    
    // Gerekli servisi yükle
    require_once __DIR__ . '/../../services/Product/ProductImageService.php';
    $productImageService = productImageService();
    
    $response = ['success' => false];
    
    switch ($action) {
        case 'set_primary_image':
            $image_id = intval($_POST['image_id'] ?? 0);
            if ($image_id > 0) {
                $success = $productImageService->setPrimaryImage($image_id);
                $response = ['success' => $success];
            }
            break;
            
        case 'delete_image':
            $image_id = intval($_POST['image_id'] ?? 0);
            if ($image_id > 0) {
                $success = $productImageService->deleteImage($image_id);
                $response = ['success' => $success];
            }
            break;
            
        case 'reorder_images':
            $order_data = json_decode($_POST['order_data'] ?? '[]', true);
            if (!empty($order_data)) {
                $success = $productImageService->reorderImages($order_data);
                $response = ['success' => $success];
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

function update_product_data($product_id, $name, $description, $base_price, $is_featured, $features, $category_ids, $gender_ids) {
    $update_data = [
        'name' => $name,
        'description' => $description,
        'base_price' => $base_price,
        'is_featured' => $is_featured,
        'features' => $features,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Supabase UPDATE işlemi
    $response = supabase()->request('product_models?id=eq.' . $product_id, 'PATCH', $update_data);
    
    if ($response && !empty($response['body'])) {
        // Mevcut kategori ilişkilerini sil
        $delete_response = supabase()->request('product_categories?product_id=eq.' . $product_id, 'DELETE');
        
        // Yeni kategori ilişkilerini ekle
        foreach ($category_ids as $category_id) {
            $category_data = [
                'product_id' => $product_id,
                'category_id' => intval($category_id)
            ];
            supabase()->request('product_categories', 'POST', $category_data);
        }
        
        // Mevcut cinsiyet ilişkilerini sil
        $delete_gender_response = supabase()->request('product_genders?product_id=eq.' . $product_id, 'DELETE');
        
        // Yeni cinsiyet ilişkilerini ekle
        foreach ($gender_ids as $gender_id) {
            $gender_data = [
                'product_id' => $product_id,
                'gender_id' => intval($gender_id)
            ];
            supabase()->request('product_genders', 'POST', $gender_data);
        }
        
        return true;
    }
    
    return false;
}
?>
