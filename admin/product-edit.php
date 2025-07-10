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

// Handle AJAX image actions if this is an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_POST['action'])) {
    handle_ajax_image_actions();
}

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

<!-- Product Edit Wizard -->
<div class="wizard-container bg-gray-50 p-4 sm:p-6 lg:p-8 rounded-2xl">
    <?php include 'views/product-edit/header-section.php'; ?>
    <?php render_flash_message(); ?>

    <!-- Wizard Header -->
    <div class="wizard-header mb-8 p-4 bg-white rounded-xl shadow border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800" id="wizard-step-title">Adım 1: Temel Bilgiler</h3>
            <div class="text-sm font-medium text-gray-500"><span id="wizard-current-step">1</span> / 4</div>
        </div>
        <div class="progress-bar w-full bg-gray-200 rounded-full h-2.5">
            <div id="wizard-progress" class="bg-blue-600 h-2.5 rounded-full" style="width: 25%"></div>
        </div>
    </div>

    <!-- Product Edit Form -->
    <form method="POST" class="space-y-8" id="productEditForm">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <!-- Step 1: Basic Info -->
        <div class="wizard-step" data-step="1">
            <?php include 'views/product-edit/basic-info-form.php'; ?>
        </div>

        <!-- Step 2: Category & Pricing -->
        <div class="wizard-step hidden" data-step="2">
            <?php include 'views/product-edit/category-pricing-form.php'; ?>
            <?php include 'views/product-edit/product-status-form.php'; ?>
        </div>

        <!-- Step 3: Variant Management -->
        <div class="wizard-step hidden" data-step="3">
            <?php include 'views/product-edit/variant-management.php'; ?>
        </div>
        
        <!-- Step 4: Image Management -->
        <div class="wizard-step hidden" data-step="4">
            <?php include 'views/product-edit/image-management.php'; ?>
        </div>

        <!-- Wizard Navigation -->
        <div class="wizard-navigation pt-6 border-t flex justify-between items-center">
            <button type="button" id="prev-step-btn" class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50" disabled>
                <i class="fas fa-arrow-left mr-2"></i> Önceki
            </button>
            <button type="button" id="next-step-btn" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                Sonraki <i class="fas fa-arrow-right ml-2"></i>
            </button>
            <button type="submit" id="save-product-btn" name="action" value="update_product" class="hidden px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-save mr-2"></i> Ürünü Kaydet
            </button>
        </div>
    </form>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
<script>
    // Pass PHP variables to JavaScript
    const VENDOR_DATA = {
        productId: <?= json_encode($product_id) ?>
    };
</script>
<script src="assets/js/product-edit.js"></script>