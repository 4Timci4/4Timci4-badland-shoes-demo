document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    const sortFilter = document.getElementById('sort-filter');
    const productGrid = document.getElementById('product-grid');
    const productCountDisplay = document.getElementById('product-count-display');
    const loadingIndicator = document.getElementById('loading-indicator');
    const endOfProducts = document.getElementById('end-of-products');
    
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let currentFilters = {};

    /**
     * Filtre sayılarını günceller
     * @param {Object} filterCounts Filtre sayıları
     */
    function updateFilterCounts(filterCounts) {
        // Cinsiyet sayılarını güncelle
        if (filterCounts.gender_counts) {
            filterCounts.gender_counts.forEach(gender => {
                const genderCheckboxes = document.querySelectorAll(`input[name="genders[]"][value="${gender.slug}"]`);
                genderCheckboxes.forEach(checkbox => {
                    const countElement = checkbox.closest('label').querySelector('.text-gray-500');
                    if (countElement) {
                        countElement.textContent = `(${gender.product_count})`;
                    }
                });
            });
        }
        
        // Kategori sayılarını güncelle
        if (filterCounts.category_counts) {
            filterCounts.category_counts.forEach(category => {
                const categoryCheckboxes = document.querySelectorAll(`input[name="categories[]"][value="${category.slug}"]`);
                categoryCheckboxes.forEach(checkbox => {
                    const countElement = checkbox.closest('label').querySelector('.text-gray-500');
                    if (countElement) {
                        countElement.textContent = `(${category.product_count})`;
                    }
                });
            });
        }
    }

    /**
     * Mevcut form verilerini alır
     */
    function getCurrentFilters() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        return params;
    }

    /**
     * Loading göstergelerini yönetir
     */
    function showLoading() {
        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
        }
        if (endOfProducts) {
            endOfProducts.classList.add('hidden');
        }
    }

    function hideLoading() {
        if (loadingIndicator) {
            loadingIndicator.classList.add('hidden');
        }
    }

    function showEndOfProducts() {
        if (endOfProducts) {
            endOfProducts.classList.remove('hidden');
        }
        hideLoading();
    }

    /**
     * Ürünleri yükler (infinite scroll veya filtre değişikliği için)
     */
    function loadProducts(page = 1, append = false) {
        if (isLoading) return;
        
        isLoading = true;
        showLoading();

        const params = getCurrentFilters();
        params.set('page', page);
        params.set('limit', 12);
        
        const apiUrl = `api/ajax-products.php?${params.toString()}`;
        const historyUrl = `products.php?${params.toString()}`;

        if (!append) {
            productGrid.style.opacity = '0.5';
        }

        fetch(apiUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (append) {
                // Infinite scroll - ürünleri ekle
                if (data.products_html.trim()) {
                    productGrid.insertAdjacentHTML('beforeend', data.products_html);
                }
            } else {
                // Filtre değişikliği - ürünleri değiştir
                productGrid.innerHTML = data.products_html;
                
                // URL'yi güncelle
                history.pushState({path: historyUrl}, '', historyUrl);
            }
            
            // Resim yükleme hatalarını yakala
            const productImages = productGrid.querySelectorAll('img');
            productImages.forEach(img => {
                img.addEventListener('error', function() {
                    if (this.src !== 'assets/images/placeholder.svg') {
                        this.src = 'assets/images/placeholder.svg';
                    }
                });
            });
            
            // Ürün sayısını güncelle
            if (productCountDisplay && data.count_html) {
                productCountDisplay.innerHTML = data.count_html;
            }
            
            // Filtre sayılarını güncelle
            if (data.filter_counts) {
                updateFilterCounts(data.filter_counts);
            }
            
            // Infinite scroll durumunu güncelle
            currentPage = data.current_page;
            hasMore = data.has_more;
            
            if (!hasMore) {
                showEndOfProducts();
            }
            
            // Eğer ürün yoksa ve ilk sayfa ise "ürün bulunamadı" mesajını göster
            if (!append && data.products_count === 0) {
                productGrid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-500 mb-4"><i class="fas fa-search text-4xl"></i></div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Ürün Bulunamadı</h3>
                        <p class="text-gray-600 mb-4">Aradığınız kriterlere uygun ürün bulunamadı.</p>
                        <a href="products.php" class="bg-primary text-white px-6 py-2 rounded hover:bg-primary-dark transition-colors">Tüm Ürünler</a>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Ürün yükleme hatası:', error);
        })
        .finally(() => {
            isLoading = false;
            hideLoading();
            if (!append) {
                productGrid.style.opacity = '1';
            }
        });
    }

    /**
     * Filtre değişikliğini işler
     */
    function handleFilterChange() {
        currentPage = 1;
        hasMore = true;
        loadProducts(1, false);
    }

    /**
     * Infinite scroll kontrolü
     */
    function checkInfiniteScroll() {
        if (isLoading || !hasMore) return;

        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        
        // Sayfanın %80'ine geldiğinde yeni ürünleri yükle
        if (scrollTop + windowHeight >= documentHeight * 0.8) {
            loadProducts(currentPage + 1, true);
        }
    }

    // Event listeners
    if (filterForm) {
        filterForm.addEventListener('change', function(e) {
            if (e.target.matches('input[type="checkbox"]') || e.target.matches('select')) {
                handleFilterChange();
            }
        });
    }

    if (sortFilter) {
        sortFilter.addEventListener('change', handleFilterChange);
    }

    // Infinite scroll event listener
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(checkInfiniteScroll, 100);
    });

    // Browser geri/ileri butonları için
    window.addEventListener('popstate', function(event) {
        const historyUrl = (event.state && event.state.path) || location.href;
        const url = new URL(historyUrl);
        const params = url.searchParams;
        
        // Form elemanlarını güncelle
        const formData = new FormData(filterForm);
        for (let [key, value] of formData.entries()) {
            const input = filterForm.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = params.getAll(key).includes(value);
                } else {
                    input.value = params.get(key) || '';
                }
            }
        }
        
        // Ürünleri yeniden yükle
        currentPage = 1;
        hasMore = true;
        loadProducts(1, false);
    });

    // Sayfa yüklendiğinde mevcut filtreleri kaydet
    currentFilters = getCurrentFilters();
});