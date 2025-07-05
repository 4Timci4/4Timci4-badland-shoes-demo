</main>
        </div>
    </div>

    <!-- Additional JavaScript -->
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Custom Scripts -->
    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                setTimeout(() => {
                    flashMessage.style.opacity = '0';
                    flashMessage.style.transform = 'translateX(100%)';
                    setTimeout(() => flashMessage.remove(), 300);
                }, 5000);
            }
        });

        // Search functionality
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === '/') {
                e.preventDefault();
                const searchInput = document.querySelector('input[placeholder*="Ara"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });

        // Global function to show toast notifications
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toast-container') || createToastContainer();
            
            const toast = document.createElement('div');
            toast.className = `
                flex items-center p-4 mb-4 text-sm rounded-lg shadow-lg transform transition-all duration-300 ease-in-out translate-x-full
                ${type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : ''}
                ${type === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : ''}
                ${type === 'warning' ? 'bg-yellow-50 text-yellow-800 border border-yellow-200' : ''}
                ${type === 'info' ? 'bg-blue-50 text-blue-800 border border-blue-200' : ''}
            `;
            
            const icon = type === 'success' ? 'fa-check-circle' : 
                        type === 'error' ? 'fa-exclamation-circle' : 
                        type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon} mr-3"></i>
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            toastContainer.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 10);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-5 right-5 z-50 w-80';
            document.body.appendChild(container);
            return container;
        }

        // Global AJAX helper
        function makeAjaxRequest(url, data = {}, method = 'POST') {
            return fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: method !== 'GET' ? JSON.stringify(data) : null
            })
            .then(response => response.json())
            .catch(error => {
                console.error('AJAX Error:', error);
                showToast('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
            });
        }

        // Form validation helper
        function validateForm(formElement) {
            const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                    input.classList.remove('border-gray-300', 'focus:border-primary-500', 'focus:ring-primary-500');
                    isValid = false;
                } else {
                    input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                    input.classList.add('border-gray-300', 'focus:border-primary-500', 'focus:ring-primary-500');
                }
            });

            return isValid;
        }

        // Data table helper functions
        function initializeDataTable(tableSelector) {
            const table = document.querySelector(tableSelector);
            if (!table) return;

            // Add sorting functionality
            const headers = table.querySelectorAll('th[data-sort]');
            headers.forEach(header => {
                header.classList.add('cursor-pointer', 'hover:bg-gray-50', 'select-none');
                header.addEventListener('click', () => sortTable(table, header.dataset.sort));
            });

            // Add search functionality
            const searchInput = document.querySelector('[data-table-search]');
            if (searchInput) {
                searchInput.addEventListener('input', () => filterTable(table, searchInput.value));
            }
        }

        function sortTable(table, column) {
            // Basic table sorting implementation
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                const aText = a.querySelector(`[data-sort="${column}"]`)?.textContent.trim() || '';
                const bText = b.querySelector(`[data-sort="${column}"]`)?.textContent.trim() || '';
                return aText.localeCompare(bText);
            });

            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }

        function filterTable(table, searchTerm) {
            const tbody = table.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matches = text.includes(searchTerm.toLowerCase());
                row.style.display = matches ? '' : 'none';
            });
        }

        // Confirm dialog helper
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }

        // Loading state helper
        function setLoading(element, loading = true) {
            if (loading) {
                element.disabled = true;
                element.classList.add('opacity-50', 'cursor-not-allowed');
                const originalText = element.textContent;
                element.dataset.originalText = originalText;
                element.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Yükleniyor...';
            } else {
                element.disabled = false;
                element.classList.remove('opacity-50', 'cursor-not-allowed');
                element.textContent = element.dataset.originalText || 'Gönder';
            }
        }

        // Initialize common functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize data tables
            initializeDataTable('.data-table');

            // Form submission handling
            document.querySelectorAll('form[data-ajax]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (!validateForm(form)) {
                        showToast('Lütfen tüm gerekli alanları doldurun.', 'error');
                        return;
                    }

                    const submitBtn = form.querySelector('button[type="submit"]');
                    setLoading(submitBtn, true);

                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());

                    makeAjaxRequest(form.action, data)
                        .then(response => {
                            if (response && response.success) {
                                showToast(response.message || 'İşlem başarılı!', 'success');
                                if (response.redirect) {
                                    setTimeout(() => window.location.href = response.redirect, 1000);
                                }
                            } else {
                                showToast(response?.message || 'Bir hata oluştu!', 'error');
                            }
                        })
                        .finally(() => {
                            setLoading(submitBtn, false);
                        });
                });
            });

            // Confirm delete buttons
            document.querySelectorAll('[data-confirm-delete]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const message = this.dataset.confirmDelete || 'Bu öğeyi silmek istediğinize emin misiniz?';
                    confirmAction(message, () => {
                        window.location.href = this.href;
                    });
                });
            });

            // Auto-resize textareas
            document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                });
            });
        });
    </script>
</body>
</html>
