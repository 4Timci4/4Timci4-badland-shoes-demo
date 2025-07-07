<?php
require_once 'config/database.php';
require_once 'services/ProductService.php';
require_once 'services/CategoryService.php';
require_once 'services/GenderService.php';

// Sadece sayfa yapısı için gerekli verileri getir
// Ürünler artık AJAX ile yüklenecek

// Yeni hiyerarşik kategori yapısını al
$category_hierarchy = category_service()->getCategoriesWithProductCounts(true);

// Düz kategori listesi (geriye uyumluluk için)
$all_categories = category_service()->getAllCategories();

// Cinsiyetleri getir
$all_genders = gender_service()->getAllGenders();

// Varsayılan sayfalama
$items_per_page = 9;

// Prepare data for JavaScript
$page_data = [
    'categories' => $all_categories,
    'categoryHierarchy' => $category_hierarchy,
    'genders' => $all_genders,
    'apiUrl' => 'api/products.php',
    'itemsPerPage' => $items_per_page
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
                    
                    <!-- Cinsiyet Filtreleri -->
                    <div id="gender-filters-container" class="mb-6 border-b pb-6">
                        <h4 class="font-semibold text-secondary mb-4">Cinsiyet</h4>
                        <div id="gender-filters-list" class="space-y-2">
                            <!-- Cinsiyetler buraya JS ile eklenecek -->
                        </div>
                    </div>
                    
                    <!-- Kategori Filtreleri -->
                    <div id="category-filters-container" class="mb-6">
                        <h4 class="font-semibold text-secondary mb-4">Kategoriler</h4>
                        <div id="category-filters-list" class="space-y-4">
                            <!-- Ana kategoriler buraya JS ile eklenecek -->
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
    const allCategories = pageData.categories;
    const apiUrl = pageData.apiUrl;

    let state = {
        currentPage: 1,
        itemsPerPage: pageData.itemsPerPage,
        selectedCategories: [],
        selectedGenders: [],
        sort: 'created_at-desc',
        isLoading: false,
        products: [],
        totalProducts: 0,
        totalPages: 0
    };

    //--- DOM ELEMENTS ---//
    const categoryFiltersList = document.getElementById('category-filters-list');
    const genderFiltersList = document.getElementById('gender-filters-list');
    const productsGrid = document.getElementById('products-grid');
    const productsLoading = document.getElementById('products-loading');
    const paginationContainer = document.getElementById('pagination-container');
    const productCountInfo = document.getElementById('product-count-info');
    const sortSelect = document.getElementById('sort-select');

    //--- INITIALIZATION ---//
    function init() {
        renderCategoryFilters();
        renderGenderFilters();
        addEventListeners();
        fetchProducts();
    }

    //--- EVENT LISTENERS ---//
    function addEventListeners() {
        sortSelect.addEventListener('change', (e) => {
            state.sort = e.target.value;
            state.currentPage = 1; // Reset to first page on sort change
            fetchProducts();
        });
    }

    //--- API FUNCTIONS ---//
    function fetchProducts() {
        setLoading(true);
        
        // Build query params
        let params = new URLSearchParams();
        params.append('page', state.currentPage);
        params.append('limit', state.itemsPerPage);
        params.append('sort', state.sort);
        
        // Add category filters
        state.selectedCategories.forEach(category => {
            params.append('categories[]', category);
        });
        
        // Add gender filters
        state.selectedGenders.forEach(gender => {
            params.append('genders[]', gender);
        });
        
        // Fetch from API
        fetch(`${apiUrl}?${params.toString()}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ürünler yüklenirken bir hata oluştu.');
                }
                return response.json();
            })
            .then(data => {
                state.products = data.products;
                state.totalProducts = data.total;
                state.totalPages = data.pages;
                renderAll();
            })
            .catch(error => {
                console.error('Ürün yükleme hatası:', error);
                productsGrid.innerHTML = `<p class="text-red-500 col-span-full">Ürünler yüklenirken bir hata oluştu: ${error.message}</p>`;
            })
            .finally(() => {
                setLoading(false);
            });
    }
    
    function setLoading(isLoading) {
        state.isLoading = isLoading;
        productsLoading.style.display = isLoading ? 'block' : 'none';
    }
    
    function goToPage(page) {
        if (page < 1 || page > state.totalPages || page === state.currentPage) return;
        
        state.currentPage = page;
        fetchProducts();
        
        // Scroll to top of products section
        document.querySelector('.py-8.bg-white').scrollIntoView({ behavior: 'smooth' });
    }

    //--- RENDERING ---//
    function renderAll() {
        renderProductGrid();
        renderPagination();
        renderProductCount();
    }

    function renderGenderFilters() {
        const genderContainer = document.getElementById('gender-filters-list');
        genderContainer.innerHTML = '';
        
        // Tüm cinsiyetleri render et
        pageData.genders.forEach(gender => {
            const label = document.createElement('label');
            label.className = 'flex items-center cursor-pointer';
            label.innerHTML = `
                <input type="checkbox" class="mr-3 text-purple-600 focus:ring-purple-500 rounded gender-filter" 
                       value="${gender.slug}" 
                       data-gender-id="${gender.id}">
                <span class="text-gray-700">${gender.name}</span>
            `;
            genderContainer.appendChild(label);
        });
        
        // Cinsiyet filtrelerine event listener ekle
        document.querySelectorAll('.gender-filter').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                state.selectedGenders = Array.from(document.querySelectorAll('.gender-filter:checked')).map(cb => cb.value);
                state.currentPage = 1; // Reset to first page
                fetchProducts();
            });
        });
    }

    function renderCategoryFilters() {
        const categoryContainer = document.getElementById('category-filters-list');
        categoryContainer.innerHTML = '';
        
        // Ana kategorileri (Erkek, Kadın, Çocuk, Unisex) render et
        pageData.categoryHierarchy.forEach(mainCategory => {
            const mainCategoryDiv = document.createElement('div');
            mainCategoryDiv.className = 'mb-3';
            
            // Ana kategori başlığı
            const mainCategoryHeader = document.createElement('div');
            mainCategoryHeader.className = 'font-semibold mb-2';
            mainCategoryHeader.textContent = mainCategory.name;
            mainCategoryDiv.appendChild(mainCategoryHeader);
            
            // Ana kategorinin alt kategorileri
            if (mainCategory.subcategories && mainCategory.subcategories.length > 0) {
                const subcategoriesDiv = document.createElement('div');
                subcategoriesDiv.className = 'pl-4 space-y-2';
                
                mainCategory.subcategories.forEach(subcategory => {
                    if (subcategory.product_count > 0) {
                        const label = document.createElement('label');
                        label.className = 'flex items-center cursor-pointer';
                        label.innerHTML = `
                            <input type="checkbox" class="mr-3 text-primary focus:ring-primary rounded category-filter" 
                                   value="${subcategory.slug}" 
                                   data-category-id="${subcategory.id}"
                                   data-parent-id="${mainCategory.id}">
                            <span class="text-gray-700">${subcategory.name}</span>
                            <span class="ml-auto text-sm text-gray-500">${subcategory.product_count || 0}</span>
                        `;
                        subcategoriesDiv.appendChild(label);
                    }
                });
                
                mainCategoryDiv.appendChild(subcategoriesDiv);
            }
            
            categoryContainer.appendChild(mainCategoryDiv);
        });
        
        // Tüm filtrelere event listener ekle
        document.querySelectorAll('.category-filter').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                state.selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(cb => cb.value);
                state.currentPage = 1; // Reset to first page
                fetchProducts();
            });
        });
    }

    function renderProductGrid() {
        productsGrid.innerHTML = '';

        if (state.products.length === 0) {
            productsGrid.innerHTML = '<p class="text-gray-500 col-span-full">Filtrelerinizle eşleşen ürün bulunamadı.</p>';
            return;
        }

        state.products.forEach(product => {
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
                    <div class="flex flex-wrap gap-1 justify-center mt-2">
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                            <i class="fas fa-box text-xs mr-1"></i>${product.category_name || ''}
                        </span>
                        ${product.genders && product.genders.map(gender => 
                            `<span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                <i class="fas fa-venus-mars text-xs mr-1"></i>${gender.name}
                             </span>`
                        ).join('')}
                    </div>
                </div>
            `;
            productsGrid.appendChild(productCard);
        });
    }

    function renderPagination() {
        paginationContainer.innerHTML = '';
        
        if (state.totalPages <= 1) return;

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

        // Calculate page range to display
        let startPage = Math.max(1, state.currentPage - 2);
        let endPage = Math.min(state.totalPages, state.currentPage + 2);
        
        // Ensure we always show 5 pages if possible
        if (endPage - startPage < 4 && state.totalPages > 5) {
            if (startPage === 1) {
                endPage = Math.min(5, state.totalPages);
            } else if (endPage === state.totalPages) {
                startPage = Math.max(1, state.totalPages - 4);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
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

        paginationContainer.appendChild(createButton('<i class="fas fa-chevron-right"></i>', state.currentPage + 1, state.currentPage === state.totalPages));
    }

    function renderProductCount() {
        const startItem = state.totalProducts === 0 ? 0 : (state.currentPage - 1) * state.itemsPerPage + 1;
        const endItem = Math.min(state.currentPage * state.itemsPerPage, state.totalProducts);
        
        if (state.totalProducts === 0) {
            productCountInfo.innerHTML = '<strong>0</strong> ürün bulundu';
        } else {
            productCountInfo.innerHTML = `Toplam <strong>${state.totalProducts}</strong> üründen <strong>${startItem}-${endItem}</strong> arası gösteriliyor`;
        }
    }

    //--- START THE APP ---//
    init();
});
</script>

<?php include 'includes/footer.php'; ?>
