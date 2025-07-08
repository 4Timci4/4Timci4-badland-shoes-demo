/**
 * Product Image Manager
 * Varyant bazlı görsel yönetimi
 */
class ProductImageManager {
    constructor(productId) {
        this.productId = productId;
        this.currentColorId = 'default';
        this.productImages = window.productImagesByColor || {};
        // Sadece varyant renklerini kullan
        this.variantColors = window.variantColors || [];
        this.allColors = window.allColors || []; // Eski uyumluluk için sakla
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initColorTabs();
    }
    
    bindEvents() {
        // Color tab switching
        document.querySelectorAll('.color-tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const colorId = e.currentTarget.dataset.colorId;
                this.switchColorTab(colorId);
            });
        });
        
        // Quick upload button
        document.getElementById('quick-upload-btn')?.addEventListener('click', () => {
            this.openFileDialog();
        });
        
        // Manage images buttons
        document.querySelectorAll('.manage-images-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const colorId = e.currentTarget.dataset.colorId;
                const colorName = e.currentTarget.dataset.colorName;
                this.openImageManager(colorId, colorName);
            });
        });
        
        // Upload for specific color
        document.querySelectorAll('.upload-for-color-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const colorId = e.currentTarget.dataset.colorId;
                this.openFileDialog(colorId);
            });
        });
        
        // Set primary image
        document.querySelectorAll('.set-primary-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const imageId = e.currentTarget.dataset.imageId;
                this.setPrimaryImage(imageId);
            });
        });
        
        // Delete image
        document.querySelectorAll('.delete-image-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const imageId = e.currentTarget.dataset.imageId;
                this.deleteImage(imageId);
            });
        });
        
        // File input change
        document.getElementById('hidden-file-input').addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.uploadFiles(e.target.files);
            }
        });
    }
    
    switchColorTab(colorId) {
        // Update active tab
        document.querySelectorAll('.color-tab-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        
        const activeTab = document.querySelector(`[data-color-id="${colorId}"]`);
        if (activeTab) {
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-500', 'text-blue-600');
        }
        
        this.currentColorId = colorId;
        this.renderColorImages(colorId);
    }
    
    renderColorImages(colorId) {
        const container = document.getElementById('color-images-content');
        const images = this.productImages[colorId] || [];
        
        // Clear existing panels
        container.querySelectorAll('.color-images-panel').forEach(panel => {
            panel.style.display = 'none';
        });
        
        // Show or create panel for this color
        let panel = container.querySelector(`[data-color-id="${colorId}"]`);
        if (!panel) {
            panel = this.createColorPanel(colorId, images);
            container.appendChild(panel);
        }
        
        panel.style.display = 'block';
    }
    
    createColorPanel(colorId, images) {
        const panel = document.createElement('div');
        panel.className = 'color-images-panel';
        panel.setAttribute('data-color-id', colorId);
        
        if (images.length > 0) {
            panel.innerHTML = `
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
                    ${images.map(image => this.createImageHTML(image)).join('')}
                </div>
            `;
        } else {
            const colorName = this.getColorName(colorId);
            panel.innerHTML = `
                <div class="text-center py-6 md:py-8 text-gray-500">
                    <i class="fas fa-image text-2xl md:text-3xl mb-3 md:mb-4"></i>
                    <p class="text-sm md:text-base mb-3 md:mb-4">${colorName} için henüz görsel yüklenmemiş</p>
                    <button type="button"
                            class="upload-for-color-btn px-3 md:px-4 py-2 bg-blue-600 text-white text-sm md:text-base rounded-lg hover:bg-blue-700"
                            data-color-id="${colorId}">
                        <i class="fas fa-plus mr-1 md:mr-2"></i>Görsel Ekle
                    </button>
                </div>
            `;
            
            // Re-bind events for new buttons
            panel.querySelector('.upload-for-color-btn')?.addEventListener('click', (e) => {
                const targetColorId = e.currentTarget.dataset.colorId;
                this.openFileDialog(targetColorId);
            });
        }
        
        return panel;
    }
    
    createImageHTML(image) {
        return `
            <div class="relative group">
                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                    <img src="${image.thumbnail_url || image.image_url}"
                         alt="${image.alt_text || ''}"
                         class="w-full h-full object-cover">
                </div>
                
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                    <div class="flex flex-col sm:flex-row gap-1 sm:gap-2 p-2">
                        ${image.is_primary ?
                            '<span class="bg-yellow-500 text-white text-xs px-1.5 py-1 rounded text-center">Primary</span>' :
                            `<button type="button" class="set-primary-btn bg-yellow-500 text-white text-xs px-1.5 py-1 rounded hover:bg-yellow-600 whitespace-nowrap" data-image-id="${image.id}">Primary Yap</button>`
                        }
                        
                        <button type="button"
                                class="delete-image-btn bg-red-500 text-white text-xs px-1.5 py-1 rounded hover:bg-red-600"
                                data-image-id="${image.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    getColorName(colorId) {
        if (colorId === 'default') return 'Varsayılan';
        
        // Önce varyant renklerinde ara
        const variantColor = this.variantColors.find(c => c.id == colorId);
        if (variantColor) return variantColor.name;
        
        // Bulamazsa tüm renklerde ara (eski uyumluluk için)
        const color = this.allColors.find(c => c.id == colorId);
        return color ? color.name : 'Bilinmeyen Renk';
    }
    
    openFileDialog(specificColorId = null) {
        const fileInput = document.getElementById('hidden-file-input');
        fileInput.setAttribute('data-target-color', specificColorId || this.currentColorId);
        fileInput.click();
    }
    
    openImageManager(colorId, colorName) {
        // Open image management modal or redirect to advanced manager
        const url = `product-image-upload.php?product_id=${this.productId}&color_id=${colorId}`;
        window.open(url, '_blank');
    }
    
    async uploadFiles(files) {
        const targetColor = document.getElementById('hidden-file-input').dataset.targetColor || this.currentColorId;
        const progressContainer = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        
        // Show progress
        if (progressContainer) {
            progressContainer.classList.remove('hidden');
        }
        
        try {
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            formData.append('action', 'upload_images');
            formData.append('product_id', this.productId);
            formData.append('color_id', targetColor);
            
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }
            
            // Simulated progress for better UX
            let progress = 0;
            const progressInterval = setInterval(() => {
                if (progress < 80 && progressBar) {
                    progress += 10;
                    progressBar.style.width = progress + '%';
                }
            }, 200);
            
            const response = await fetch('ajax/image-upload.php', {
                method: 'POST',
                body: formData
            });
            
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            
            const result = await response.json();
            
            if (result.success) {
                // Update local data and refresh display
                this.productImages[targetColor] = result.images;
                this.renderColorImages(targetColor);
                this.updateImageCounts();
                
                // Show success message
                this.showMessage('Görseller başarıyla yüklendi!', 'success');
            } else {
                throw new Error(result.message || 'Yükleme hatası');
            }
            
        } catch (error) {
            console.error('Upload error:', error);
            this.showMessage('Görsel yükleme hatası: ' + error.message, 'error');
        } finally {
            // Hide progress
            progressContainer.classList.add('hidden');
            progressBar.style.width = '0%';
            
            // Clear file input
            document.getElementById('hidden-file-input').value = '';
        }
    }
    
    async setPrimaryImage(imageId) {
        try {
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            formData.append('action', 'set_primary');
            formData.append('image_id', imageId);
            formData.append('product_id', this.productId);
            
            const response = await fetch('ajax/image-upload.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Refresh current color panel
                await this.refreshColorData();
                this.showMessage('Ana görsel güncellendi!', 'success');
            } else {
                throw new Error(result.message);
            }
            
        } catch (error) {
            console.error('Set primary error:', error);
            this.showMessage('Hata: ' + error.message, 'error');
        }
    }
    
    async deleteImage(imageId) {
        if (!confirm('Bu görseli silmek istediğinizden emin misiniz?')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            formData.append('action', 'delete_image');
            formData.append('image_id', imageId);
            formData.append('product_id', this.productId);
            
            const response = await fetch('ajax/image-upload.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Refresh current color panel
                await this.refreshColorData();
                this.showMessage('Görsel silindi!', 'success');
            } else {
                throw new Error(result.message);
            }
            
        } catch (error) {
            console.error('Delete error:', error);
            this.showMessage('Silme hatası: ' + error.message, 'error');
        }
    }
    
    async refreshColorData() {
        try {
            const response = await fetch(`ajax/image-upload.php?action=get_images&product_id=${this.productId}`);
            const result = await response.json();
            
            if (result.success) {
                this.productImages = result.imagesByColor;
                this.renderColorImages(this.currentColorId);
                this.updateImageCounts();
            }
        } catch (error) {
            console.error('Refresh data error:', error);
        }
    }
    
    updateImageCounts() {
        // Update tab badges
        document.querySelectorAll('.color-tab-btn').forEach(btn => {
            const colorId = btn.dataset.colorId;
            const badge = btn.querySelector('.bg-gray-100');
            if (badge) {
                const count = (this.productImages[colorId] || []).length;
                badge.textContent = count;
            }
        });
        
        // Update total count
        const totalImages = Object.values(this.productImages).reduce((total, images) => total + images.length, 0);
        const totalCountElement = document.querySelector('.text-2xl.font-bold.text-blue-600');
        if (totalCountElement) {
            totalCountElement.textContent = totalImages;
        }
    }
    
    showMessage(message, type = 'info') {
        // Create flash message
        const alertClass = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' :
                          type === 'error' ? 'bg-red-50 border-red-200 text-red-800' :
                          'bg-blue-50 border-blue-200 text-blue-800';
        
        const iconClass = type === 'success' ? 'fa-check-circle text-green-500' :
                         type === 'error' ? 'fa-exclamation-triangle text-red-500' :
                         'fa-info-circle text-blue-500';
        
        const messageHtml = `
            <div class="${alertClass} border rounded-xl p-4 flex items-start mb-4 auto-dismiss">
                <i class="fas ${iconClass} mr-3 mt-0.5"></i>
                <div class="font-medium">${message}</div>
            </div>
        `;
        
        // Insert at top of form
        const form = document.getElementById('productEditForm');
        form.insertAdjacentHTML('afterbegin', messageHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = form.querySelector('.auto-dismiss');
            if (alert) alert.remove();
        }, 5000);
    }
    
    initColorTabs() {
        // Set default active tab
        this.switchColorTab('default');
    }
}

// Export for use in main file
window.ProductImageManager = ProductImageManager;
