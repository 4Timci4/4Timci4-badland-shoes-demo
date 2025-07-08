/**
 * Varyant Yönetimi JavaScript Modülü
 * Ürün varyantlarının AJAX ile eklenmesi, güncellenmesi ve silinmesi
 */

class VariantManagement {
    constructor(productId) {
        this.productId = productId;
        this.init();
    }

    init() {
        this.addEventListeners();
        this.initExistingVariants();
    }

    addEventListeners() {
        // Add New Variant
        const addBtn = document.getElementById('add-variant-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.handleAddVariant());
        }

        // Bulk Actions
        const bulkActivateBtn = document.getElementById('bulk-activate-btn');
        const bulkDeactivateBtn = document.getElementById('bulk-deactivate-btn');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

        if (bulkActivateBtn) {
            bulkActivateBtn.addEventListener('click', () => {
                this.bulkUpdateVariants({is_active: true});
            });
        }

        if (bulkDeactivateBtn) {
            bulkDeactivateBtn.addEventListener('click', () => {
                this.bulkUpdateVariants({is_active: false});
            });
        }

        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', () => {
                if (confirm('Tüm varyantları silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                    this.bulkDeleteVariants();
                }
            });
        }
    }

    initExistingVariants() {
        // Save Variant Buttons
        document.querySelectorAll('.save-variant-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleSaveVariant(e));
        });

        // Delete Variant Buttons
        document.querySelectorAll('.delete-variant-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleDeleteVariant(e));
        });

        // Update total stock when stock inputs change
        document.querySelectorAll('.variant-stock').forEach(input => {
            input.addEventListener('input', () => this.updateTotalStock());
        });
    }

    handleAddVariant() {
        const colorId = document.getElementById('new-variant-color').value;
        const sizeId = document.getElementById('new-variant-size').value;
        const price = document.getElementById('new-variant-price').value;
        const stock = document.getElementById('new-variant-stock').value;
        const isActive = document.getElementById('new-variant-active').checked;

        if (!colorId || !sizeId || !price) {
            alert('Lütfen renk, beden ve fiyat alanlarını doldurun.');
            return;
        }

        const data = {
            model_id: this.productId,
            color_id: parseInt(colorId),
            size_id: parseInt(sizeId),
            price: parseFloat(price),
            stock_quantity: parseInt(stock) || 0,
            is_active: isActive
        };

        this.addVariant(data);
    }

    handleSaveVariant(event) {
        const variantId = event.target.dataset.variantId;
        const row = document.querySelector(`tr[data-variant-id="${variantId}"]`);
        
        const price = row.querySelector('.variant-price').value;
        const stock = row.querySelector('.variant-stock').value;
        const isActive = row.querySelector('.variant-active').checked;

        const data = {
            price: parseFloat(price),
            stock_quantity: parseInt(stock) || 0,
            is_active: isActive
        };

        this.updateVariant(variantId, data);
    }

    handleDeleteVariant(event) {
        const variantId = event.target.dataset.variantId;
        
        if (confirm('Bu varyantı silmek istediğinizden emin misiniz?')) {
            this.deleteVariant(variantId);
        }
    }

    // AJAX Functions
    addVariant(data) {
        // Loading state
        const addBtn = document.getElementById('add-variant-btn');
        const originalBtnText = addBtn.innerHTML;
        addBtn.disabled = true;
        addBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Ekleniyor...';

        fetch('variant-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'add',
                data: data
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                this.showNotification('Varyant başarıyla eklendi.', 'success');
                
                // Tabloya yeni satır ekle
                this.addVariantToTable(result.variant);
                
                // Form alanlarını temizle
                this.clearVariantForm();
                
                // Toplam stok güncelle
                this.updateTotalStock();
            } else {
                this.showNotification(result.message || 'Varyant eklenirken hata oluştu.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('Bir hata oluştu.', 'error');
        })
        .finally(() => {
            // Reset button
            addBtn.disabled = false;
            addBtn.innerHTML = originalBtnText;
        });
    }

    updateVariant(variantId, data) {
        fetch('variant-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'update',
                variant_id: variantId,
                data: data
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                this.showNotification('Varyant başarıyla güncellendi.', 'success');
            } else {
                this.showNotification(result.message || 'Varyant güncellenirken hata oluştu.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('Bir hata oluştu.', 'error');
        });
    }

    deleteVariant(variantId) {
        fetch('variant-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'delete',
                variant_id: variantId
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                this.showNotification('Varyant başarıyla silindi.', 'success');
                document.querySelector(`tr[data-variant-id="${variantId}"]`).remove();
                this.updateTotalStock();
            } else {
                this.showNotification(result.message || 'Varyant silinirken hata oluştu.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('Bir hata oluştu.', 'error');
        });
    }

    bulkUpdateVariants(data) {
        fetch('variant-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'bulk_update',
                product_id: this.productId,
                data: data
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                this.showNotification('Varyantlar başarıyla güncellendi.', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showNotification(result.message || 'Varyantlar güncellenirken hata oluştu.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('Bir hata oluştu.', 'error');
        });
    }

    bulkDeleteVariants() {
        fetch('variant-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'bulk_delete',
                product_id: this.productId
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                this.showNotification('Tüm varyantlar başarıyla silindi.', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showNotification(result.message || 'Varyantlar silinirken hata oluştu.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('Bir hata oluştu.', 'error');
        });
    }

    // Helper Functions
    addVariantToTable(variant) {
        const tbody = document.querySelector('tbody.bg-white.divide-y.divide-gray-200');
        if (!tbody) {
            // Eğer tablo yoksa, önce empty state'i kaldır ve tablo oluştur
            this.createVariantTable();
            return this.addVariantToTable(variant);
        }

        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        row.setAttribute('data-variant-id', variant.id);
        
        row.innerHTML = `
            <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded-full border border-gray-300" 
                         style="background-color: ${variant.color_hex || '#cccccc'}"></div>
                    <span class="ml-3 text-sm text-gray-900">${variant.color_name || 'Renk Yok'}</span>
                </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${variant.size_value || 'Beden Yok'} ${variant.size_type || ''}
                </span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900 font-mono">${variant.sku || ''}</span>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                    <input type="number" 
                           value="${variant.price || 0}" 
                           step="0.01" 
                           min="0"
                           class="variant-price w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           data-variant-id="${variant.id}">
                    <span class="text-xs text-gray-500">₺</span>
                </div>
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <input type="number" 
                       value="${variant.stock_quantity || 0}" 
                       min="0"
                       class="variant-stock w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       data-variant-id="${variant.id}">
            </td>
            <td class="px-4 py-4 whitespace-nowrap">
                <label class="inline-flex items-center">
                    <input type="checkbox" 
                           ${variant.is_active ? 'checked' : ''}
                           class="variant-active form-checkbox h-4 w-4 text-primary-600"
                           data-variant-id="${variant.id}">
                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                </label>
            </td>
            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                <button type="button" 
                        class="save-variant-btn text-green-600 hover:text-green-900 mr-3"
                        data-variant-id="${variant.id}">
                    <i class="fas fa-save"></i>
                </button>
                <button type="button" 
                        class="delete-variant-btn text-red-600 hover:text-red-900"
                        data-variant-id="${variant.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
        
        // Yeni eklenen satırdaki butonlara event listener ekle
        this.addEventListenersToVariantRow(row);
        
        // Varyant sayısını güncelle
        this.updateVariantCount();
    }

    createVariantTable() {
        const emptyState = document.querySelector('.text-center.py-8.bg-gray-50.rounded-xl');
        if (emptyState) {
            emptyState.outerHTML = `
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-list mr-2"></i>Mevcut Varyantlar (<span id="variant-count">0</span>)
                    </h4>
                    
                    <div class="overflow-hidden border border-gray-200 rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Renk</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Beden</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fiyat</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }
    }

    addEventListenersToVariantRow(row) {
        const variantId = row.getAttribute('data-variant-id');
        
        // Save button
        const saveBtn = row.querySelector('.save-variant-btn');
        saveBtn.addEventListener('click', (e) => this.handleSaveVariant(e));

        // Delete button
        const deleteBtn = row.querySelector('.delete-variant-btn');
        deleteBtn.addEventListener('click', (e) => this.handleDeleteVariant(e));

        // Stock input change
        const stockInput = row.querySelector('.variant-stock');
        stockInput.addEventListener('input', () => this.updateTotalStock());
    }

    clearVariantForm() {
        document.getElementById('new-variant-color').value = '';
        document.getElementById('new-variant-size').value = '';
        document.getElementById('new-variant-price').value = window.productBasePrice || 0;
        document.getElementById('new-variant-stock').value = '0';
        document.getElementById('new-variant-active').checked = true;
    }

    updateVariantCount() {
        const variantCountElement = document.getElementById('variant-count');
        if (variantCountElement) {
            const rowCount = document.querySelectorAll('tbody.bg-white.divide-y.divide-gray-200 tr').length;
            variantCountElement.textContent = rowCount;
        }
    }

    updateTotalStock() {
        let totalStock = 0;
        document.querySelectorAll('.variant-stock').forEach(input => {
            totalStock += parseInt(input.value) || 0;
        });
        
        const totalStockElement = document.querySelector('.text-2xl.font-bold.text-green-600');
        if (totalStockElement) {
            totalStockElement.textContent = totalStock;
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        // Set colors based on type
        if (type === 'success') {
            notification.classList.add('bg-green-500', 'text-white');
        } else if (type === 'error') {
            notification.classList.add('bg-red-500', 'text-white');
        } else {
            notification.classList.add('bg-blue-500', 'text-white');
        }
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} mr-3"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Animate out and remove
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Global access için
window.VariantManagement = VariantManagement;
