<?php
/**
 * =================================================================
 * OPTIMIZED PRODUCTS PAGE - FINAL VERSION
 * =================================================================
 * This is the final, optimized version of products.php, relying
 * entirely on Materialized Views for high performance.
 * =================================================================
 */

// Required optimized services
require_once 'services/CategoryService.php';
require_once 'services/Product/ProductApiService.php';
require_once 'services/GenderService.php';

// --- Parameter Handling ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 9;
$category_filters = isset($_GET['categories']) ? (is_array($_GET['categories']) ? $_GET['categories'] : [$_GET['categories']]) : [];
$gender_filters = isset($_GET['genders']) ? (is_array($_GET['genders']) ? $_GET['genders'] : [$_GET['genders']]) : [];
$sort_filter = isset($_GET['sort']) ? $_GET['sort'] : 'created_at-desc';
$featured_filter = isset($_GET['featured']) ? (bool)$_GET['featured'] : null;

// --- Service Initialization ---
$category_service = category_service();
$product_api_service = product_api_service();

// --- Data Fetching (Optimized with Materialized Views) ---

// Fetch categories with counts directly from the view
$categories = $category_service->getCategoriesWithProductCountsOptimized(true);

// Fetch genders with counts directly from the view
$genders = gender_service()->getGendersWithProductCounts();

// Fetch products using slugs directly
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
$total_pages = $products_result['pages'] ?? ceil($total_products / $limit);

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
    
    <!-- Breadcrumb -->
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

    <!-- Page Title -->
    <section class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-5 text-center">
            <h1 class="text-5xl font-bold text-secondary mb-4 tracking-tight">AYAKKABI KOLEKSİYONU</h1>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-8 bg-white">
        <div class="max-w-7xl mx-auto px-5">
            <form method="GET" action="products.php" id="filter-form">
                <div class="flex flex-col lg:flex-row gap-8">
                    
                    <!-- Sol Sidebar - Filtreler -->
                    <aside class="lg:w-1/4">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-xl font-bold text-secondary mb-6">FİLTRELE</h3>
                            
                            <div class="space-y-6">
                            <!-- Kategori Filtreleri -->
                            <div class="border-b pb-6">
                                <h4 class="font-semibold text-secondary mb-4">Kategoriler</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <?php foreach ($categories as $category): ?>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="categories[]" value="<?php echo htmlspecialchars($category['slug']); ?>"
                                                   <?php echo in_array($category['slug'], $category_filters) ? 'checked' : ''; ?>
                                                   class="mr-3 text-primary focus:ring-primary rounded">
                                            <span class="text-gray-700 text-sm">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                                <span class="text-gray-500">(<?php echo $category['product_count']; ?>)</span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Cinsiyet Filtreleri -->
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
                            
                            <!-- Öne Çıkanlar -->
                            <div class="pb-6">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="featured" value="1"
                                           <?php echo $featured_filter ? 'checked' : ''; ?>
                                           class="mr-3 text-primary focus:ring-primary rounded">
                                    <span class="text-gray-700 font-medium">Öne Çıkanlar</span>
                                </label>
                            </div>
                        </div>
                    </aside>
                
                <!-- Sağ İçerik Alanı -->
                <main class="lg:w-3/4">
                    <!-- Sonuç Sayısı ve Sıralama -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div class="text-gray-600" id="product-count-display">
                            <strong><?php echo number_format($total_products); ?></strong> ürün bulundu
                        </div>
                        
                        <!-- Sıralama -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Sırala:</span>
                            <select name="sort" id="sort-filter" class="px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-sm">
                                <option value="created_at-desc" <?php echo ($sort_filter === 'created_at-desc') ? 'selected' : ''; ?>>Önce En Yeni</option>
                                <option value="price-asc" <?php echo ($sort_filter === 'price-asc') ? 'selected' : ''; ?>>Fiyat: Düşükten Yükseğe</option>
                                <option value="price-desc" <?php echo ($sort_filter === 'price-desc') ? 'selected' : ''; ?>>Fiyat: Yüksekten Düşüğe</option>
                                <option value="name-asc" <?php echo ($sort_filter === 'name-asc') ? 'selected' : ''; ?>>İsim A-Z</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Ürün Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="product-grid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 group h-full flex flex-col">
                                    <div class="relative overflow-hidden bg-gray-100 aspect-square">
                                        <img src="<?php echo htmlspecialchars($product['primary_image'] ?? 'assets/images/placeholder.svg'); ?>"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                             loading="lazy">
                                        <?php if ($product['is_featured']): ?>
                                            <span class="absolute top-3 left-3 bg-primary text-white text-xs px-2 py-1 rounded">Öne Çıkan</span>
                                        <?php endif; ?>
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                            <a href="product-details.php?id=<?php echo $product['id']; ?>"
                                               class="w-10 h-10 bg-white rounded-full hover:bg-primary hover:text-white transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100"
                                               title="Ürün Detayı">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4 text-center flex flex-col flex-grow">
                                        <h3 class="text-lg font-medium text-secondary mb-3 min-h-[3.5rem] flex items-center justify-center">
                                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="text-inherit hover:text-primary transition-colors line-clamp-2">
                                                <?php echo htmlspecialchars($product['name']); ?>
                                            </a>
                                        </h3>
                                        <div class="text-xl font-bold text-secondary mb-3">
                                            ₺ <?php echo number_format($product['base_price'], 2); ?>
                                        </div>
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
                                <a href="products.php" class="bg-primary text-white px-6 py-2 rounded hover:bg-primary-dark transition-colors">Tüm Ürünler</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sayfalama -->
                    <?php if ($total_pages > 1): ?>
                        <div class="flex justify-center mt-12">
                            <nav class="flex items-center gap-2" id="pagination-container">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo ($page - 1); ?>&<?php echo http_build_query(array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)); ?>" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors"><i class="fas fa-chevron-left"></i></a>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                if ($end_page - $start_page < 4 && $total_pages > 5) {
                                    if ($start_page === 1) $end_page = min(5, $total_pages);
                                    else if ($end_page === $total_pages) $start_page = max(1, $total_pages - 4);
                                }
                                ?>
                                
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)); ?>" class="px-4 py-2 rounded transition-all <?php echo ($i === $page) ? 'bg-primary text-white' : 'text-gray-600 hover:bg-primary hover:text-white'; ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo ($page + 1); ?>&<?php echo http_build_query(array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)); ?>" class="px-3 py-2 text-gray-600 hover:text-primary transition-colors"><i class="fas fa-chevron-right"></i></a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
            </form>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/script.js"></script>
    <script src="assets/js/products-filter.js" defer></script>
</body>
</html>