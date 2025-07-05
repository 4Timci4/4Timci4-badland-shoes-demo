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
        $category_id = intval($_POST['category_id'] ?? 0);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
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
        
        if ($category_id <= 0) {
            $errors[] = 'Bir kategori seçiniz.';
        }
        
        if (empty($errors)) {
            try {
                $product_data = [
                    'name' => $name,
                    'description' => $description,
                    'base_price' => $base_price,
                    'category_id' => $category_id,
                    'is_featured' => $is_featured,
                    'features' => $features
                ];
                
                if ($edit_mode) {
                    // Güncelleme işlemi (şimdilik basit placeholder)
                    $response = supabase()->request('products?id=eq.' . $product_id, 'PATCH', $product_data);
                    if ($response) {
                        set_flash_message('success', 'Ürün başarıyla güncellendi.');
                        header('Location: products.php');
                        exit;
                    } else {
                        $errors[] = 'Ürün güncellenirken bir hata oluştu.';
                    }
                } else {
                    // Yeni ürün ekleme
                    $response = supabase()->request('products', 'POST', $product_data);
                    if ($response) {
                        set_flash_message('success', 'Ürün başarıyla eklendi.');
                        header('Location: products.php');
                        exit;
                    } else {
                        $errors[] = 'Ürün eklenirken bir hata oluştu.';
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

// Kategorileri getir
$categories = category_service()->getAllCategories();

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
                    
                    <!-- Category Selection -->
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-folder mr-2"></i>Kategori *
                        </label>
                        <select id="category_id" 
                                name="category_id" 
                                required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <option value="">Kategori Seçin</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>" 
                                        <?= (isset($product['category_id']) && $product['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

        <!-- Images Card (Placeholder for Future) -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Ürün Görselleri</h3>
                <p class="text-gray-600 text-sm">Ürün görsellerini yükleyin (Yakında eklenecek)</p>
            </div>
            <div class="p-6">
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center">
                    <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-4"></i>
                    <p class="text-gray-500 font-medium mb-2">Görsel Yükleme</p>
                    <p class="text-gray-400 text-sm">Bu özellik yakında eklenecektir</p>
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
    
    // Category validation
    categorySelect.addEventListener('change', function() {
        validateField(this, this.value !== '', 'Bir kategori seçiniz.');
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const btnText = submitBtn.innerHTML;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Kaydediliyor...';
        
        // Re-enable after 3 seconds as fallback
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        }, 3000);
    });
    
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
