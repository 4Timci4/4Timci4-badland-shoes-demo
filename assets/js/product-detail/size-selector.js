// Beden seçimi işlevselliği
export function initializeSizeSelector(state, variantManager) {
    // Beden seçimi fonksiyonu
    function selectSize(sizeId, sizeValue) {
        // Önceki seçimi temizle
        document.querySelectorAll('.size-option').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white', 'border-primary');
            btn.classList.add('border-gray-300');
        });
        
        // Yeni seçimi işaretle
        const selectedButton = document.querySelector(`.size-option[data-size="${sizeId}"]`);
        if (selectedButton) {
            selectedButton.classList.remove('border-gray-300');
            selectedButton.classList.add('bg-primary', 'text-white', 'border-primary');
        }
        
        state.selectedSize = sizeId;
        const selectedSizeElement = document.getElementById('selected-size');
        if (selectedSizeElement) {
            selectedSizeElement.textContent = sizeValue;
        }
        
        // Stok durumunu güncelle
        variantManager.updateStockStatus(state.selectedColor, state.selectedSize, window.productVariantsData);
        
        // Seçili varyantı güncelle
        updateSelectedVariant();
    }
    
    // Seçili varyantı güncelle
    function updateSelectedVariant() {
        if (state.selectedColor && state.selectedSize) {
            const variant = window.productVariantsData.find(v =>
                v.color_id === state.selectedColor && v.size_id === state.selectedSize
            );
            
            if (variant) {
                state.currentSelectedVariantId = variant.id;
                console.log('Varyant bulundu:', variant.id, 'Favori mi:', window.favoriteVariantIds.includes(variant.id));
                
                // Favori durumunu güncelle (favoriteManager tarafından yapılacak)
                const event = new CustomEvent('variantSelected', { detail: { variantId: variant.id } });
                document.dispatchEvent(event);
            } else {
                console.log('Eşleşen varyant bulunamadı');
            }
        } else {
            console.log('Renk veya beden seçilmemiş');
        }
    }
    
    // Beden seçimi event listener'ları
    document.querySelectorAll('.size-option').forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;
            
            const sizeId = parseInt(this.dataset.size);
            const sizeValue = this.dataset.sizeValue;
            
            selectSize(sizeId, sizeValue);
        });
    });
    
    // Public API
    return {
        selectSize: selectSize,
        updateSelectedVariant: updateSelectedVariant
    };
}