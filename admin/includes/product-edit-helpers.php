<?php
/**
 * Product Edit Helper Functions
 * Common utilities ve reusable functions
 */

function render_flash_message() {
    $flash_message = get_flash_message();
    if (!$flash_message) return;

    $bg_colors = [
        'success' => 'bg-green-50 border-green-200',
        'error' => 'bg-red-50 border-red-200',
        'info' => 'bg-blue-50 border-blue-200'
    ];
    
    $text_colors = [
        'success' => 'text-green-800',
        'error' => 'text-red-800',
        'info' => 'text-blue-800'
    ];
    
    $icons = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle'
    ];
    
    $icon_colors = [
        'success' => 'text-green-500',
        'error' => 'text-red-500',
        'info' => 'text-blue-500'
    ];
    
    $type = $flash_message['type'];
    $bg_color = $bg_colors[$type] ?? 'bg-gray-50 border-gray-200';
    $text_color = $text_colors[$type] ?? 'text-gray-800';
    $icon = $icons[$type] ?? 'fa-info';
    $icon_color = $icon_colors[$type] ?? 'text-gray-500';
    
    ?>
    <div class="<?= $bg_color ?> border rounded-xl p-4 flex items-start">
        <i class="fas <?= $icon ?> <?= $icon_color ?> mr-3 mt-0.5"></i>
        <div class="<?= $text_color ?> font-medium"><?= $flash_message['message'] ?></div>
    </div>
    <?php
}

function include_product_edit_scripts($product_id, $product_images_by_color, $all_colors, $product_base_price) {
    ?>
    <!-- Modular JavaScript Files -->
    <script src="assets/js/product-edit/form-validation.js"></script>
    <script src="assets/js/product-edit/variant-management.js"></script>
    <script src="assets/js/product-edit/image-manager.js"></script>
    <script src="assets/js/product-edit/main.js"></script>

    <!-- Product Image Management Initialization -->
    <script>
    // Global variables for JavaScript components
    window.productImagesByColor = <?= json_encode($product_images_by_color) ?>;
    window.allColors = <?= json_encode($all_colors) ?>;

    // Initialize Product Edit and Image Manager
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof ProductEditMain !== 'undefined') {
            window.productEditApp = new ProductEditMain(<?= $product_id ?>, <?= $product_base_price ?>);
        }
        
        if (typeof ProductImageManager !== 'undefined') {
            window.imageManager = new ProductImageManager(<?= $product_id ?>);
        }
    });
    </script>
    <?php
}

function validate_product_id($product_id) {
    if (!isset($product_id) || empty($product_id)) {
        set_flash_message('error', 'Düzenlenecek ürün ID\'si belirtilmedi.');
        header('Location: products.php');
        exit;
    }
    
    return intval($product_id);
}

function check_product_exists($product_data) {
    if (empty($product_data)) {
        set_flash_message('error', 'Ürün bulunamadı.');
        header('Location: products.php');
        exit;
    }
    
    return $product_data[0];
}
?>