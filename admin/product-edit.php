<?php
/**
 * Ürün Düzenleme Sayfası
 * Modern, kullanıcı dostu ürün düzenleme formu
 * Refactored with modular components
 */

require_once 'config/auth.php';
check_admin_auth();

// Include dependencies
require_once '../config/database.php';
require_once '../services/ProductService.php';
require_once '../services/CategoryService.php';
require_once '../services/GenderService.php';
require_once '../services/VariantService.php';
require_once '../services/Product/ProductImageService.php';

// Include modular components
require_once 'includes/product-edit-controller.php';
require_once 'includes/product-edit-data.php';
require_once 'includes/product-edit-helpers.php';

// Validate product ID
$product_id = validate_product_id($_GET['id'] ?? null);

// Get product data
$product_data = get_product_model($product_id);
$product = check_product_exists($product_data);

// Get all data needed for the page
$data = get_product_edit_data($product_id);
extract($data); // Extract variables for use in views

// Prepare page metadata
$page_data = prepare_page_data($product);
extract($page_data);

// Handle form submission
handle_product_edit_form($product_id);

// Include header
include 'includes/header.php';
?>

<!-- Product Edit Content -->
<div class="space-y-6">
    
    <?php include 'views/product-edit/header-section.php'; ?>

    <?php include 'views/product-edit/product-info-card.php'; ?>

    <!-- Flash Messages -->
    <?php render_flash_message(); ?>

    <!-- Product Edit Form -->
    <form method="POST" class="space-y-8" id="productEditForm">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <?php include 'views/product-edit/basic-info-form.php'; ?>

        <?php include 'views/product-edit/category-pricing-form.php'; ?>

        <?php include 'views/product-edit/product-status-form.php'; ?>

        <?php include 'views/product-edit/variant-management.php'; ?>
        
        <?php include 'views/product-edit/image-management.php'; ?>

        <?php include 'views/product-edit/form-actions.php'; ?>
    </form>
</div>

<?php
// Include scripts
include_product_edit_scripts(
    $product_id,
    $productImagesByColor,
    $all_colors,
    $product['base_price'],
    $variants
);

// Include footer
include 'includes/footer.php';
?>
