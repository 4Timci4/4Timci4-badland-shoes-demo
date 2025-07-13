document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    const sortFilter = document.getElementById('sort-filter');
    const productGrid = document.getElementById('product-grid');
    const paginationContainer = document.getElementById('pagination-container');
    const productCountDisplay = document.getElementById('product-count-display');

    /**
     * Filtre sayılarını günceller
     * @param {Object} filterCounts Filtre sayıları
     */
    function updateFilterCounts(filterCounts) {
        // Cinsiyet sayılarını güncelle
        if (filterCounts.gender_counts) {
            filterCounts.gender_counts.forEach(gender => {
                // Cinsiyet checkbox'larını bul
                const genderCheckboxes = document.querySelectorAll(`input[name="genders[]"][value="${gender.slug}"]`);
                
                // Her bir checkbox için yanındaki sayıyı güncelle
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
                // Kategori checkbox'larını bul
                const categoryCheckboxes = document.querySelectorAll(`input[name="categories[]"][value="${category.slug}"]`);
                
                // Her bir checkbox için yanındaki sayıyı güncelle
                categoryCheckboxes.forEach(checkbox => {
                    const countElement = checkbox.closest('label').querySelector('.text-gray-500');
                    if (countElement) {
                        countElement.textContent = `(${category.product_count})`;
                    }
                });
            });
        }
    }

    function fetchProducts(apiUrl, historyUrl, updateHistory = true) {
        
        productGrid.style.opacity = '0.5';

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
            
            if (productGrid) {
                productGrid.innerHTML = data.products_html;
            }
            if (paginationContainer) {
                paginationContainer.innerHTML = data.pagination_html;
            }
            if (productCountDisplay) {
                productCountDisplay.innerHTML = data.count_html;
            }
            
            // Filtre sayılarını güncelle
            if (data.filter_counts) {
                updateFilterCounts(data.filter_counts);
            }
            
            if (updateHistory) {
                history.pushState({path: historyUrl}, '', historyUrl);
            }
        })
        .catch(error => {
            console.error('Filtreleme hatası:', error);
            
        })
        .finally(() => {
            productGrid.style.opacity = '1';
        });
    }

    function handleFilterChange() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const paramsString = params.toString();
        
        const historyUrl = `products.php?${paramsString}`;
        const apiUrl = `api/ajax-products.php?${paramsString}`;
        
        fetchProducts(apiUrl, historyUrl);
    }

    
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

    if (paginationContainer) {
        
        paginationContainer.addEventListener('click', function (e) {
            if (e.target.tagName === 'A') {
                e.preventDefault();
                const historyUrl = e.target.getAttribute('href');
                if (historyUrl) {
                    const apiUrl = historyUrl.replace('products.php', 'api/ajax-products.php');
                    fetchProducts(apiUrl, historyUrl);
                }
            }
        });
    }
    
    
    window.addEventListener('popstate', function(event) {
        const historyUrl = (event.state && event.state.path) || location.href;
        const apiUrl = historyUrl.replace('products.php', 'api/ajax-products.php');
        fetchProducts(apiUrl, historyUrl, false);
    });
});