
export function initializeSizeSelector(state, variantManager) {
    
    function selectSize(sizeId, sizeValue) {
        
        document.querySelectorAll('.size-option').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white', 'border-primary');
            btn.classList.add('border-gray-300');
        });
        
        
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
        
        
        variantManager.updateStockStatus(state.selectedColor, state.selectedSize, window.productVariantsData);
        
        
        updateSelectedVariant();
    }
    
    
    function updateSelectedVariant() {
        if (state.selectedColor && state.selectedSize) {
            const variant = window.productVariantsData.find(v =>
                v.color_id === state.selectedColor && v.size_id === state.selectedSize
            );
            
            if (variant) {
                state.currentSelectedVariantId = variant.id;
                
                
                const event = new CustomEvent('variantSelected', { detail: { variantId: variant.id } });
                document.dispatchEvent(event);
            }
        }
    }
    
    
    document.querySelectorAll('.size-option').forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;
            
            const sizeId = parseInt(this.dataset.size);
            const sizeValue = this.dataset.sizeValue;
            
            selectSize(sizeId, sizeValue);
        });
    });
    
    
    return {
        selectSize: selectSize,
        updateSelectedVariant: updateSelectedVariant
    };
}