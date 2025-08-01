<?php


require_once 'services/AuthService.php';
$authService = new AuthService();


require_once 'services/CategoryService.php';
require_once 'services/Product/ProductApiService.php';
require_once 'services/GenderService.php';


require_once 'services/SettingsService.php';
$settingsService = new SettingsService();

$page = 1;
$limit = 12; // İlk yüklemede gösterilecek ürün sayısı
$category_filters = isset($_GET['categories']) ? (is_array($_GET['categories']) ? $_GET['categories'] : [$_GET['categories']]) : [];
$gender_filters = isset($_GET['genders']) ? (is_array($_GET['genders']) ? $_GET['genders'] : [$_GET['genders']]) : [];
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : 'created_at-desc';
$featured_filter = isset($_GET['featured']) ? (bool)$_GET['featured'] : null;


$category_service = category_service();
$product_api_service = product_api_service();




$categories = $category_service->getCategoriesWithProductCountsOptimized(true);


$genders = gender_service()->getGendersWithProductCounts();


$products_result = $product_api_service->getProductsForApi([
    'page' => $page,
    'limit' => $limit,
    'categories' => $category_filters,
    'genders' => $gender_filters,
    'sort' => $sort_filter,
    'featured' => $featured_filter
]);

$products = $products_result['products'];
$total_products = $products_result['total'];

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler - Bandland Shoes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="bg-gray-50 py-4 border-b">
        <div class="max-w-7xl mx-auto px-5">
            <nav class="text-sm">
                <ol class="flex items-center space-x-2 text-gray-500">
                    <li><a href="/" class="hover:text-primary transition-colors">Ana Sayfa</a></li>
                    <li class="text-gray-400">></li>
                    <li class="text-secondary font-medium">Ayakkabılar</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="bg-white py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-5 text-center">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-secondary mb-4 tracking-tight">AYAKKABI KOLEKSİYONU</h1>
        </div>
    </section>

    <section class="py-8 bg-white">
        <div class="max-w-7xl mx-auto px-5">
            <form method="GET" action="/products" id="filter-form">
                <input type="hidden" name="limit" value="<?php echo htmlspecialchars($limit); ?>">
                
                <!-- Mobile Filter Toggle Button -->
                <div class="lg:hidden mb-6">
                    <button type="button" id="mobile-filter-toggle"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <span class="flex items-center">
                            <i class="fas fa-filter mr-2"></i>
                            <span class="font-medium">Filtrele</span>
                            <span class="ml-2 text-sm text-gray-600">(<?php echo count(array_filter([$category_filters, $gender_filters, $featured_filter])); ?> aktif)</span>
                        </span>
                        <i class="fas fa-chevron-down transition-transform duration-200" id="filter-chevron"></i>
                    </button>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    
                    <aside class="lg:w-1/4">
                        <!-- Mobile Filter Overlay -->
                        <div id="mobile-filter-overlay" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>
                        
                        <!-- Filter Panel -->
                        <div id="filter-panel" class="lg:block hidden lg:relative fixed lg:top-auto lg:left-auto lg:w-auto lg:h-auto lg:z-auto lg:transform-none
                                    top-0 left-0 w-full h-full z-50 transform -translate-x-full transition-transform duration-300 ease-in-out">
                            <div class="bg-gray-50 p-6 rounded-lg lg:rounded-lg rounded-none h-full lg:h-auto overflow-y-auto">
                                <!-- Mobile Close Button -->
                                <div class="lg:hidden flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                                    <h3 class="text-xl font-bold text-secondary">FİLTRELE</h3>
                                    <button type="button" id="close-mobile-filter" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-times text-lg"></i>
                                    </button>
                                </div>
                                
                                <h3 class="hidden lg:block text-xl font-bold text-secondary mb-6">FİLTRELE</h3>
                            
                            <div class="space-y-6">
                            <div class="border-b pb-6">
                                <h4 class="font-semibold text-secondary mb-4">Kategoriler</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <?php foreach ($categories as $category): ?>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="categories[]" value="<?php echo htmlspecialchars($category['category_slug']); ?>"
                                                   <?php echo in_array($category['category_slug'], $category_filters) ? 'checked' : ''; ?>
                                                   class="mr-3 text-primary focus:ring-primary rounded">
                                            <span class="text-gray-700 text-sm">
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                                <span class="text-gray-500">(<?php echo $category['product_count']; ?>)</span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="border-b pb-6">
                                <h4 class="font-semibold text-secondary mb-4">Cinsiyet</h4>
                                <div class="space-y-2">
                                    <?php foreach ($genders as $gender): ?>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="genders[]" value="<?php echo htmlspecialchars($gender['slug']); ?>"
                                                   <?php echo in_array($gender['slug'], $gender_filters) ? 'checked' : ''; ?>
                                                   class="mr-3 text-primary focus:ring-primary rounded">
                                            <span class="text-gray-700 text-sm">
                                                <?php echo htmlspecialchars($gender['name']); ?>
                                                <span class="text-gray-500">(<?php echo $gender['product_count']; ?>)</span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="pb-6">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="featured" value="1"
                                           <?php echo $featured_filter ? 'checked' : ''; ?>
                                           class="mr-3 text-primary focus:ring-primary rounded">
                                    <span class="text-gray-700 font-medium">Öne Çıkanlar</span>
                                </label>
                                <!-- Mobile Apply Button -->
                                <div class="lg:hidden mt-6 pt-4 border-t border-gray-200">
                                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-medium hover:bg-primary-dark transition-colors">
                                        Filtreleri Uygula
                                    </button>
                                </div>
                            </div>
                        </div>
                    </aside>
                
                <main class="lg:w-3/4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div class="text-gray-600" id="product-count-display">
                            <strong><?php echo number_format($total_products); ?></strong> ürün bulundu
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Sırala:</span>
                            <select name="sort" id="sort-filter" class="px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-sm">
                                <option value="created_at-desc" <?php echo ($sort_filter === 'created_at-desc') ? 'selected' : ''; ?>>Önce En Yeni</option>
                                <option value="name-asc" <?php echo ($sort_filter === 'name-asc') ? 'selected' : ''; ?>>İsim A-Z</option>
                                <option value="price-asc" <?php echo ($sort_filter === 'price-asc') ? 'selected' : ''; ?>>Fiyat Artan</option>
                                <option value="price-desc" <?php echo ($sort_filter === 'price-desc') ? 'selected' : ''; ?>>Fiyat Azalan</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="product-grid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 group h-full flex flex-col">
                                    <div class="relative overflow-hidden bg-gray-100 aspect-square">
                                        <img src="<?php echo htmlspecialchars($product['primary_image'] ?? 'assets/images/placeholder.svg'); ?>"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                             loading="lazy"
                                             onerror="if(this.src !== 'assets/images/placeholder.svg') this.src = 'assets/images/placeholder.svg';">
                                        <?php if ($product['is_featured']): ?>
                                            <span class="absolute top-3 left-3 bg-primary text-white text-xs px-2 py-1 rounded">Öne Çıkan</span>
                                        <?php endif; ?>
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                            <a href="/product-details?id=<?php echo $product['id']; ?>"
                                               class="w-10 h-10 bg-white rounded-full hover:bg-primary hover:text-white transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100"
                                               title="Ürün Detayı">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4 text-center flex flex-col flex-grow">
                                        <h3 class="text-lg font-medium text-secondary mb-3 min-h-[3.5rem] flex items-center justify-center">
                                            <a href="/product-details?id=<?php echo $product['id']; ?>" class="text-inherit hover:text-primary transition-colors line-clamp-2">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h3>
                                        <div class="flex flex-wrap gap-1 justify-center mt-auto">
                                            <?php if (!empty($product['category_names'])): ?>
                                                <?php foreach ($product['category_names'] as $category_name): ?>
                                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                                        <i class="fas fa-tag text-xs mr-1"></i><?php echo htmlspecialchars($category_name); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <?php if (!empty($product['gender_names'])): ?>
                                                <?php foreach ($product['gender_names'] as $gender_name): ?>
                                                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                                        <i class="fas fa-venus-mars text-xs mr-1"></i><?php echo htmlspecialchars($gender_name); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-full text-center py-12">
                                <div class="text-gray-500 mb-4"><i class="fas fa-search text-4xl"></i></div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">Ürün Bulunamadı</h3>
                                <p class="text-gray-600 mb-4">Aradığınız kriterlere uygun ürün bulunamadı.</p>
                                <a href="/products" class="bg-primary text-white px-6 py-2 rounded hover:bg-primary-dark transition-colors">Tüm Ürünler</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Loading indicator for infinite scroll -->
                    <div id="loading-indicator" class="hidden flex justify-center items-center py-8">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                            <span>Daha fazla ürün yükleniyor...</span>
                        </div>
                    </div>
                    
                    <!-- End of products indicator -->
                    <div id="end-of-products" class="hidden text-center py-8 text-gray-500">
                        <i class="fas fa-check-circle text-2xl mb-2"></i>
                        <p>Tüm ürünler gösterildi</p>
                    </div>
                </main>
            </div>
            </form>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Mobile Filter Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileFilterToggle = document.getElementById('mobile-filter-toggle');
            const filterPanel = document.getElementById('filter-panel');
            const mobileFilterOverlay = document.getElementById('mobile-filter-overlay');
            const closeMobileFilter = document.getElementById('close-mobile-filter');
            const filterChevron = document.getElementById('filter-chevron');

            function openMobileFilter() {
                filterPanel.classList.remove('hidden', '-translate-x-full');
                filterPanel.classList.add('translate-x-0');
                mobileFilterOverlay.classList.remove('hidden');
                filterChevron.classList.add('rotate-180');
                document.body.style.overflow = 'hidden';
            }

            function closeMobileFilterPanel() {
                filterPanel.classList.add('-translate-x-full');
                filterPanel.classList.remove('translate-x-0');
                mobileFilterOverlay.classList.add('hidden');
                filterChevron.classList.remove('rotate-180');
                document.body.style.overflow = '';
                
                setTimeout(() => {
                    filterPanel.classList.add('hidden');
                }, 300);
            }

            if (mobileFilterToggle) {
                mobileFilterToggle.addEventListener('click', openMobileFilter);
            }

            if (closeMobileFilter) {
                closeMobileFilter.addEventListener('click', closeMobileFilterPanel);
            }

            if (mobileFilterOverlay) {
                mobileFilterOverlay.addEventListener('click', closeMobileFilterPanel);
            }

            // Close filter on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !filterPanel.classList.contains('hidden')) {
                    closeMobileFilterPanel();
                }
            });
        });
    </script>
    
    <script src="assets/js/script.js"></script>
    <script src="assets/js/products-filter.js" defer></script>
    
    <script>
        // Ana sayfa yüklendiğinde resim hatalarını yakala
        document.addEventListener('DOMContentLoaded', function() {
            const productImages = document.querySelectorAll('#product-grid img');
            productImages.forEach(img => {
                img.addEventListener('error', function() {
                    if (this.src !== 'assets/images/placeholder.svg') {
                        this.src = 'assets/images/placeholder.svg';
                    }
                });
            });
        });
    </script>
</body>
</html>