// Renk-beden grid'i işlevselliği
export function initializeColorSizeGrid(state, productColors, productSizesData, variantManager, colorSelector, sizeSelector) {
    // Renk-beden grid'ini oluştur
    function createColorSizeGrid() {
        const gridContainer = document.getElementById('color-size-grid');
        if (!gridContainer) return;
        
        let gridHTML = '<div class="grid grid-cols-4 gap-2 mt-4">';
        
        productColors.forEach(color => {
            // Ürüne tanımlı bedenleri al ve durumlarını işaretle
            const sizesWithAvailability = variantManager.getAllSizesWithAvailability(color.id, productSizesData);
            
            sizesWithAvailability.forEach(size => {
                const isInStock = size.isAvailable;
                const isSelected = state.selectedColor === color.id && state.selectedSize === size.id;
                
                gridHTML += `
                    <div
                        class="variant-grid-item p-2 border rounded-md text-center cursor-pointer ${isInStock ? '' : 'opacity-50 line-through'} ${isSelected ? 'border-primary bg-primary/10' : 'border-gray-300'}"
                        data-color-id="${color.id}"
                        data-size-id="${size.id}"
                        data-color-name="${color.name}"
                        data-size-value="${size.size_value}"
                        ${isInStock ? '' : 'disabled'}
                    >
                        <div class="w-4 h-4 rounded-full mx-auto mb-1" style="background-color: ${color.hex_code}"></div>
                        <span class="text-xs font-medium">${size.size_value}</span>
                    </div>
                `;
            });
        });
        
        gridHTML += '</div>';
        gridContainer.innerHTML = gridHTML;
        
        // Grid item'lara event listener ekle
        document.querySelectorAll('.variant-grid-item:not([disabled])').forEach(item => {
            item.addEventListener('click', function() {
                const colorId = parseInt(this.dataset.colorId);
                const sizeId = parseInt(this.dataset.sizeId);
                const colorName = this.dataset.colorName;
                const sizeValue = this.dataset.sizeValue;
                
                // Önce rengi seç (eğer farklıysa)
                if (colorId !== state.selectedColor) {
                    colorSelector.selectColor(colorId, colorName);
                }
                
                // Sonra bedeni seç
                sizeSelector.selectSize(sizeId, sizeValue);
                
                // Grid'i güncelle
                updateColorSizeGrid();
            });
        });
    }
    
    // Renk-beden grid'ini güncelle
    function updateColorSizeGrid() {
        createColorSizeGrid();
    }
    
    // İlk grid'i oluştur
    createColorSizeGrid();
    
    // Public API
    return {
        updateColorSizeGrid: updateColorSizeGrid
    };
}