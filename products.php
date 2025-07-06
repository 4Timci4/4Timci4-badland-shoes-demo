<?php
require_once 'config/database.php';
require_once 'services/ProductService.php';
require_once 'services/CategoryService.php';

// Fetch all data on the server side
$all_products = product_service()->getProductModels(999, 0); // Get all products
$all_categories = category_service()->getAllCategories();

// Calculate product counts for each category
$category_counts = array_fill_keys(array_column($all_categories, 'slug'), 0);
foreach ($all_products as $product) {
    if (isset($category_counts[$product['category_slug']])) {
        $category_counts[$product['category_slug']]++;
    }
}

// Add counts to categories
foreach ($all_categories as &$category) {
    $category['product_count'] = $category_counts[$category['slug']] ?? 0;
}

// Prepare data for JavaScript
$page_data = [
    'products' => $all_products,
    'categories' => $all_categories
];

include 'includes/header.php';
?>

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
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Sol Sidebar - Filtreler -->
            <aside class="lg:w-1/4">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-secondary mb-6">FİLTRELE</h3>
                    <!-- Kategori Filtreleri -->
                    <div id="category-filters-container" class="mb-8">
                        <h4 class="font-semibold text-secondary mb-4">Stile Göre Filtrele</h4>
                        <div id="category-filters-list" class="space-y-3">
                            <!-- Kategoriler buraya JS ile eklenecek -->
                        </div>
                    </div>
                </div>
            </aside>
            
            <!-- Sağ İçerik Alanı -->
            <main class="lg:w-3/4">
                <!-- Sonuç Sayısı ve Sıralama -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div id="product-count-info" class="text-gray-600"></div>
                    <div class="flex items-center gap-4">
                        <!-- Sıralama -->
                        <select id="sort-select" class="px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-transparent bg-white text-sm">
                            <option value="created_at-desc">Önce En Yeni</option>
                            <option value="price-asc">Fiyat: Düşükten Yükseğe</option>
                            <option value="price-desc">Fiyat: Yüksekten Düşüğe</option>
                            <option value="name-asc">İsim A-Z</option>
                        </select>
                    </div>
                </div>
                
                <!-- Ürün Grid -->
                <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Ürünler buraya JS ile eklenecek -->
                </div>
                <div id="products-loading" class="text-center py-10" style="display: none;">
                    <p class="text-gray-500">Ürünler yükleniyor...</p>
                </div>
                
                <!-- Sayfalama -->
                <div id="pagination-container" class="flex justify-center mt-12">
                    <!-- Sayfalama buraya JS ile eklenecek -->
                </div>
            </main>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    //--- STATE MANAGEMENT ---//
    const pageData = <?php echo json_encode($page_data); ?>;
    let allProducts = pageData.products;
    const allCategories = pageData.categories;

    let state = {
        currentPage: 1,
        itemsPerPage: 9,
        selectedCategories: [],
        sort: 'created_at-desc',
        filteredProducts: []
    };

    //--- DOM ELEMENTS ---//
    const categoryFiltersList = document.getElementById('category-filters-list');
    const productsGrid = document.getElementById('products-grid');
    const paginationContainer = document.getElementById('pagination-container');
    const productCountInfo = document.getElementById('product-count-info');
    const sortSelect = document.getElementById('sort-select');

    //--- INITIALIZATION ---//
    function init() {
        renderCategoryFilters();
        addEventListeners();
        updateStateAndRender();
    }

    //--- EVENT LISTENERS ---//
    function addEventListeners() {
        sortSelect.addEventListener('change', (e) => {
            state.sort = e.target.value;
            updateStateAndRender();
        });
    }

    //--- STATE UPDATE ---//
    function updateStateAndRender() {
        // 1. Apply filters
        let filtered = [...allProducts];
        if (state.selectedCategories.length > 0) {
            filtered = filtered.filter(p => state.selectedCategories.includes(p.category_slug));
        }

        // 2. Apply sorting
        const [sortColumn, sortDirection] = state.sort.split('-');
        filtered.sort((a, b) => {
            const column = sortColumn === 'price' ? 'base_price' : sortColumn;
            let valA = a[column];
            let valB = b[column];

            if (column === 'base_price') {
                valA = parseFloat(valA);
                valB = parseFloat(valB);
            }

            if (valA < valB) return sortDirection === 'asc' ? -1 : 1;
            if (valA > valB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
        
        state.filteredProducts = filtered;
        state.currentPage = 1; // Reset to first page on any change

        // 3. Render everything
        renderAll();
    }
    
    function goToPage(page) {
        state.currentPage = page;
        renderAll();
    }

    //--- RENDERING ---//
    function renderAll() {
        renderProductGrid();
        renderPagination();
        renderProductCount();
    }

    function renderCategoryFilters() {
        categoryFiltersList.innerHTML = '';
        allCategories.forEach(category => {
            if (category.product_count > 0) {
                const label = document.createElement('label');
                label.className = 'flex items-center cursor-pointer';
                label.innerHTML = `
                    <input type="checkbox" class="mr-3 text-primary focus:ring-primary rounded category-filter" value="${category.slug}">
                    <span class="text-gray-700">${category.name}</span>
                    <span class="ml-auto text-sm text-gray-500">${category.product_count}</span>
                `;
                categoryFiltersList.appendChild(label);
            }
        });
        
        document.querySelectorAll('.category-filter').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                state.selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(cb => cb.value);
                updateStateAndRender();
            });
        });
    }

    function renderProductGrid() {
        productsGrid.innerHTML = '';
        const paginatedProducts = state.filteredProducts.slice(
            (state.currentPage - 1) * state.itemsPerPage,
            state.currentPage * state.itemsPerPage
        );

        if (paginatedProducts.length === 0) {
            productsGrid.innerHTML = '<p class="text-gray-500 col-span-full">Filtrelerinizle eşleşen ürün bulunamadı.</p>';
            return;
        }

        paginatedProducts.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 group';
            productCard.innerHTML = `
                <div class="relative overflow-hidden bg-gray-100 aspect-square">
                    <img src="${product.image_url || 'assets/images/placeholder.png'}" alt="${product.name}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                        <a href="/product-details.php?id=${product.id}" class="w-10 h-10 bg-white rounded-full hover:bg-primary hover:text-white transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100" title="Ürün Detayı">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                <div class="p-4 text-center">
                    <h3 class="text-lg font-medium text-secondary mb-2">
                        <a href="/product-details.php?id=${product.id}" class="text-inherit hover:text-primary transition-colors">${product.name}</a>
                    </h3>
                    <div class="text-xl font-bold text-secondary">
                        ₺ ${Number(product.base_price).toFixed(2)}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">${product.category_name || ''}</div>
                </div>
            `;
            productsGrid.appendChild(productCard);
        });
    }

    function renderPagination() {
        paginationContainer.innerHTML = '';
        const totalItems = state.filteredProducts.length;
        const totalPages = Math.ceil(totalItems / state.itemsPerPage);

        if (totalPages <= 1) return;

        const createButton = (text, page, isDisabled = false) => {
            const button = document.createElement('button');
            button.innerHTML = text;
            button.className = `px-3 py-2 text-gray-600 hover:text-primary transition-colors`;
            if (isDisabled) {
                button.classList.add('text-gray-400', 'cursor-not-allowed');
                button.disabled = true;
            } else {
                 button.addEventListener('click', () => goToPage(page));
            }
            return button;
        };
        
        paginationContainer.appendChild(createButton('<i class="fas fa-chevron-left"></i>', state.currentPage - 1, state.currentPage === 1));

        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.className = `px-4 py-2 rounded transition-all`;
            if (i === state.currentPage) {
                pageButton.classList.add('bg-primary', 'text-white');
            } else {
                pageButton.classList.add('text-gray-600', 'hover:bg-primary', 'hover:text-white');
                pageButton.addEventListener('click', () => goToPage(i));
            }
            paginationContainer.appendChild(pageButton);
        }

        paginationContainer.appendChild(createButton('<i class="fas fa-chevron-right"></i>', state.currentPage + 1, state.currentPage === totalPages));
    }

    function renderProductCount() {
        const totalItems = state.filteredProducts.length;
        const startItem = Math.min((state.currentPage - 1) * state.itemsPerPage + 1, totalItems);
        const endItem = Math.min(state.currentPage * state.itemsPerPage, totalItems);
        
        if (totalItems === 0) {
            productCountInfo.innerHTML = '<strong>0</strong> ürün bulundu';
        } else {
            productCountInfo.innerHTML = `Toplam <strong>${totalItems}</strong> üründen <strong>${startItem}-${endItem}</strong> arası gösteriliyor`;
        }
    }

    //--- START THE APP ---//
    init();
});
</script>

<?php include 'includes/footer.php'; ?>
