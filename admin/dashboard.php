<?php
/**
 * Admin Panel Dashboard
 * Ana yönetim sayfası - istatistikler ve genel bakış
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';

// Sayfa bilgileri
$page_title = 'Dashboard';
$breadcrumb_items = [];

// İstatistik verilerini al
$stats = get_dashboard_stats();

// Son eklenen ürünleri al
$recent_products = get_recent_products(3);

// Son eklenen blogları al
$recent_blogs = get_recent_blogs(3);


// Grafik verileri kaldırıldı.

// Gerekli CSS ve JS
$additional_css = [];

$additional_js = [];

// Header dahil et
include 'includes/header.php';
?>

<!-- Dashboard Content -->
<div class="space-y-8">
    

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        
        <!-- Total Products Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-box text-primary-600 text-xl"></i>
                </div>
                <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-semibold">
                    +<?= $stats['monthly_products'] ?>
                </span>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= number_format($stats['total_products']) ?></h3>
                <p class="text-gray-600 font-medium">Toplam Ürün</p>
                <div class="flex items-center mt-3 text-green-600 text-sm">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="font-semibold">Bu Ay</span>
                </div>
            </div>
        </div>

        <!-- Total Categories Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-tags text-green-600 text-xl"></i>
                </div>
                <span class="bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full font-semibold">
                    Sabit
                </span>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= number_format($stats['total_categories']) ?></h3>
                <p class="text-gray-600 font-medium">Toplam Kategori</p>
                <div class="flex items-center mt-3 text-gray-500 text-sm">
                    <i class="fas fa-minus mr-1"></i>
                    <span class="font-semibold">Değişiklik Yok</span>
                </div>
            </div>
        </div>

        <!-- Blog Posts Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-cyan-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-edit text-cyan-600 text-xl"></i>
                </div>
                <span class="bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full font-semibold">
                    +<?= $stats['monthly_blogs'] ?>
                </span>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= number_format($stats['total_blogs']) ?></h3>
                <p class="text-gray-600 font-medium">Blog Yazıları</p>
                <div class="flex items-center mt-3 text-green-600 text-sm">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span class="font-semibold">Bu Ay</span>
                </div>
            </div>
        </div>

    </div>


    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900 mb-1">Hızlı Eylemler</h3>
            <p class="text-gray-600 text-sm">Yaygın işlemler için hızlı erişim</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                
                <a href="product-add.php" class="group block p-6 border-2 border-dashed border-primary-200 rounded-xl hover:border-primary-400 hover:bg-primary-50 transition-all duration-300">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-primary-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-plus-circle text-primary-600 text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Ürün Ekle</h4>
                        <p class="text-gray-600 text-sm">Yeni ürün oluştur ve stoka ekle</p>
                    </div>
                </a>

                <a href="blogs.php" class="group block p-6 border-2 border-dashed border-green-200 rounded-xl hover:border-green-400 hover:bg-green-50 transition-all duration-300">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-edit text-green-600 text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Blog Yazısı</h4>
                        <p class="text-gray-600 text-sm">Yeni blog yazısı oluştur</p>
                    </div>
                </a>

                <a href="categories.php" class="group block p-6 border-2 border-dashed border-cyan-200 rounded-xl hover:border-cyan-400 hover:bg-cyan-50 transition-all duration-300">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-cyan-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-tags text-cyan-600 text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Kategori</h4>
                        <p class="text-gray-600 text-sm">Kategori yönet ve düzenle</p>
                    </div>
                </a>

            </div>
        </div>
    </div>

    <!-- Recent Content Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        <!-- Recent Products -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Son Eklenen Ürünler</h3>
                    <p class="text-gray-600 text-sm">En son eklenen ürünler</p>
                </div>
                <a href="products.php" class="text-primary-600 hover:text-primary-700 font-semibold text-sm flex items-center">
                    Tümünü Gör
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if (!empty($recent_products)): ?>
                    <?php foreach ($recent_products as $product): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <img src="<?= htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/64') ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="w-16 h-16 rounded-xl object-cover">
                                    <?php
                                        $status_icon = null;
                                        $time_diff = time() - strtotime($product['created_at']);
                                        $is_new = $time_diff < (86400 * 2); // 2 days

                                        if (!empty($product['is_featured'])) {
                                            $status_icon = 'fa-star';
                                            $status_bg = 'bg-yellow-500';
                                        } elseif ($is_new) {
                                            $status_icon = 'fa-check';
                                            $status_bg = 'bg-green-500';
                                        }
                                    ?>
                                    <?php if ($status_icon): ?>
                                        <span class="absolute -top-2 -right-2 w-6 h-6 <?= $status_bg ?> rounded-full flex items-center justify-center shadow-md">
                                            <i class="fas <?= $status_icon ?> text-white text-xs"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 mb-1 truncate"><?= htmlspecialchars($product['name']) ?></h4>
                                    <p class="text-gray-600 text-sm mb-2">
                                        <?= htmlspecialchars($product['categories']['name'] ?? 'Kategorisiz') ?> • Stok: Bilinmiyor
                                    </p>
                                    <div class="flex items-center justify-end">
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                            <?php
                                            $time_diff = time() - strtotime($product['created_at']);
                                            if ($time_diff < 86400) { echo 'Bugün'; } 
                                            else { echo floor($time_diff / 86400) . ' gün önce'; }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-box-open text-gray-400 text-3xl mb-4"></i>
                        <p class="text-gray-500">Henüz ürün eklenmemiş</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Blog Posts -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Son Blog Yazıları</h3>
                    <p class="text-gray-600 text-sm">En son yayınlanan yazılar</p>
                </div>
                <a href="blogs.php" class="text-primary-600 hover:text-primary-700 font-semibold text-sm flex items-center">
                    Tümünü Gör
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="p-2">
                <div class="space-y-2">
                    <?php if (!empty($recent_blogs)): ?>
                        <?php 
                            $category_colors = [
                                'Moda' => 'primary',
                                'Sağlık' => 'green',
                                'Bakım' => 'cyan',
                                'Default' => 'gray'
                            ];
                            $category_icons = [
                                'Moda' => 'fa-edit',
                                'Sağlık' => 'fa-heart',
                                'Bakım' => 'fa-tools',
                                'Default' => 'fa-file-alt'
                            ];
                        ?>
                        <?php foreach ($recent_blogs as $blog): ?>
                            <?php 
                                $color = $category_colors[$blog['category']] ?? $category_colors['Default'];
                                $icon = $category_icons[$blog['category']] ?? $category_icons['Default'];
                            ?>
                            <a href="blog-edit.php?id=<?= $blog['id'] ?>" class="block p-4 rounded-2xl hover:bg-gray-50 transition-colors">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-<?= $color ?>-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas <?= $icon ?> text-<?= $color ?>-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-gray-900 mb-2 truncate"><?= htmlspecialchars($blog['title']) ?></h4>
                                        <p class="text-gray-600 text-sm mb-3 line-clamp-2"><?= htmlspecialchars($blog['excerpt']) ?></p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-<?= $color ?>-700 bg-<?= $color ?>-50 px-2 py-1 rounded-full font-medium"><?= htmlspecialchars($blog['category']) ?></span>
                                            <span class="text-xs text-gray-500">
                                                <?php
                                                $time_diff = time() - strtotime($blog['created_at']);
                                                if ($time_diff < 3600) { echo floor($time_diff / 60) . ' dk önce'; }
                                                elseif ($time_diff < 86400) { echo floor($time_diff / 3600) . ' saat önce'; } 
                                                else { echo floor($time_diff / 86400) . ' gün önce'; }
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-gray-400 text-3xl mb-4"></i>
                            <p class="text-gray-500">Henüz blog yazısı eklenmemiş</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts Script - Kaldırıldı -->

<?php
// Footer dahil et
include 'includes/footer.php';
?>
