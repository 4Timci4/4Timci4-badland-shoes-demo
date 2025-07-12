document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    const sortFilter = document.getElementById('sort-filter');
    const productGrid = document.getElementById('product-grid');
    const paginationContainer = document.getElementById('pagination-container');
    const productCountDisplay = document.getElementById('product-count-display');

    function fetchProducts(apiUrl, historyUrl, updateHistory = true) {
        // Add a loading indicator
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
            // Update content
            if (productGrid) {
                productGrid.innerHTML = data.products_html;
            }
            if (paginationContainer) {
                paginationContainer.innerHTML = data.pagination_html;
            }
            if (productCountDisplay) {
                productCountDisplay.innerHTML = data.count_html;
            }

            // Update browser history with the clean URL
            if (updateHistory) {
                history.pushState({path: historyUrl}, '', historyUrl);
            }
        })
        .catch(error => {
            console.error('Filtreleme hatası:', error);
            // Optionally, show an error message to the user
        })
        .finally(() => {
            // Remove loading indicator
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

    // Event Listeners
    if (filterForm) {
        filterForm.addEventListener('change', function(e) {
            // Sadece checkbox ve select değişikliklerini dinle
            if (e.target.matches('input[type="checkbox"]') || e.target.matches('select')) {
                handleFilterChange();
            }
        });
    }

    if (sortFilter) {
        sortFilter.addEventListener('change', handleFilterChange);
    }

    if (paginationContainer) {
        // Use event delegation for pagination links
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
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        const historyUrl = (event.state && event.state.path) || location.href;
        const apiUrl = historyUrl.replace('products.php', 'api/ajax-products.php');
        fetchProducts(apiUrl, historyUrl, false);
    });
});