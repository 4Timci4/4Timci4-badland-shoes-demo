
export function initializeColorSelector(state, productColors, imageManager, variantManager) {
    
    async function selectColor(colorId, colorName) {
        
        document.querySelectorAll('.color-option').forEach(btn => {
            btn.classList.remove('border-secondary');
            btn.classList.add('border-gray-300');
        });
        
        
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

        
        try {
            const productId = window.productData.id;
            const response = await fetch(`/api/get_variants.php?product_id=${productId}&color_id=${colorId}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const newVariants = await response.json();

            
            window.productVariantsData = newVariants;
            variantManager.reinitialize(newVariants);

            
            if (typeof updateImagesForColor === 'function') {
                updateImagesForColor(colorId);
            }
            
            
            updateAllSizeButtons();
            
            
            state.selectedSize = null;
            const selectedSizeElement = document.getElementById('selected-size');
            if (selectedSizeElement) {
                selectedSizeElement.textContent = '-';
            }
            
            document.querySelectorAll('.size-option').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white', 'border-primary');
                btn.classList.add('border-gray-300');
            });
            
            
            const firstAvailableSizeButton = document.querySelector('.size-option:not(.unavailable)');
            if (firstAvailableSizeButton) {
                firstAvailableSizeButton.click(); 
            } else {
                variantManager.updateStockStatus(state.selectedColor, state.selectedSize, window.productVariantsData);
            }
        } catch (error) {
            console.error('Varyant verileri alınırken hata oluştu:', error);
        }
    }
    
    
    function updateAllSizeButtons() {
        if (!state.selectedColor) return;
        
        
        const sizesWithAvailability = variantManager.getAllSizesWithAvailability(state.selectedColor, window.productSizesData);
        
        
        document.querySelectorAll('.size-option').forEach(button => {
            const sizeId = parseInt(button.dataset.size);
            const sizeInfo = sizesWithAvailability.find(s => s.id === sizeId);
            
            if (sizeInfo && sizeInfo.isAvailable) {
                button.classList.remove('line-through', 'opacity-50', 'unavailable');
                button.disabled = false;
            } else {
                button.classList.add('line-through', 'opacity-50', 'unavailable');
                button.disabled = true;
            }
        });
    }
    
    
    document.querySelectorAll('.color-option').forEach(button => {
        
        button.addEventListener('mouseenter', async function() {
            const colorId = parseInt(this.dataset.colorId);
            
            
            const firstImage = imageManager.previewColorImages(colorId);
            if (firstImage) {
                imageManager.changeMainImage(firstImage, null, true); 
            }

            
            try {
                const productId = window.productData.id;
                const response = await fetch(`/api/get_variants.php?product_id=${productId}&color_id=${colorId}`);
                if (!response.ok) return; 
                const tempVariants = await response.json();

                
                const tempAvailableSizeIds = [...new Set(tempVariants.map(v => parseInt(v.size_id)))];
                
                
                document.querySelectorAll('.size-option').forEach(button => {
                    const sizeId = parseInt(button.dataset.size);
                    
                    const isTempAvailable = tempAvailableSizeIds.includes(sizeId) &&
                                           tempVariants.some(v => parseInt(v.size_id) === sizeId && v.stock_quantity > 0);

                    if (isTempAvailable) {
                        button.classList.remove('line-through', 'opacity-50', 'unavailable');
                        button.disabled = false;
                    } else {
                        button.classList.add('line-through', 'opacity-50', 'unavailable');
                        button.disabled = true;
                    }
                });
            } catch (error) {
                
            }
        });
        
        
        button.addEventListener('mouseleave', function() {
            if (state.selectedColor) {
                
                if (typeof updateImagesForColor === 'function') {
                    updateImagesForColor(state.selectedColor);
                }
                
                
                updateAllSizeButtons();
            }
        });
        
        
        button.addEventListener('click', function() {
            const colorId = parseInt(this.dataset.colorId);
            const colorName = this.dataset.colorName;
            const colorSlug = this.dataset.colorSlug;
            
            
            const url = new URL(window.location);
            url.searchParams.set('color', colorSlug);
            history.pushState({colorId: colorId, colorSlug: colorSlug}, '', url);
            
            
            selectColor(colorId, colorName);
        });
    });
    
    
    return {
        selectColor: selectColor,
        updateAllSizeButtons: updateAllSizeButtons
    };
}