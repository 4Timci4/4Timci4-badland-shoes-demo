<?php
/**
 * Ürün Düzenleme Sayfası
 * Modern, kullanıcı dostu ürün düzenleme formu
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';
require_once '../services/ProductService.php';
require_once '../services/CategoryService.php';
require_once '../services/GenderService.php';
require_once '../services/VariantService.php';

// ID parametresi zorunlu kontrolü
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error', 'Düzenlenecek ürün ID\'si belirtilmedi.');
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);

// Ürün bilgilerini getir
$product_data = get_product_model($product_id);
if (empty($product_data)) {
    set_flash_message('error', 'Ürün bulunamadı.');
    header('Location: products.php');
    exit;
}

$product = $product_data[0];

// Sayfa bilgileri
$page_title = 'Ürün Düzenle: ' . htmlspecialchars($product['name']);
$breadcrumb_items = [
    ['title' => 'Ürün Yönetimi', 'url' => 'products.php', 'icon' => 'fas fa-box'],
    ['title' => htmlspecialchars($product['name']), 'url' => '#', 'icon' => 'fas fa-tag'],
    ['title' => 'Düzenle', 'url' => '#', 'icon' => 'fas fa-edit']
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
        $gender_ids = $_POST['gender_ids'] ?? []; // Çoklu cinsiyet seçimi
        $is_featured = isset($_POST['is_featured']) ? true : false;
        $features = trim($_POST['features'] ?? '');
        
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
    }
}

// Kategorileri ve cinsiyetleri getir
$categories = category_service()->getAllCategories();
$genders = gender_service()->getAllGenders();

// Header dahil et
include 'includes/header.php';
?>

<!-- Product Edit Content -->
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Ürün Düzenle
            </h1>
            <p class="text-gray-600">
                <span class="font-semibold"><?= htmlspecialchars($product['name']) ?></span> ürününün bilgilerini güncelleyin
            </p>
        </div>
        <div class="mt-4 lg:mt-0 flex space-x-3">
            <a href="products.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Ürün Listesi
            </a>
            <a href="../product-details.php?id=<?= $product_id ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 font-medium rounded-lg hover:bg-blue-200 transition-colors">
                <i class="fas fa-external-link-alt mr-2"></i>
                Önizle
            </a>
        </div>
    </div>

    <!-- Product Info Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-blue-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-box text-white text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="text-gray-600">Ürün ID: #<?= $product_id ?></p>
                <p class="text-sm text-gray-500">
                    Son güncelleme: <?= date('d.m.Y H:i', strtotime($product['updated_at'] ?? $product['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php
    $flash_message = get_flash_message();
    if ($flash_message):
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
    <?php endif; ?>

    <!-- Product Edit Form -->
    <form method="POST" class="space-y-8" id="productEditForm">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <!-- Basic Information Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Temel Bilgiler</h3>
                <p class="text-gray-600 text-sm">Ürünün temel bilgilerini güncelleyin</p>
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
                           value="<?= htmlspecialchars($product['name']) ?>"
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
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($product['description']) ?></textarea>
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
                <p class="text-gray-600 text-sm">Ürün kategorisi ve fiyat bilgilerini güncelleyin</p>
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
                        // Mevcut kategorileri al
                        $selected_categories = [];
                        try {
                            $response = supabase()->request('product_categories?select=category_id&product_id=eq.' . $product_id);
                            $category_relations = $response['body'] ?? [];
                            $selected_categories = array_column($category_relations, 'category_id');
                        } catch (Exception $e) {
                            error_log("Error fetching product categories: " . $e->getMessage());
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
                        // Mevcut cinsiyetleri al
                        $selected_genders = [];
                        try {
                            $response = supabase()->request('product_genders?select=gender_id&product_id=eq.' . $product_id);
                            $gender_relations = $response['body'] ?? [];
                            $selected_genders = array_column($gender_relations, 'gender_id');
                        } catch (Exception $e) {
                            error_log("Error fetching product genders: " . $e->getMessage());
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
                               value="<?= htmlspecialchars($product['base_price']) ?>"
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
                <p class="text-gray-600 text-sm">Ürünün görünürlük ayarlarını güncelleyin</p>
            </div>
            <div class="p-6">
                
                <!-- Featured Status -->
                <div class="flex items-start space-x-4">
                    <div class="flex items-center h-5">
                        <input type="checkbox" 
                               id="is_featured" 
                               name="is_featured" 
                               value="1"
                               <?= $product['is_featured'] ? 'checked' : '' ?>
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

        <!-- Variant Management Card -->
        <?php
        // Varyant verilerini getir
        $variants = variant_service()->getProductVariants($product_id);
        $all_colors = variant_service()->getAllColors();
        $all_sizes = variant_service()->getAllSizes();
        $total_stock = variant_service()->getTotalStock($product_id);
        ?>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Varyant Yönetimi</h3>
                        <p class="text-gray-600 text-sm">Renk/beden kombinasyonları ve stok yönetimi</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-green-600"><?= $total_stock ?></div>
                        <div class="text-xs text-gray-500">Toplam Stok</div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                
                <!-- Existing Variants -->
                <?php if (!empty($variants)): ?>
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-list mr-2"></i>Mevcut Varyantlar (<?= count($variants) ?>)
                    </h4>
                    
                    <div class="overflow-hidden border border-gray-200 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Renk</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beden</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fiyat</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($variants as $variant): ?>
                                <tr class="hover:bg-gray-50" data-variant-id="<?= $variant['id'] ?>">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded-full border border-gray-300" 
                                                 style="background-color: <?= htmlspecialchars($variant['color_hex'] ?? '#cccccc') ?>"></div>
                                            <span class="ml-3 text-sm text-gray-900"><?= htmlspecialchars($variant['color_name'] ?? 'Renk Yok') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($variant['size_value'] ?? 'Beden Yok') ?> <?= htmlspecialchars($variant['size_type'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 font-mono"><?= htmlspecialchars($variant['sku']) ?></span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <input type="number" 
                                                   value="<?= htmlspecialchars($variant['price']) ?>" 
                                                   step="0.01" 
                                                   min="0"
                                                   class="variant-price w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                   data-variant-id="<?= $variant['id'] ?>">
                                            <span class="text-xs text-gray-500">₺</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <input type="number" 
                                               value="<?= htmlspecialchars($variant['stock_quantity']) ?>" 
                                               min="0"
                                               class="variant-stock w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                               data-variant-id="<?= $variant['id'] ?>">
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                   <?= $variant['is_active'] ? 'checked' : '' ?>
                                                   class="variant-active form-checkbox h-4 w-4 text-primary-600"
                                                   data-variant-id="<?= $variant['id'] ?>">
                                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                        </label>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" 
                                                class="save-variant-btn text-green-600 hover:text-green-900 mr-3"
                                                data-variant-id="<?= $variant['id'] ?>">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <button type="button" 
                                                class="delete-variant-btn text-red-600 hover:text-red-900"
                                                data-variant-id="<?= $variant['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-8 bg-gray-50 rounded-xl">
                    <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Henüz varyant eklenmemiş</h4>
                    <p class="text-gray-500">Bu ürün için renk ve beden kombinasyonları ekleyin.</p>
                </div>
                <?php endif; ?>

                <!-- Add New Variant -->
                <div class="border-t pt-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-plus mr-2"></i>Yeni Varyant Ekle
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Renk</label>
                            <select id="new-variant-color" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Renk Seçin</option>
                                <?php foreach ($all_colors as $color): ?>
                                <option value="<?= $color['id'] ?>" data-hex="<?= $color['hex_code'] ?>">
                                    <?= htmlspecialchars($color['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Beden</label>
                            <select id="new-variant-size" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Beden Seçin</option>
                                <?php foreach ($all_sizes as $size): ?>
                                <option value="<?= $size['id'] ?>">
                                    <?= htmlspecialchars($size['size_value']) ?> <?= htmlspecialchars($size['size_type']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fiyat (₺)</label>
                            <input type="number" 
                                   id="new-variant-price" 
                                   value="<?= $product['base_price'] ?>"
                                   step="0.01" 
                                   min="0"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stok Miktarı</label>
                            <input type="number" 
                                   id="new-variant-stock" 
                                   value="0"
                                   min="0"
                                   placeholder="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="new-variant-active" checked class="form-checkbox h-4 w-4 text-primary-600">
                            <span class="ml-2 text-sm text-gray-700">Aktif varyant</span>
                        </label>
                        
                        <button type="button" 
                                id="add-variant-btn"
                                class="px-6 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Varyant Ekle
                        </button>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div class="border-t pt-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-tools mr-2"></i>Toplu İşlemler
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button type="button" 
                                id="bulk-activate-btn"
                                class="px-4 py-2 bg-green-100 text-green-700 font-medium rounded-lg hover:bg-green-200 transition-colors">
                            <i class="fas fa-check mr-2"></i>Tümünü Aktif Et
                        </button>
                        
                        <button type="button" 
                                id="bulk-deactivate-btn"
                                class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-ban mr-2"></i>Tümünü Pasif Et
                        </button>
                        
                        <button type="button" 
                                id="bulk-delete-btn"
                                class="px-4 py-2 bg-red-100 text-red-700 font-medium rounded-lg hover:bg-red-200 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Tümünü Sil
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <button type="submit" 
                    name="save_and_return"
                    class="flex-1 sm:flex-none sm:min-w-[200px] bg-primary-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-primary-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Kaydet ve Listeye Dön
            </button>
            
            <button type="submit" 
                    name="save_and_continue"
                    class="flex-1 sm:flex-none sm:min-w-[200px] bg-green-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-green-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                <i class="fas fa-check mr-2"></i>
                Kaydet ve Düzenlemeye Devam Et
            </button>
            
            <a href="products.php" 
               class="flex-1 sm:flex-none sm:min-w-[150px] bg-gray-100 text-gray-700 font-semibold py-3 px-8 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center">
                <i class="fas fa-times mr-2"></i>
                İptal
            </a>
        </div>
    </form>
</div>

<!-- Modular JavaScript Files -->
<script src="assets/js/product-edit/form-validation.js"></script>
<script src="assets/js/product-edit/variant-management.js"></script>
<script src="assets/js/product-edit/main.js"></script>

<!-- Initialize Product Edit -->
<script>
// Product Edit başlatma
const productEditApp = new ProductEditMain(<?= $product_id ?>, <?= $product['base_price'] ?>);

// Global erişim için (geriye uyumluluk)
window.productEditApp = productEditApp;
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
