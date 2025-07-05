<?php
/**
 * Ürün Yönetimi - Tüm Ürünler
 * Modern, kullanıcı dostu ürün listesi ve yönetim paneli
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';
require_once '../services/ProductService.php';
require_once '../services/CategoryService.php';

// Sayfa bilgileri
$page_title = 'Ürün Yönetimi';
$breadcrumb_items = [
    ['title' => 'Ürün Yönetimi', 'url' => '#', 'icon' => 'fas fa-box']
];

// Filtreleme ve sayfalama parametreleri
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

// POST işlemleri
if ($_POST) {
    if (isset($_POST['action'])) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!verify_csrf_token($csrf_token)) {
            set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
        } else {
            switch ($_POST['action']) {
                case 'delete':
                    $product_id = intval($_POST['product_id'] ?? 0);
                    if ($product_id > 0) {
                        if (delete_product($product_id)) {
                            set_flash_message('success', 'Ürün başarıyla silindi.');
                        } else {
                            set_flash_message('error', 'Ürün silinirken bir hata oluştu.');
                        }
                    }
                    break;
                    
                case 'toggle_featured':
                    $product_id = intval($_POST['product_id'] ?? 0);
                    $is_featured = $_POST['is_featured'] === 'true';
                    if ($product_id > 0) {
                        if (update_product_status($product_id, $is_featured)) {
                            $status_text = $is_featured ? 'öne çıkarıldı' : 'normal duruma getirildi';
                            set_flash_message('success', "Ürün başarıyla $status_text.");
                        } else {
                            set_flash_message('error', 'Ürün durumu güncellenirken bir hata oluştu.');
                        }
                    }
                    break;
            }
            
            // Redirect to prevent form resubmission
            $redirect_url = 'products.php?';
            $params = [];
            if (!empty($search)) $params[] = 'search=' . urlencode($search);
            if (!empty($category_filter)) $params[] = 'category=' . urlencode($category_filter);
            if (!empty($status_filter)) $params[] = 'status=' . urlencode($status_filter);
            if ($page > 1) $params[] = 'page=' . $page;
            
            header('Location: ' . $redirect_url . implode('&', $params));
            exit;
        }
    }
}

// Ürün ve kategori verilerini getir
$products_data = get_admin_products($limit, $offset, $search, $category_filter, $status_filter);
$products = $products_data['products'];
$total_products = $products_data['total'];
$total_pages = ceil($total_products / $limit);

$categories = category_service()->getAllCategories();

// Header dahil et
include 'includes/header.php';
?>

<!-- Products Management Content -->
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Ürün Yönetimi</h1>
            <p class="text-gray-600">Tüm ürünlerinizi görüntüleyin, düzenleyin ve yönetin</p>
        </div>
        <div class="mt-4 lg:mt-0">
            <a href="product-add.php" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                <i class="fas fa-plus mr-2"></i>
                Yeni Ürün Ekle
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
        <div class="<?= $bg_color ?> border rounded-xl p-4 flex items-center">
            <i class="fas <?= $icon ?> <?= $icon_color ?> mr-3"></i>
            <span class="<?= $text_color ?> font-medium"><?= htmlspecialchars($flash_message['message']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Filters and Search -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" class="space-y-4 lg:space-y-0 lg:flex lg:items-end lg:space-x-4">
            
            <!-- Search -->
            <div class="flex-1">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-search mr-2"></i>Ürün Ara
                </label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="Ürün adı ile arayın..."
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
            </div>

            <!-- Category Filter -->
            <div class="lg:w-48">
                <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-tags mr-2"></i>Kategori
                </label>
                <select id="category" 
                        name="category"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                    <option value="">Tüm Kategoriler</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="lg:w-48">
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-star mr-2"></i>Durum
                </label>
                <select id="status" 
                        name="status"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                    <option value="">Tüm Durumlar</option>
                    <option value="featured" <?= $status_filter === 'featured' ? 'selected' : '' ?>>Öne Çıkan</option>
                    <option value="normal" <?= $status_filter === 'normal' ? 'selected' : '' ?>>Normal</option>
                </select>
            </div>

            <!-- Search Button -->
            <div class="lg:w-auto">
                <button type="submit" 
                        class="w-full lg:w-auto px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i>
                    Filtrele
                </button>
            </div>

            <!-- Reset Button -->
            <?php if (!empty($search) || !empty($category_filter) || !empty($status_filter)): ?>
                <div class="lg:w-auto">
                    <a href="products.php" 
                       class="w-full lg:w-auto px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center">
                        <i class="fas fa-times mr-2"></i>
                        Temizle
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="flex items-center justify-between text-sm text-gray-600">
        <div>
            <span class="font-semibold"><?= number_format($total_products) ?></span> ürün bulundu
            <?php if (!empty($search)): ?>
                "<span class="font-semibold"><?= htmlspecialchars($search) ?></span>" için
            <?php endif; ?>
        </div>
        <div>
            Sayfa <span class="font-semibold"><?= $page ?></span> / <span class="font-semibold"><?= $total_pages ?></span>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <?php if (!empty($products)): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ürün</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Fiyat</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Durum</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Oluşturma</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($product['name']) ?></h4>
                                            <p class="text-sm text-gray-500">ID: #<?= $product['id'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($product['categories']['name'] ?? 'Kategorisiz') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-gray-900">₺<?= number_format($product['base_price'], 2) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline-block" onsubmit="return confirm('Ürün durumunu değiştirmek istediğinizden emin misiniz?')">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <input type="hidden" name="is_featured" value="<?= $product['is_featured'] ? 'false' : 'true' ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-colors <?= $product['is_featured'] ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' ?>">
                                            <i class="fas fa-star mr-1"></i>
                                            <?= $product['is_featured'] ? 'Öne Çıkan' : 'Normal' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-500"><?= date('d.m.Y', strtotime($product['created_at'])) ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="product-edit.php?id=<?= $product['id'] ?>" 
                                           class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium">
                                            <i class="fas fa-edit mr-1"></i>
                                            Düzenle
                                        </a>
                                        <form method="POST" class="inline-block" onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <button type="submit" 
                                                    class="inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                                                <i class="fas fa-trash mr-1"></i>
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 border-t border-gray-100">
                    <nav class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Toplam <span class="font-semibold"><?= number_format($total_products) ?></span> üründen 
                            <span class="font-semibold"><?= $offset + 1 ?></span> - <span class="font-semibold"><?= min($offset + $limit, $total_products) ?></span> arası gösteriliyor
                        </div>
                        <div class="flex items-center space-x-2">
                            <?php if ($page > 1): ?>
                                <?php
                                $prev_params = [];
                                if (!empty($search)) $prev_params[] = 'search=' . urlencode($search);
                                if (!empty($category_filter)) $prev_params[] = 'category=' . urlencode($category_filter);
                                if (!empty($status_filter)) $prev_params[] = 'status=' . urlencode($status_filter);
                                $prev_params[] = 'page=' . ($page - 1);
                                ?>
                                <a href="products.php?<?= implode('&', $prev_params) ?>" 
                                   class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                                $page_params = [];
                                if (!empty($search)) $page_params[] = 'search=' . urlencode($search);
                                if (!empty($category_filter)) $page_params[] = 'category=' . urlencode($category_filter);
                                if (!empty($status_filter)) $page_params[] = 'status=' . urlencode($status_filter);
                                if ($i > 1) $page_params[] = 'page=' . $i;
                                
                                $is_current = ($i == $page);
                            ?>
                                <a href="products.php?<?= implode('&', $page_params) ?>" 
                                   class="px-3 py-2 rounded-lg transition-colors <?= $is_current ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <?php
                                $next_params = [];
                                if (!empty($search)) $next_params[] = 'search=' . urlencode($search);
                                if (!empty($category_filter)) $next_params[] = 'category=' . urlencode($category_filter);
                                if (!empty($status_filter)) $next_params[] = 'status=' . urlencode($status_filter);
                                $next_params[] = 'page=' . ($page + 1);
                                ?>
                                <a href="products.php?<?= implode('&', $next_params) ?>" 
                                   class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </nav>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-box-open text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Ürün bulunamadı</h3>
                <p class="text-gray-600 mb-6">
                    <?php if (!empty($search) || !empty($category_filter) || !empty($status_filter)): ?>
                        Belirttiğiniz kriterlere uygun ürün bulunmuyor. Filtreleri değiştirmeyi deneyin.
                    <?php else: ?>
                        Henüz hiç ürün eklenmemiş. İlk ürününüzü ekleyerek başlayın.
                    <?php endif; ?>
                </p>
                <div class="space-x-4">
                    <?php if (!empty($search) || !empty($category_filter) || !empty($status_filter)): ?>
                        <a href="products.php" 
                           class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Filtreleri Temizle
                        </a>
                    <?php endif; ?>
                    <a href="product-add.php" 
                       class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        İlk Ürünü Ekle
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
