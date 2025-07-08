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

// Düzenleme modu kontrolü
$edit_mode = isset($_GET['id']) && !empty($_GET['id']);
$product_id = $edit_mode ? intval($_GET['id']) : 0;
$product = null;

if ($edit_mode) {
    $product_data = get_product_model($product_id);
    if (empty($product_data)) {
        set_flash_message('error', 'Ürün bulunamadı.');
        header('Location: products.php');
        exit;
    }
    $product = $product_data[0];
}

// Sayfa bilgileri
$page_title = $edit_mode ? 'Ürün Düzenle' : 'Yeni Ürün Ekle';
$breadcrumb_items = [
    ['title' => 'Ürün Yönetimi', 'url' => 'products.php', 'icon' => 'fas fa-box'],
    ['title' => $page_title, 'url' => '#', 'icon' => $edit_mode ? 'fas fa-edit' : 'fas fa-plus']
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
        $base_price = floatval($_POST['base_price'] ?? 0);
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
        
        if ($base_price <= 0) {
            $errors[] = 'Geçerli bir fiyat giriniz.';
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
                    'base_price' => $base_price,
                    'is_featured' => $is_featured,
                    'features' => $features
                ];
                
                if ($edit_mode) {
                    // Güncelleme işlemi
                    try {
                        // 1. Ürün bilgilerini güncelle
                        $response = supabase()->request('product_models?id=eq.' . $product_id, 'PATCH', $product_data);
                        if ($response && !empty($response['body'])) {
                            
                            // 2. Mevcut kategori ilişkilerini sil
                            $delete_response = supabase()->request('product_categories?product_id=eq.' . $product_id, 'DELETE');
                            
                            // 3. Yeni kategori ilişkilerini ekle
                            foreach ($category_ids as $category_id) {
                                $category_data = [
                                    'product_id' => $product_id,
                                    'category_id' => intval($category_id)
                                ];
                                supabase()->request('product_categories', 'POST', $category_data);
                            }
                            
                            set_flash_message('success', 'Ürün başarıyla güncellendi.');
                            header('Location: products.php');
                            exit;
                        } else {
                            throw new Exception('Ürün güncelleme başarısız');
                        }
                    } catch (Exception $e) {
                        error_log("Product update error: " . $e->getMessage());
                        $errors[] = 'Ürün güncellenirken bir hata oluştu: ' . $e->getMessage();
                    }
                } else {
                    // Yeni ürün ekleme
                    try {
                        // 1. Önce ürünü ekle
                        $response = supabase()->request('product_models', 'POST', $product_data);
                        if ($response && !empty($response['body'])) {
                            // 2. Yeni ürünün ID'sini al
                            $new_product = $response['body'][0] ?? null;
                            $new_product_id = $new_product['id'] ?? null;
                            
                            if ($new_product_id) {
                                // 3. Kategori ilişkilerini ekle
                                foreach ($category_ids as $category_id) {
                                    $category_data = [
                                        'product_id' => $new_product_id,
                                        'category_id' => intval($category_id)
                                    ];
                                    supabase()->request('product_categories', 'POST', $category_data);
                                }
                                
                                // 4. Cinsiyet ilişkilerini ekle
                                foreach ($gender_ids as $gender_id) {
                                    $gender_data = [
                                        'product_id' => $new_product_id,
                                        'gender_id' => intval($gender_id)
                                    ];
                                    supabase()->request('product_genders', 'POST', $gender_data);
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
$categories = category_service()->getAllCategories();
$genders = gender_service()->getAllGenders();

// Image service'i initialize et
$imageService = new ProductImageService();

// Header dahil et
include 'includes/header.php';
?>

<!-- Product Form Content -->
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <?= $edit_mode ? 'Ürün Düzenle' : 'Yeni Ürün Ekle' ?>
            </h1>
            <p class="text-gray-600">
                <?= $edit_mode ? 'Mevcut ürün bilgilerini güncelleyin' : 'Yeni bir ürün oluşturun ve stoka ekleyin' ?>
            </p>
        </div>
        <div class="mt-4 lg:mt-0">
            <a href="products.php" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Ürün Listesine Dön
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php
    $flash_message = get_flash_message();
    if ($flash_message):
        $bg_color = $flash_message['type'] === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
        $text_color = $flash_message['type'] === 'success' ? 'text-green-800' : 'text-red-800';
        $icon = $flash_message['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        $icon_color = $flash_message['type'] === 'success' ? 'text-green-500' : 'text-red-500';
    ?>
        <div class="<?= $bg_color ?> border rounded-xl p-4 flex items-start">
            <i class="fas <?= $icon ?> <?= $icon_color ?> mr-3 mt-0.5"></i>
            <div class="<?= $text_color ?> font-medium"><?= $flash_message['message'] ?></div>
        </div>
    <?php endif; ?>

    <!-- Product Form -->
    <form method="POST" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <!-- Basic Information Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Temel Bilgiler</h3>
                <p class="text-gray-600 text-sm">Ürünün temel bilgilerini girin</p>
            </div>
            <div class="p-6 space-y-6">
                
                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2"></i>Ürün Adı *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required
                           value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                           placeholder="Örn: Nike Air Max 270"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-2"></i>Ürün Açıklaması *
                    </label>
                    <textarea id="description" 
                              name="description" 
                              required
                              rows="4"
                              placeholder="Ürününüzün detaylı açıklamasını yazın..."
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <!-- Features -->
                <div>
                    <label for="features" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-list mr-2"></i>Özellikler
                    </label>
                    <textarea id="features" 
                              name="features" 
                              rows="3"
                              placeholder="Ürün özelliklerini listeleyin (her satıra bir özellik)..."
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($product['features'] ?? '') ?></textarea>
                    <p class="text-xs text-gray-500 mt-2">Her satıra bir özellik yazın. Örn: "Su geçirmez", "Nefes alabilir kumaş"</p>
                </div>
            </div>
        </div>

        <!-- Category and Pricing Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Kategori ve Fiyatlandırma</h3>
                <p class="text-gray-600 text-sm">Ürün kategorisi ve fiyat bilgilerini ayarlayın</p>
            </div>
            <div class="p-6 space-y-6">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Multi-Category Selection -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-box mr-2"></i>Ürün Kategorileri *
                        </label>
                        <p class="text-xs text-gray-500 mb-4">Ürününüzün tipini seçin (örn: Sneaker, Bot, Sandalet, vb.)</p>
                        
                        <?php 
                        // Düzenleme modunda mevcut kategorileri al
                        $selected_categories = [];
                        if ($edit_mode && $product_id) {
                            try {
                                $response = supabase()->request('product_categories?select=category_id&product_id=eq.' . $product_id);
                                $category_relations = $response['body'] ?? [];
                                $selected_categories = array_column($category_relations, 'category_id');
                            } catch (Exception $e) {
                                error_log("Error fetching product categories: " . $e->getMessage());
                            }
                        }
                        ?>
                        
                        <div class="space-y-6">
                            <div class="border border-gray-200 rounded-xl p-4">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-box text-blue-500 mr-2"></i>
                                    Ürün Tipleri
                                    <span class="ml-2 text-xs text-gray-500">(<?= count($categories) ?> kategori)</span>
                                </h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <?php foreach ($categories as $category): ?>
                                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                            <input type="checkbox" 
                                                   name="category_ids[]" 
                                                   value="<?= htmlspecialchars($category['id']) ?>"
                                                   <?= in_array($category['id'], $selected_categories) ? 'checked' : '' ?>
                                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                            <span class="ml-2 text-sm font-medium text-gray-700">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Selected Categories Preview -->
                        <div id="selected-categories-preview" class="mt-4 p-3 bg-gray-50 rounded-lg hidden">
                            <p class="text-sm font-medium text-gray-700 mb-2">Seçilen Kategoriler:</p>
                            <div id="selected-categories-list" class="flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                    
                    <!-- Gender Selection -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-venus-mars mr-2"></i>Cinsiyetler *
                        </label>
                        <p class="text-xs text-gray-500 mb-4">Ürününüzün hitap ettiği cinsiyetleri seçin (örn: Erkek, Kadın, Çocuk, Unisex)</p>
                        
                        <?php 
                        // Düzenleme modunda mevcut cinsiyetleri al
                        $selected_genders = [];
                        if ($edit_mode && $product_id) {
                            try {
                                $response = supabase()->request('product_genders?select=gender_id&product_id=eq.' . $product_id);
                                $gender_relations = $response['body'] ?? [];
                                $selected_genders = array_column($gender_relations, 'gender_id');
                            } catch (Exception $e) {
                                error_log("Error fetching product genders: " . $e->getMessage());
                            }
                        }
                        ?>
                        
                        <div class="border border-gray-200 rounded-xl p-4">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-venus-mars text-purple-500 mr-2"></i>
                                Cinsiyetler
                                <span class="ml-2 text-xs text-gray-500">(<?= count($genders) ?> cinsiyet)</span>
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                <?php foreach ($genders as $gender): ?>
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                        <input type="checkbox" 
                                               name="gender_ids[]" 
                                               value="<?= htmlspecialchars($gender['id']) ?>"
                                               <?= in_array($gender['id'], $selected_genders) ? 'checked' : '' ?>
                                               class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2">
                                        <span class="ml-2 text-sm font-medium text-gray-700">
                                            <?= htmlspecialchars($gender['name']) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Selected Genders Preview -->
                        <div id="selected-genders-preview" class="mt-4 p-3 bg-gray-50 rounded-lg hidden">
                            <p class="text-sm font-medium text-gray-700 mb-2">Seçilen Cinsiyetler:</p>
                            <div id="selected-genders-list" class="flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                    <!-- Base Price -->
                    <div>
                        <label for="base_price" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lira-sign mr-2"></i>Fiyat (₺) *
                        </label>
                        <input type="number" 
                               id="base_price" 
                               name="base_price" 
                               required
                               min="0" 
                               step="0.01"
                               value="<?= htmlspecialchars($product['base_price'] ?? '') ?>"
                               placeholder="0.00"
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Status Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Ürün Durumu</h3>
                <p class="text-gray-600 text-sm">Ürünün görünürlük ayarlarını yapın</p>
            </div>
            <div class="p-6">
                
                <!-- Featured Status -->
                <div class="flex items-start space-x-4">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               id="is_featured" 
                               name="is_featured" 
                               value="1"
                               <?= (isset($product['is_featured']) && $product['is_featured']) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2">
                    </div>
                    <div class="text-sm">
                        <label for="is_featured" class="font-semibold text-gray-700 cursor-pointer">
                            <i class="fas fa-star text-yellow-500 mr-2"></i>Öne Çıkarılmış Ürün
                        </label>
                        <p class="text-gray-500">Bu ürün ana sayfada öne çıkarılmış ürünler bölümünde görünecektir.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Images Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Ürün Görselleri</h3>
                <p class="text-gray-600 text-sm">Ürün eklendikten sonra renk varyantlarına özel görseller ekleyebileceksiniz</p>
            </div>
            <div class="p-6">
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-images text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Varyant Bazlı Görsel Yönetimi</h4>
                            <p class="text-gray-600 mb-4">
                                Ürününüz başarıyla eklendikten sonra, her renk varyantı için ayrı görseller yükleyebileceksiniz.
                                Bu sayede siyah ayakkabı ile kırmızı ayakkabının farklı görsellerini müşterilerinize sunabilirsiniz.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-palette text-purple-500 mr-2"></i>
                                        <span class="font-medium text-gray-900">Renk Bazlı Görseller</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Her renk varyantı için farklı ürün görselleri</p>
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                                        <span class="font-medium text-gray-900">Ana Görsel Seçimi</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Her varyant için ana görsel belirleme</p>
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-expand-arrows-alt text-green-500 mr-2"></i>
                                        <span class="font-medium text-gray-900">Çoklu Boyut</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Otomatik thumbnail ve WebP optimizasyonu</p>
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-upload text-blue-500 mr-2"></i>
                                        <span class="font-medium text-gray-900">Kolay Yükleme</span>
                                    </div>
                                    <p class="text-sm text-gray-600">Sürükle-bırak ile hızlı görsel ekleme</p>
                                </div>
                            </div>
                            
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-lightbulb text-amber-500 mr-3 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-medium text-amber-800 mb-1">💡 İpucu:</p>
                                        <p class="text-sm text-amber-700">
                                            Ürününüzü kaydetip varyantlarını (renk/beden) ekledikten sonra,
                                            her renk için özel görseller yükleyebilirsiniz. Bu özellik ürün düzenleme sayfasında mevcuttur.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <button type="submit" 
                    class="flex-1 sm:flex-none sm:min-w-[200px] bg-primary-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-primary-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                <i class="fas <?= $edit_mode ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                <?= $edit_mode ? 'Değişiklikleri Kaydet' : 'Ürünü Ekle' ?>
            </button>
            
            <a href="products.php" 
               class="flex-1 sm:flex-none sm:min-w-[150px] bg-gray-100 text-gray-700 font-semibold py-3 px-8 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center">
                <i class="fas fa-times mr-2"></i>
                İptal
            </a>
        </div>
    </form>
</div>

<!-- Form Validation JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const nameInput = document.querySelector('#name');
    const descriptionInput = document.querySelector('#description');
    const priceInput = document.querySelector('#base_price');
    const categorySelect = document.querySelector('#category_id');
    
    // Real-time validation
    function validateField(field, condition, message) {
        const errorElement = field.parentNode.querySelector('.error-message');
        
        if (condition) {
            field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            field.classList.add('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
            if (errorElement) errorElement.remove();
        } else {
            field.classList.remove('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
            field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            
            if (!errorElement) {
                const error = document.createElement('p');
                error.className = 'error-message text-red-600 text-xs mt-1';
                error.textContent = message;
                field.parentNode.appendChild(error);
            }
        }
    }
    
    // Name validation
    nameInput.addEventListener('blur', function() {
        validateField(this, this.value.trim().length >= 2, 'Ürün adı en az 2 karakter olmalıdır.');
    });
    
    // Description validation
    descriptionInput.addEventListener('blur', function() {
        validateField(this, this.value.trim().length >= 10, 'Açıklama en az 10 karakter olmalıdır.');
    });
    
    // Price validation
    priceInput.addEventListener('blur', function() {
        const price = parseFloat(this.value);
        validateField(this, price > 0, 'Geçerli bir fiyat giriniz.');
    });
    
    // Multi-category selection handling
    const categoryCheckboxes = document.querySelectorAll('input[name="category_ids[]"]');
    const selectedPreview = document.getElementById('selected-categories-preview');
    const selectedList = document.getElementById('selected-categories-list');
    
    // Multi-gender selection handling
    const genderCheckboxes = document.querySelectorAll('input[name="gender_ids[]"]');
    const selectedGenderPreview = document.getElementById('selected-genders-preview');
    const selectedGenderList = document.getElementById('selected-genders-list');
    
    function updateCategoryPreview() {
        const selected = Array.from(categoryCheckboxes).filter(cb => cb.checked);
        
        if (selected.length > 0) {
            selectedPreview.classList.remove('hidden');
            selectedList.innerHTML = '';
            
            selected.forEach(checkbox => {
                const label = checkbox.parentNode.querySelector('span').textContent;
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
                badge.textContent = label;
                selectedList.appendChild(badge);
            });
        } else {
            selectedPreview.classList.add('hidden');
        }
    }
    
    function updateGenderPreview() {
        const selected = Array.from(genderCheckboxes).filter(cb => cb.checked);
        
        if (selected.length > 0) {
            selectedGenderPreview.classList.remove('hidden');
            selectedGenderList.innerHTML = '';
            
            selected.forEach(checkbox => {
                const label = checkbox.parentNode.querySelector('span').textContent;
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800';
                badge.textContent = label;
                selectedGenderList.appendChild(badge);
            });
        } else {
            selectedGenderPreview.classList.add('hidden');
        }
    }
    
    // Category selection validation and preview
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateCategoryPreview();
            
            // Validation: en az bir kategori seçilmeli
            const anySelected = Array.from(categoryCheckboxes).some(cb => cb.checked);
            
            if (anySelected) {
                // Remove validation errors from all category sections
                document.querySelectorAll('.category-validation-error').forEach(error => error.remove());
            }
        });
    });
    
    // Gender selection validation and preview
    genderCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateGenderPreview();
            
            // Validation: en az bir cinsiyet seçilmeli
            const anySelected = Array.from(genderCheckboxes).some(cb => cb.checked);
            
            if (anySelected) {
                // Remove validation errors from all gender sections
                document.querySelectorAll('.gender-validation-error').forEach(error => error.remove());
            }
        });
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        // Category validation
        const anyCategorySelected = Array.from(categoryCheckboxes).some(cb => cb.checked);
        if (!anyCategorySelected) {
            e.preventDefault();
            
            // Show error message
            const categorySection = document.querySelector('input[name="category_ids[]"]').closest('.lg\\:col-span-2');
            const existingError = categorySection.querySelector('.category-validation-error');
            
            if (!existingError) {
                const error = document.createElement('p');
                error.className = 'category-validation-error text-red-600 text-sm mt-2 font-medium';
                error.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>En az bir kategori seçmelisiniz!';
                categorySection.appendChild(error);
            }
            
            // Scroll to category section
            categorySection.scrollIntoView({ behavior: 'smooth' });
            return false;
        }
        
        // Gender validation
        const anyGenderSelected = Array.from(genderCheckboxes).some(cb => cb.checked);
        if (!anyGenderSelected) {
            e.preventDefault();
            
            // Show error message
            const genderSection = document.querySelector('input[name="gender_ids[]"]').closest('.lg\\:col-span-2');
            const existingError = genderSection.querySelector('.gender-validation-error');
            
            if (!existingError) {
                const error = document.createElement('p');
                error.className = 'gender-validation-error text-red-600 text-sm mt-2 font-medium';
                error.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>En az bir cinsiyet seçmelisiniz!';
                genderSection.appendChild(error);
            }
            
            // Scroll to gender section
            genderSection.scrollIntoView({ behavior: 'smooth' });
            return false;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const btnText = submitBtn.innerHTML;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Kaydediliyor...';
        
        // Re-enable after 5 seconds as fallback
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        }, 5000);
    });
    
    // Initialize previews on page load
    updateCategoryPreview();
    updateGenderPreview();
    
    // Auto-resize textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
});
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
