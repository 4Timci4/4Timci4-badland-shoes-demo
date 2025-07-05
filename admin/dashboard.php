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

// Son aktiviteleri al
$recent_activities = get_recent_activities(4);

// Grafik verileri al
$chart_data = get_monthly_chart_data();

// Gerekli CSS ve JS
$additional_css = [
    'https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.css'
];

$additional_js = [
    'https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js'
];

// Header dahil et
include 'includes/header.php';
?>

<!-- Dashboard Content -->
<div class="space-y-8">
    
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl p-8 text-white shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-6 lg:mb-0">
                <h1 class="text-3xl font-bold mb-3 flex items-center">
                    <i class="fas fa-hand-wave mr-3 text-yellow-300"></i>
                    Hoş geldiniz, <?= htmlspecialchars(get_admin_info()['username']) ?>!
                </h1>
                <p class="text-white/80 text-lg">Admin paneline hoş geldiniz. İşte sitenizin genel durumu.</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                <div class="text-white/70 text-sm mb-1">Bugün</div>
                <div class="text-2xl font-bold mb-1"><?= date('d.m.Y') ?></div>
                <div class="text-white/70 text-sm"><?= date('H:i') ?></div>
            </div>
        </div>
    </div>

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

        <!-- Messages Card -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-envelope text-yellow-600 text-xl"></i>
                </div>
                <span class="bg-red-100 text-red-700 text-xs px-3 py-1 rounded-full font-semibold">
                    <?= $stats['pending_messages'] ?> Beklemede
                </span>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-gray-900 mb-1"><?= number_format($stats['total_messages']) ?></h3>
                <p class="text-gray-600 font-medium">Mesajlar</p>
                <div class="flex items-center mt-3 text-red-600 text-sm">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <span class="font-semibold">Beklemede</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Activity Section -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Monthly Statistics Chart -->
        <div class="xl:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Aylık İstatistikler</h3>
                        <p class="text-gray-600 text-sm mt-1">Son 12 ayın performans özeti</p>
                    </div>
                    <div class="relative">
                        <select class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option>Son 6 Ay</option>
                            <option selected>Son 12 Ay</option>
                            <option>Bu Yıl</option>
                        </select>
                    </div>
                </div>
                <div class="p-6">
                    <div id="monthlyStatsChart" class="h-80"></div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 mb-1">Son Aktiviteler</h3>
                <p class="text-gray-600 text-sm">Sistem üzerindeki son işlemler</p>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-<?= $activity['color'] ?>-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas <?= $activity['icon'] ?> text-<?= $activity['color'] ?>-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 mb-1"><?= htmlspecialchars($activity['title']) ?></h4>
                                    <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($activity['description']) ?></p>
                                    <span class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded-full">
                                        <?php
                                        $time_diff = time() - strtotime($activity['created_at']);
                                        if ($time_diff < 3600) {
                                            echo floor($time_diff / 60) . ' dakika önce';
                                        } elseif ($time_diff < 86400) {
                                            echo floor($time_diff / 3600) . ' saat önce';
                                        } else {
                                            echo floor($time_diff / 86400) . ' gün önce';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-clock text-gray-400 text-3xl mb-4"></i>
                            <p class="text-gray-500">Henüz aktivite bulunmuyor</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <a href="activity-log.php" class="text-primary-600 hover:text-primary-700 font-medium text-sm flex items-center">
                        Tüm aktiviteleri gör
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
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

                <a href="messages.php" class="group block p-6 border-2 border-dashed border-yellow-200 rounded-xl hover:border-yellow-400 hover:bg-yellow-50 transition-all duration-300">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-envelope text-yellow-600 text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-900 mb-2">Mesajlar</h4>
                        <p class="text-gray-600 text-sm">Gelen mesajları görüntüle</p>
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
                                    <?php if ($product['is_featured']): ?>
                                        <span class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center shadow-md">
                                            <i class="fas fa-star text-white text-xs"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 mb-1 truncate"><?= htmlspecialchars($product['name']) ?></h4>
                                    <p class="text-gray-600 text-sm mb-2">
                                        <?= htmlspecialchars($product['categories']['name'] ?? 'Kategorisiz') ?> • Stokta: <?= htmlspecialchars($product['stock']) ?> adet
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-bold text-primary-600">₺<?= number_format($product['price'], 2) ?></span>
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
            <div class="p-6">
                <div class="space-y-6">
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

<!-- ApexCharts Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ApexCharts !== 'undefined') {
        const chartOptions = {
            series: [
                {
                    name: 'Ürünler',
                    data: <?= json_encode($chart_data['products']) ?>
                },
                {
                    name: 'Blog Yazıları',
                    data: <?= json_encode($chart_data['blogs']) ?>
                },
                {
                    name: 'Mesajlar',
                    data: <?= json_encode($chart_data['messages']) ?>
                }
            ],
            chart: {
                height: 320,
                type: 'area',
                toolbar: {
                    show: false
                },
                background: 'transparent',
                fontFamily: 'Inter, system-ui, sans-serif'
            },
            colors: ['#7367f0', '#28c76f', '#ff9f43'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 3,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            xaxis: {
                categories: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
                labels: {
                    style: {
                        colors: '#64748b',
                        fontSize: '12px',
                        fontWeight: 500
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748b',
                        fontSize: '12px',
                        fontWeight: 500
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                fontFamily: 'Inter, system-ui, sans-serif',
                fontWeight: 500,
                labels: {
                    colors: '#374151'
                },
                markers: {
                    width: 8,
                    height: 8,
                    radius: 4
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'light',
                style: {
                    fontFamily: 'Inter, system-ui, sans-serif'
                }
            }
        };
        
        const chart = new ApexCharts(document.querySelector('#monthlyStatsChart'), chartOptions);
        chart.render();
    }
});
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
