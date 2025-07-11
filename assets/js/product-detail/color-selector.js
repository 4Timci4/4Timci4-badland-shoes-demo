// Renk seçimi işlevselliği
export function initializeColorSelector(state, productColors, imageManager, variantManager) {
    // Renk seçme fonksiyonu
    function selectColor(colorId, colorName) {
        // Önceki seçimi temizle
        document.querySelectorAll('.color-option').forEach(btn => {
            btn.classList.remove('border-secondary');
            btn.classList.add('border-gray-300');
        });
        
        // Yeni seçimi işaretle
        const selectedButton = document.querySelector('.color-option[data-color-id="' + colorId + '"]');
        if (selectedButton) {
            selectedButton.classList.remove('border-gray-300');
            selectedButton.classList.add('border-secondary');
        }
        
        state.selectedColor = colorId;
        const selectedColorElement = document.getElementById('selected-color');
        if (selectedColorElement) {
            selectedColorElement.textContent = colorName;
        }
        
        // Görselleri güncelle
        if (typeof updateImagesForColor === 'function') {
            updateImagesForColor(colorId);
        }
        
        // Önce bedenlerin görünümünü güncelle
        updateSizeButtonsBasedOnStock();
        
        // Beden seçimini sıfırla
        state.selectedSize = null;
        const selectedSizeElement = document.getElementById('selected-size');
        if (selectedSizeElement) {
            selectedSizeElement.textContent = '-';
        }
        
        document.querySelectorAll('.size-option').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white', 'border-primary');
            btn.classList.add('border-gray-300');
        });
        
        // Stokta olan ilk bedeni otomatik seç
        const firstAvailableSizeButton = document.querySelector('.size-option:not([disabled])');
        if (firstAvailableSizeButton) {
            firstAvailableSizeButton.click(); // Otomatik olarak ilk uygun bedeni seç
        } else {
            variantManager.updateStockStatus(state.selectedColor, state.selectedSize, window.productVariantsData);
        }
    }
    
    // Stok olmayan bedenlerin üstünü çiz
    function updateSizeButtonsBasedOnStock() {
        if (!state.selectedColor) return;
        
        document.querySelectorAll('.size-option').forEach(button => {
            const sizeId = parseInt(button.dataset.size);
            const variant = variantManager.findVariant(state.selectedColor, sizeId);
            
            if (!variant || variant.stock_quantity <= 0) {
                button.classList.add('line-through', 'opacity-50');
                button.disabled = true;
            } else {
                button.classList.remove('line-through', 'opacity-50');
                button.disabled = false;
            }
        });
    }
    
    // Renk seçimi için event listener'lar ekle
    document.querySelectorAll('.color-option').forEach(button => {
        // Hover olduğunda önizleme göster
        button.addEventListener('mouseenter', function() {
            const colorId = parseInt(this.dataset.colorId);
            const firstImage = imageManager.previewColorImages(colorId);
            if (firstImage) {
                imageManager.changeMainImage(firstImage, null, true); // true parametresi önizleme modunu belirtir
            }
        });
        
        // Hover'dan çıkıldığında seçili rengin görsellerini göster
        button.addEventListener('mouseleave', function() {
            if (state.selectedColor) {
                if (typeof updateImagesForColor === 'function') {
                    updateImagesForColor(state.selectedColor);
                }
            }
        });
        
        // Tıklandığında rengi seç
        button.addEventListener('click', function() {
            const colorId = parseInt(this.dataset.colorId);
            const colorName = this.dataset.colorName;
            const colorSlug = this.dataset.colorSlug;
            
            // URL'yi güncelle (soft geçiş)
            const url = new URL(window.location);
            url.searchParams.set('color', colorSlug);
            history.pushState({colorId: colorId, colorSlug: colorSlug}, '', url);
            
            // Renk seçimini güncelle
            selectColor(colorId, colorName);
        });
    });
    
    // Public API
    return {
        selectColor: selectColor,
        updateSizeButtonsBasedOnStock: updateSizeButtonsBasedOnStock
    };
}