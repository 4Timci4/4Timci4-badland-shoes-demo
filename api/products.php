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
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Sol Sidebar - Filtreler -->
            <aside class="lg:w-1/4">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-secondary mb-6">FİLTRELE</h3>
                    <div id="filters-loading" class="text-gray-500">Filtreler yükleniyor...</div>
                    <!-- Kategori Filtreleri -->
                    <div id="category-filters-container" class="mb-8 hidden">
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
                    <div id="product-count-info" class="text-gray-600">Ürünler yükleniyor...</div>
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
                <div id="products-loading" class="text-center py-10">
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

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // PHP'den Supabase bilgilerini al
    <?php require_once 'config/database.php'; ?>
    const supabaseUrl = '<?php echo SUPABASE_URL; ?>';
    const supabaseKey = '<?php echo SUPABASE_KEY; ?>';

    // Supabase istemcisini oluştur
    const { createClient } = supabase;
    const _supabase = createClient(supabaseUrl, supabaseKey);

    // DOM Elementleri
    const categoryFiltersContainer = document.getElementById('category-filters-container');
    const categoryFiltersList = document.getElementById('category-filters-list');
    const productsGrid = document.getElementById('products-grid');
    const paginationContainer = document.getElementById('pagination-container');
    const productCountInfo = document.getElementById('product-count-info');
    const sortSelect = document.getElementById('sort-select');
    const filtersLoading = document.getElementById('filters-loading');
    const productsLoading = document.getElementById('products-loading');

    // Durum (State)
    let currentPage = 1;
    const itemsPerPage = 9;
    let selectedCategories = [];

    async function fetchAndRenderAll() {
        showLoading(true);
        await fetchAndRenderCategories();
        await fetchAndRenderProducts();
        showLoading(false);
    }
    
    function showLoading(isLoading) {
        if (isLoading) {
            productsGrid.innerHTML = '';
            filtersLoading.style.display = 'block';
            productsLoading.style.display = 'block';
            paginationContainer.style.display = 'none';
        } else {
            filtersLoading.style.display = 'none';
            productsLoading.style.display = 'none';
            paginationContainer.style.display = 'flex';
        }
    }

    async function fetchAndRenderCategories() {
        const { data: categories, error } = await _supabase.from('categories').select('name, slug');
        if (error || !categories) {
            categoryFiltersList.innerHTML = '<p class="text-red-500">Kategoriler yüklenemedi.</p>';
            return;
        }

        const { data: counts, error: countError } = await _supabase.rpc('get_category_product_counts');

        const categoryCounts = (counts || []).reduce((acc, item) => {
            acc[item.slug] = item.product_count;
            return acc;
        }, {});

        categoryFiltersList.innerHTML = '';
        categories.forEach(category => {
            const count = categoryCounts[category.slug] || 0;
            const label = document.createElement('label');
            label.className = 'flex items-center cursor-pointer';
            label.innerHTML = `
                <input type="checkbox" class="mr-3 text-primary focus:ring-primary rounded category-filter" value="${category.slug}">
                <span class="text-gray-700">${category.name}</span>
                <span class="ml-auto text-sm text-gray-500">${count}</span>
            `;
            categoryFiltersList.appendChild(label);
        });
        categoryFiltersContainer.style.display = 'block';
        
        // Event listener'ları ekle
        document.querySelectorAll('.category-filter').forEach(checkbox => {
            checkbox.addEventListener('change', handleFilterChange);
        });
    }

    async function fetchAndRenderProducts() {
        productsLoading.style.display = 'block';
        productsGrid.innerHTML = '';

        const offset = (currentPage - 1) * itemsPerPage;
        const [sortColumn, sortDirection] = sortSelect.value.split('-');
        
        let query = _supabase.from('product_models_view').select('*', { count: 'exact' });

        // Filtreleme
        if (selectedCategories.length > 0) {
            query = query.in('category_slug', selectedCategories);
        }

        // Sıralama
        if (sortColumn && sortDirection) {
            query = query.order(sortColumn, { ascending: sortDirection === 'asc' });
        }

        // Sayfalama
        query = query.range(offset, offset + itemsPerPage - 1);

        const { data: products, error, count } = await query;

        productsLoading.style.display = 'none';
        if (error) {
            productsGrid.innerHTML = `<p class="text-red-500 col-span-full">Ürünler yüklenirken bir hata oluştu: ${error.message}</p>`;
            productCountInfo.textContent = '0 sonuç';
            renderPagination(0);
            return;
        }

        if (products.length === 0) {
            productsGrid.innerHTML = '<p class="text-gray-500 col-span-full">Filtrelerinizle eşleşen ürün bulunamadı.</p>';
        } else {
            products.forEach(product => {
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
                            ₺ ${Number(product.price).toFixed(2)}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">${product.category_name || ''}</div>
                    </div>
                `;
                productsGrid.appendChild(productCard);
            });
        }
        
        const startItem = Math.min(offset + 1, count || 0);
        const endItem = Math.min(offset + itemsPerPage, count || 0);
        productCountInfo.innerHTML = `Toplam <strong>${count || 0}</strong> üründen <strong>${startItem}-${endItem}</strong> arası gösteriliyor`;

        renderPagination(count || 0);
    }

    function renderPagination(totalItems) {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (totalPages <= 1) return;

        // Önceki Butonu
        const prevButton = document.createElement('button');
        prevButton.innerHTML = `<i class="fas fa-chevron-left"></i>`;
        prevButton.className = "px-3 py-2 text-gray-600 hover:text-primary transition-colors";
        prevButton.disabled = currentPage === 1;
        if(prevButton.disabled) prevButton.classList.add('text-gray-400', 'cursor-not-allowed');
        prevButton.addEventListener('click', () => {
            currentPage--;
            fetchAndRenderProducts();
        });
        paginationContainer.appendChild(prevButton);

        // Sayfa Numaraları
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.className = `px-4 py-2 rounded transition-all`;
            if (i === currentPage) {
                pageButton.classList.add('bg-primary', 'text-white');
            } else {
                pageButton.classList.add('text-gray-600', 'hover:bg-primary', 'hover:text-white');
            }
            pageButton.addEventListener('click', () => {
                currentPage = i;
                fetchAndRenderProducts();
            });
            paginationContainer.appendChild(pageButton);
        }

        // Sonraki Butonu
        const nextButton = document.createElement('button');
        nextButton.innerHTML = `<i class="fas fa-chevron-right"></i>`;
        nextButton.className = "px-3 py-2 text-gray-600 hover:text-primary transition-colors";
        nextButton.disabled = currentPage === totalPages;
        if(nextButton.disabled) nextButton.classList.add('text-gray-400', 'cursor-not-allowed');
        nextButton.addEventListener('click', () => {
            currentPage++;
            fetchAndRenderProducts();
        });
        paginationContainer.appendChild(nextButton);
    }
    
    function handleFilterChange() {
        selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(cb => cb.value);
        currentPage = 1; // Filtre değiştiğinde ilk sayfaya dön
        fetchAndRenderProducts();
    }

    // Event Listeners
    sortSelect.addEventListener('change', handleFilterChange);

    // Başlangıç
    fetchAndRenderAll();
});
</script>

<?php include 'includes/footer.php'; ?>
