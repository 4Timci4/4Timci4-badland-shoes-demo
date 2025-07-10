<?php
/**
 * Ürün Ekleme/Düzenleme Sayfası
 * Modern, kullanıcı dostu ürün formu
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';
require_once '../services/ProductService.php';
require_once '../services/CategoryService.php';
require_once '../services/GenderService.php';
require_once '../services/VariantService.php';
require_once '../services/Product/ProductImageService.php';
require_once 'includes/product-edit-helpers.php';

// Sayfa sadece ekleme modu için
$edit_mode = false;
$product_id = 0;
$product = null;

// Sayfa bilgileri
$page_title = 'Yeni Ürün Ekle';
$breadcrumb_items = [
    ['title' => 'Ürün Yönetimi', 'url' => 'products.php', 'icon' => 'fas fa-box'],
    ['title' => $page_title, 'url' => '#', 'icon' => 'fas fa-plus']
];

// Form işleme
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        // Form verilerini al
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category_ids = $_POST['category_ids'] ?? []; // Çoklu kategori seçimi
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $features = trim($_POST['features'] ?? '');
        $gender_ids = $_POST['gender_ids'] ?? []; // Çoklu cinsiyet seçimi
        
        // Validation
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Ürün adı zorunludur.';
        }
        
        if (empty($description)) {
            $errors[] = 'Ürün açıklaması zorunludur.';
        }
        
        if (empty($category_ids) || !is_array($category_ids)) {
            $errors[] = 'En az bir kategori seçiniz.';
        }
        
        if (empty($errors)) {
            try {
                // Artık category_id olmayacak - sadece çoklu kategori sistemi
                $product_data = [
                    'name' => $name,
                    'description' => $description,
                    'is_featured' => $is_featured,
                    'features' => $features
                ];
                
                // Yeni ürün ekleme
                try {
                    $db = database();
                    // 1. Önce ürünü ekle ve ID'sini geri al
                    $new_product_array = $db->insert('product_models', $product_data, ['returning' => 'representation']);
                    
                    if ($new_product_array && !empty($new_product_array)) {
                        // 2. Yeni ürünün ID'sini al
                        $new_product = $new_product_array[0] ?? null;
                        $new_product_id = $new_product['id'] ?? null;
                        
                        if ($new_product_id) {
                            // 3. Kategori ilişkilerini ekle
                            foreach ($category_ids as $category_id) {
                                $category_data = [
                                    'product_id' => $new_product_id,
                                    'category_id' => intval($category_id)
                                ];
                                $db->insert('product_categories', $category_data);
                            }
                            
                            // 4. Cinsiyet ilişkilerini ekle
                            foreach ($gender_ids as $gender_id) {
                                $gender_data = [
                                    'product_id' => $new_product_id,
                                    'gender_id' => intval($gender_id)
                                ];
                                $db->insert('product_genders', $gender_data);
                            }
                            
                            set_flash_message('success', 'Ürün başarıyla eklendi! Şimdi renk/beden varyantlarını ekleyebilirsiniz.');
                            header('Location: product-edit.php?id=' . $new_product_id);
                        } else {
                            set_flash_message('success', 'Ürün başarıyla eklendi.');
                            header('Location: products.php');
                        }
                        exit;
                    } else {
                        throw new Exception('Ürün ekleme başarısız');
                    }
                } catch (Exception $e) {
                    error_log("Product add error: " . $e->getMessage());
                    $errors[] = 'Ürün eklenirken bir hata oluştu: ' . $e->getMessage();
                }
            } catch (Exception $e) {
                error_log("Product save error: " . $e->getMessage());
                $errors[] = 'Sistem hatası oluştu. Lütfen tekrar deneyin.';
            }
        }
        
        // Hataları flash message olarak sakla
        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
        }
    }
}

// Kategorileri ve cinsiyetleri getir
$categories = category_service()->getCategoriesWithProductCountsOptimized();
$genders = gender_service()->getAllGenders();

// Image service'i initialize et
$imageService = new ProductImageService();

// Header dahil et
include 'includes/header.php';
?>

<!-- Product Add Wizard -->
<div class="wizard-container bg-gray-50 p-4 sm:p-6 lg:p-8 rounded-2xl">
    <?php include 'views/product-edit/header-section.php'; ?>
    <?php render_flash_message(); ?>

    <!-- Wizard Header -->
    <div class="wizard-header mb-8 p-4 bg-white rounded-xl shadow border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800" id="wizard-step-title">Adım 1: Temel Bilgiler</h3>
            <div class="text-sm font-medium text-gray-500">
                <span id="wizard-current-step">1</span> / <span id="wizard-total-steps">2</span>
            </div>
        </div>
        <div class="progress-bar w-full bg-gray-200 rounded-full h-2.5">
            <div id="wizard-progress" class="bg-blue-600 h-2.5 rounded-full" style="width: 50%"></div>
        </div>
    </div>

    <!-- Product Add Form -->
    <form method="POST" class="space-y-8" id="productEditForm">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <!-- Step 1: Basic Info -->
        <div class="wizard-step" data-step="1">
            <?php include 'views/product-edit/basic-info-form.php'; ?>
        </div>

        <!-- Step 2: Category & Status -->
        <div class="wizard-step hidden" data-step="2">
            <?php include 'views/product-edit/category-pricing-form.php'; ?>
            <?php include 'views/product-edit/product-status-form.php'; ?>
        </div>

        <!-- Wizard Navigation -->
        <div class="wizard-navigation pt-6 border-t flex justify-between items-center">
            <button type="button" id="prev-step-btn" class="px-6 py-2 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50" disabled>
                <i class="fas fa-arrow-left mr-2"></i> Önceki
            </button>
            <button type="button" id="next-step-btn" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                Sonraki <i class="fas fa-arrow-right ml-2"></i>
            </button>
            <button type="submit" id="save-product-btn" name="action" value="add_product" class="hidden px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-plus mr-2"></i> Ürünü Oluştur ve Devam Et
            </button>
        </div>
    </form>
</div>

<script src="assets/js/product-edit.js"></script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
