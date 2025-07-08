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