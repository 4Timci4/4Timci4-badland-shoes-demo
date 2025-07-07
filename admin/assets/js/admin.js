/**
 * Admin Panel JavaScript
 * Modern admin panel için temel fonksiyonlar ve etkileşimler
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeAdminPanel();
    });

    /**
     * Admin Panel'i başlat
     */
    function initializeAdminPanel() {
        initMobileMenu();
        initSubmenuToggle();
        initSearchToggle();
        initPerfectScrollbar();
        initTooltips();
        initConfirmDialogs();
        initFormValidation();
        initTableFeatures();
        // initProductsPage(); // Kaldırıldı
        setActiveMenuItem();
    }

    /**
     * Mobil menü toggle
     */
    function initMobileMenu() {
        const menuToggle = document.querySelector('.layout-menu-toggle');
        const menu = document.querySelector('.layout-menu');
        const overlay = document.querySelector('.layout-overlay');
        const body = document.body;

        if (menuToggle) {
            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                toggleMobileMenu();
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function() {
                closeMobileMenu();
            });
        }

        // ESC tuşu ile menüyü kapat
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        function toggleMobileMenu() {
            if (window.innerWidth < 1200) {
                menu.classList.toggle('show');
                overlay.classList.toggle('show');
                body.classList.toggle('menu-open');
            }
        }

        function closeMobileMenu() {
            menu.classList.remove('show');
            overlay.classList.remove('show');
            body.classList.remove('menu-open');
        }

        // Pencere boyutu değiştiğinde menüyü kapat
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1200) {
                closeMobileMenu();
            }
        });
    }

    /**
     * Submenu toggle işlevselliği
     */
    function initSubmenuToggle() {
        const submenuToggles = document.querySelectorAll('.menu-toggle');

        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                const menuItem = this.closest('.menu-item-submenu');
                const isOpen = menuItem.classList.contains('open');
                
                // Diğer açık menüleri kapat (accordion davranışı)
                document.querySelectorAll('.menu-item-submenu.open').forEach(function(item) {
                    if (item !== menuItem) {
                        item.classList.remove('open');
                    }
                });
                
                // Tıklanan menüyü aç/kapat
                menuItem.classList.toggle('open', !isOpen);
            });
        });
    }

    /**
     * Arama toggle
     */
    function initSearchToggle() {
        const searchToggle = document.querySelector('.search-toggler');
        const searchWrapper = document.querySelector('.navbar-search-wrapper');
        const searchInput = document.querySelector('.search-input');

        if (searchToggle && searchWrapper) {
            searchToggle.addEventListener('click', function(e) {
                e.preventDefault();
                searchWrapper.classList.toggle('d-none');
                
                if (!searchWrapper.classList.contains('d-none')) {
                    searchInput.focus();
                }
            });
        }

        // Arama inputu için enter tuşu
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    performSearch(this.value);
                }
            });
        }
    }

    /**
     * Perfect Scrollbar başlatma
     */
    function initPerfectScrollbar() {
        const scrollElements = document.querySelectorAll('.ps');
        
        if (typeof PerfectScrollbar !== 'undefined') {
            scrollElements.forEach(function(element) {
                new PerfectScrollbar(element, {
                    wheelPropagation: false,
                    suppressScrollX: true
                });
            });
        }
    }

    /**
     * Bootstrap tooltips başlatma
     */
    function initTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    /**
     * Onay diyalogları
     */
    function initConfirmDialogs() {
        const confirmButtons = document.querySelectorAll('[data-confirm]');
        
        confirmButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Bu işlemi gerçekleştirmek istediğinize emin misiniz?';
                
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }

    /**
     * Form validasyonu
     */
    function initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // İlk hatalı alana odaklan
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                }
                
                form.classList.add('was-validated');
            });
        });

        // Real-time validation
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(function(input) {
            input.addEventListener('blur', function() {
                if (this.closest('.was-validated')) {
                    this.classList.toggle('is-valid', this.checkValidity());
                    this.classList.toggle('is-invalid', !this.checkValidity());
                }
            });
        });
    }

    /**
     * Tablo özellikleri
     */
    function initTableFeatures() {
        // Tablo satırı seçimi
        const selectAllCheckbox = document.querySelector('#select-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateBulkActions();
            });
        }
        
        rowCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSelectAll();
                updateBulkActions();
            });
        });

        // Toplu işlemler
        const bulkActionButton = document.querySelector('#bulk-action-btn');
        const bulkActionSelect = document.querySelector('#bulk-action-select');
        
        if (bulkActionButton) {
            bulkActionButton.addEventListener('click', function() {
                const action = bulkActionSelect.value;
                const selectedIds = getSelectedIds();
                
                if (selectedIds.length === 0) {
                    showAlert('Lütfen en az bir öğe seçin.', 'warning');
                    return;
                }
                
                if (action) {
                    performBulkAction(action, selectedIds);
                }
            });
        }
    }

    /**
     * Aktif menü öğesini ayarla
     */
    function setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.menu-link[href]');
        
        menuLinks.forEach(function(link) {
            const href = link.getAttribute('href');
            if (currentPath.includes(href) && href !== '#') {
                const menuItem = link.closest('.menu-item');
                menuItem.classList.add('active');
                
                // Parent submenu'yu aç
                const parentSubmenu = menuItem.closest('.menu-item-submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.add('open');
                }
            }
        });
    }

    /**
     * Yardımcı fonksiyonlar
     */
    
    function updateSelectAll() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const totalCount = document.querySelectorAll('.row-checkbox').length;
        const selectAllCheckbox = document.querySelector('#select-all');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkedCount === totalCount;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }
    }
    
    function updateBulkActions() {
        const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const bulkActionsContainer = document.querySelector('.bulk-actions');
        
        if (bulkActionsContainer) {
            bulkActionsContainer.style.display = selectedCount > 0 ? 'block' : 'none';
        }
    }
    
    function getSelectedIds() {
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        return Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
    }
    
    function performBulkAction(action, ids) {
        const actionMessages = {
            'delete': 'Seçili öğeleri silmek istediğinize emin misiniz?',
            'activate': 'Seçili öğeleri aktifleştirmek istediğinize emin misiniz?',
            'deactivate': 'Seçili öğeleri pasifleştirmek istediğinize emin misiniz?'
        };
        
        const message = actionMessages[action] || 'Bu işlemi gerçekleştirmek istediğinize emin misiniz?';
        
        if (confirm(message)) {
            // AJAX isteği gönder
            fetch('ajax/bulk-actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: action,
                    ids: ids,
                    csrf_token: getCsrfToken()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
            });
        }
    }
    
    function performSearch(query) {
        if (query.trim()) {
            // Arama sayfasına yönlendir veya AJAX ile sonuçları getir
            window.location.href = `search.php?q=${encodeURIComponent(query)}`;
        }
    }
    
    function getCsrfToken() {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        return csrfMeta ? csrfMeta.getAttribute('content') : '';
    }
    
    function showAlert(message, type = 'info') {
        // Tailwind CSS alert oluştur
        const bgColor = type === 'success' ? 'bg-green-50 border-green-200' : (type === 'error' ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200');
        const textColor = type === 'success' ? 'text-green-800' : (type === 'error' ? 'text-red-800' : 'text-blue-800');
        const iconColor = type === 'success' ? 'text-green-500' : (type === 'error' ? 'text-red-500' : 'text-blue-500');
        const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-triangle' : 'info-circle');
        
        const alertId = 'alert-' + Date.now();
        const alertHTML = `
            <div id="${alertId}" class="fixed top-4 right-4 z-50 max-w-sm w-full ${bgColor} border rounded-xl p-4 shadow-lg transition-all duration-300 transform translate-x-0">
                <div class="flex items-center">
                    <i class="fas fa-${icon} ${iconColor} mr-3"></i>
                    <div class="flex-1 ${textColor} font-medium">${message}</div>
                    <button type="button" class="ml-2 ${textColor} hover:${textColor.replace('800', '900')} transition-colors" onclick="document.getElementById('${alertId}').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('afterbegin', alertHTML);
        
        // 5 saniye sonra otomatik kapat
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }

    // Global fonksiyonları window objesine ekle
    window.AdminPanel = {
        showAlert: showAlert,
        performBulkAction: performBulkAction,
        updateBulkActions: updateBulkActions
    };

    // jQuery uyumluluğu
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            // DataTables başlatma
            if ($.fn.DataTable) {
                $('.data-table').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json'
                    },
                    pageLength: 25,
                    order: [[0, 'desc']],
                    columnDefs: [
                        { orderable: false, targets: 'no-sort' }
                    ]
                });
            }

            // Select2 başlatma
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Seçiniz...',
                    allowClear: true
                });
            }

            // Summernote başlatma
            if ($.fn.summernote) {
                $('.summernote').summernote({
                    height: 300,
                    lang: 'tr-TR',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            }

            // DatePicker başlatma
            if ($.fn.datepicker) {
                $('.datepicker').datepicker({
                    format: 'dd.mm.yyyy',
                    language: 'tr',
                    autoclose: true,
                    todayHighlight: true
                });
            }
        });
    }

})();

/**
 * File Upload fonksiyonları
 */
function initFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const files = this.files;
            const preview = this.parentNode.querySelector('.file-preview');
            
            if (preview && files.length > 0) {
                preview.innerHTML = '';
                
                Array.from(files).forEach(function(file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-thumbnail me-2 mb-2';
                            img.style.maxWidth = '100px';
                            img.style.maxHeight = '100px';
                            preview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    });
}

/**
 * Chart fonksiyonları (ApexCharts için)
 */
function initCharts() {
    // Dashboard için basit chart
    if (typeof ApexCharts !== 'undefined') {
        const chartElement = document.querySelector('#dashboard-chart');
        if (chartElement) {
            const options = {
                series: [{
                    name: 'Ürünler',
                    data: [30, 40, 35, 50, 49, 60, 70, 91, 125]
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#7367f0'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                grid: {
                    borderColor: '#e7eaed'
                },
                xaxis: {
                    categories: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl']
                }
            };
            
            const chart = new ApexCharts(chartElement, options);
            chart.render();
        }
    }
}

// Sayfa yüklendiğinde çalıştır
document.addEventListener('DOMContentLoaded', function() {
    initFileUpload();
    initCharts();
});
