
export function initializeColorSizeGrid(state, productColors, productSizesData, variantManager, colorSelector, sizeSelector) {
    
    function createColorSizeGrid() {
        const gridContainer = document.getElementById('color-size-grid');
        if (!gridContainer) return;
        
        let gridHTML = '<div class="grid grid-cols-4 gap-2 mt-4">';
        
        productColors.forEach(color => {
            
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
        
        
        document.querySelectorAll('.variant-grid-item:not([disabled])').forEach(item => {
            item.addEventListener('click', function() {
                const colorId = parseInt(this.dataset.colorId);
                const sizeId = parseInt(this.dataset.sizeId);
                const colorName = this.dataset.colorName;
                const sizeValue = this.dataset.sizeValue;
                
                
                if (colorId !== state.selectedColor) {
                    colorSelector.selectColor(colorId, colorName);
                }
                
                
                sizeSelector.selectSize(sizeId, sizeValue);
                
                
                updateColorSizeGrid();
            });
        });
    }
    
    
    function updateColorSizeGrid() {
        createColorSizeGrid();
    }
    
    
    createColorSizeGrid();
    
    
    return {
        updateColorSizeGrid: updateColorSizeGrid
    };
}