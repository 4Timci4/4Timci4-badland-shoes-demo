<?php
/**
 * Favori Raporları
 * En çok favoriye alınan ürün ve varyantları gösteren rapor sayfası
 */

require_once 'config/auth.php';
check_admin_auth();

// Sayfa bilgileri
$page_title = 'Favori Raporları';
$breadcrumb_items = [
    ['title' => 'Favori Raporları', 'url' => 'favorited-products.php', 'icon' => 'fas fa-heart']
];

// Favori verilerini al
$favorited_products = get_favorited_variants_summary();

// Gerekli CSS ve JS
$additional_css = [
    'https://cdn.datatables.net/1.11.5/css/dataTables.tailwind.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'
];
$additional_js = [
    // jQuery ve DataTables için gerekli dosyalar
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.11.5/js/dataTables.tailwind.min.js',
    'https://cdn.jsdelivr.net/npm/apexcharts',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// İstatistik verileri
$total_products = count($favorited_products);
$total_favorites = 0;
$top_product_name = $favorited_products[0]['product_name'] ?? '-';
$top_product_favorites = $favorited_products[0]['total_favorites'] ?? 0;

foreach ($favorited_products as $product) {
    $total_favorites += $product['total_favorites'];
}

// Header dahil et
include 'includes/header.php';
?>

<!-- Favorited Products Content -->
<div class="space-y-8 animate__animated animate__fadeIn">
    <!-- Dashboard Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Products Card -->
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl p-6 shadow-lg overflow-hidden relative">
            <div class="absolute right-0 bottom-0 opacity-10">
                <i class="fas fa-box-open text-8xl"></i>
            </div>
            <div class="relative z-10">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-heart text-white"></i>
                    </div>
                    <h3 class="text-lg font-medium text-white/90">Favorilenen Ürünler</h3>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-bold text-white"><?= $total_products ?></p>
                        <p class="text-xs text-white/70 mt-1">Toplam ürün</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Favorites Card -->
        <div class="bg-gradient-to-br from-pink-500 to-rose-700 rounded-2xl p-6 shadow-lg overflow-hidden relative">
            <div class="absolute right-0 bottom-0 opacity-10">
                <i class="fas fa-users text-8xl"></i>
            </div>
            <div class="relative z-10">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-thumbs-up text-white"></i>
                    </div>
                    <h3 class="text-lg font-medium text-white/90">Toplam Favoriler</h3>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-bold text-white"><?= $total_favorites ?></p>
                        <p class="text-xs text-white/70 mt-1">Toplam favori sayısı</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Product Card -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-700 rounded-2xl p-6 shadow-lg overflow-hidden relative">
            <div class="absolute right-0 bottom-0 opacity-10">
                <i class="fas fa-trophy text-8xl"></i>
            </div>
            <div class="relative z-10">
                <div class="flex items-center mb-2">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                    <h3 class="text-lg font-medium text-white/90">En Popüler Ürün</h3>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xl font-bold text-white truncate" style="max-width: 180px;"><?= htmlspecialchars($top_product_name) ?></p>
                        <p class="text-xs text-white/70 mt-1"><?= $top_product_favorites ?> favori</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Instructions Card -->
    <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100">
        <div class="flex items-start space-x-6">
            <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-chart-pie text-indigo-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Favori Analizi</h3>
                <p class="text-gray-600 leading-relaxed">
                    Bu dashboard, kullanıcıların favori listelerine eklediği ürün ve varyantları analiz etmenizi sağlar.
                    Hangi ürünlerin daha popüler olduğunu, hangi renk ve bedenlerin daha çok talep gördüğünü kolayca tespit edebilirsiniz.
                    Verileri istediğiniz sütuna göre sıralayabilir, filtreleyebilir ve arama yapabilirsiniz.
                </p>
                <div class="mt-4 flex space-x-4">
                    <button class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium transition-colors flex items-center">
                        <i class="fas fa-file-export mr-2"></i> Raporu Dışa Aktar
                    </button>
                    <button class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium transition-colors flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i> Verileri Yenile
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-heart text-rose-500 mr-3"></i>
                    En Çok Favorilenen Ürünler
                </h2>
                <p class="text-gray-500 mt-2">Kullanıcılar tarafından en çok favorilenen ürünler ve varyantlar</p>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="relative">
                    <input type="text" placeholder="Ürün ara..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table id="favorited-products-table" class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam Favori</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">En Popüler Varyantlar</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($favorited_products)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-heart-broken text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="text-lg font-medium text-gray-700 mb-2">Henüz favorilere eklenen ürün bulunmuyor</p>
                                    <p class="text-gray-500 max-w-sm">Kullanıcılar ürünleri favorilere ekledikçe burada görüntülenecekler.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($favorited_products as $index => $product): ?>
                            <tr class="hover:bg-gray-50 transition-colors <?= $index < 3 ? 'relative overflow-hidden' : '' ?>">
                                <?php if ($index < 3): ?>
                                    <div class="absolute left-0 top-0 bottom-0 w-1 <?= $index === 0 ? 'bg-amber-400' : ($index === 1 ? 'bg-gray-400' : 'bg-amber-700') ?>"></div>
                                <?php endif; ?>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($index < 3): ?>
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 <?= $index === 0 ? 'bg-amber-100 text-amber-600' : ($index === 1 ? 'bg-gray-100 text-gray-600' : 'bg-amber-50 text-amber-700') ?>">
                                                <i class="fas <?= $index === 0 ? 'fa-trophy' : ($index === 1 ? 'fa-medal' : 'fa-award') ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="<?= $index < 3 ? '' : 'ml-4' ?>">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['product_name']) ?></div>
                                            <div class="text-xs text-gray-500 flex items-center">
                                                <span class="bg-gray-100 text-gray-600 rounded px-1.5 py-0.5">ID: <?= $product['product_id'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 flex items-center justify-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-rose-600 h-2.5 rounded-full" style="width: <?= min(100, ($product['total_favorites'] / max(1, $top_product_favorites)) * 100) ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-lg font-bold text-rose-600"><?= $product['total_favorites'] ?></div>
                                            <div class="text-xs text-gray-500">kişi tarafından favorilendi</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="grid grid-cols-1 gap-3">
                                        <?php
                                        // En fazla 3 popüler varyantı göster
                                        $top_variants = array_slice($product['variants'], 0, 3);
                                        foreach ($top_variants as $variant):
                                        ?>
                                            <div class="flex items-center p-2 rounded-lg border border-gray-100 hover:border-gray-200 hover:shadow-sm transition">
                                                <div class="relative">
                                                    <div class="w-8 h-8 rounded-md border border-gray-200 shadow-sm" style="background-color: <?= htmlspecialchars($variant['color_hex']) ?>"></div>
                                                    <div class="absolute -top-2 -right-2 w-5 h-5 bg-indigo-600 rounded-full text-white flex items-center justify-center text-xs font-bold"><?= min(99, $variant['favorite_count']) ?></div>
                                                </div>
                                                <div class="flex-1 ml-3">
                                                    <div class="text-sm font-medium text-gray-900 flex items-center">
                                                        <?= htmlspecialchars($variant['color_name']) ?> / <?= htmlspecialchars($variant['size_name']) ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 flex items-center">
                                                        <i class="fas fa-users text-gray-400 mr-1"></i>
                                                        <?= $variant['favorite_count'] ?> favori
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if (count($product['variants']) > 3): ?>
                                            <button class="text-sm text-indigo-600 hover:text-indigo-800 transition flex items-center mt-1 p-2 hover:bg-indigo-50 rounded-lg">
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                <?= count($product['variants']) - 3 ?> varyant daha
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Ürün detayları">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="p-2 text-gray-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Ürün düzenle">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="p-2 text-gray-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Rapor çıkar">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Initialize DataTable -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery !== 'undefined') {
            $('#favorited-products-table').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/tr.json'
                },
                pageLength: 10,
                order: [[1, 'desc']], // Toplam favori sayısına göre sırala
                columnDefs: [
                    { orderable: true, targets: [0, 1] },
                    { orderable: false, targets: [2, 3] }
                ],
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"<"flex-1 mb-2 md:mb-0"f><"flex items-center"l<"ml-2"p>>>rtip',
                initComplete: function() {
                    // Tablo başlığına özel stil ekleyelim
                    $('.dataTables_wrapper .dataTables_filter input').addClass('border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent');
                    $('.dataTables_wrapper .dataTables_length select').addClass('border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent');
                    
                    // DataTables eklentisinin oluşturduğu araçları özelleştirelim
                    $('.dataTables_paginate .paginate_button').addClass('px-3 py-1 mx-1 border border-gray-200 rounded hover:bg-indigo-50');
                    $('.dataTables_paginate .paginate_button.current').addClass('bg-indigo-500 text-white border-indigo-500 hover:bg-indigo-600');
                }
            });
        } else {
            console.error('jQuery not loaded. DataTables cannot be initialized.');
            // Tüm düğmelere hover efekti ekleyelim
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.classList.add('scale-105');
                    this.style.transition = 'all 0.2s ease';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.classList.remove('scale-105');
                });
            });
            
            // Favoriler için grafik ekleyelim
            if (typeof ApexCharts !== 'undefined') {
                const chartContainer = document.createElement('div');
                chartContainer.className = 'bg-white rounded-2xl p-6 shadow-xl border border-gray-100 mt-8';
                chartContainer.innerHTML = `
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Favori Trendleri</h2>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-sm hover:bg-indigo-100 transition">Haftalık</button>
                            <button class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Aylık</button>
                            <button class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-sm hover:bg-indigo-100 transition">Yıllık</button>
                        </div>
                    </div>
                    <div id="favoriteChart" style="height: 350px;"></div>
                `;
                
                document.querySelector('.space-y-8').appendChild(chartContainer);
                
                const chartOptions = {
                    series: [{
                        name: 'Favoriler',
                        data: [31, 40, 28, 51, 42, 109, 100, 120, 80, 95, 110, 150]
                    }],
                    chart: {
                        height: 350,
                        type: 'area',
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'Inter, sans-serif'
                    },
                    title: {
                        text: 'Aylık Favori İstatistikleri',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#263238'
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                            stops: [0, 90, 100],
                            colorStops: [
                                {
                                    offset: 0,
                                    color: '#4F46E5',
                                    opacity: 0.8
                                },
                                {
                                    offset: 100,
                                    color: '#C026D3',
                                    opacity: 0.2
                                }
                            ]
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3,
                        colors: ['#4F46E5']
                    },
                    xaxis: {
                        categories: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
                    },
                    markers: {
                        size: 5,
                        colors: ['#4F46E5'],
                        strokeColors: '#fff',
                        strokeWidth: 2,
                        hover: {
                            size: 7,
                        }
                    },
                    tooltip: {
                        theme: 'dark'
                    }
                };
                
                setTimeout(() => {
                    const chart = new ApexCharts(document.querySelector('#favoriteChart'), chartOptions);
                    chart.render();
                }, 300);
            }
        }
    });
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>