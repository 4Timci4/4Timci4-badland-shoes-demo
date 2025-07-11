// Varyant verilerini yönetir
export function initializeVariantData(state, productVariants) {
    // Varyant verilerini indeksle
    productVariants.forEach(variant => {
        const key = `${variant.color_id}-${variant.size_id}`;
        state.variantMap.set(key, variant);
        
        // Renge göre varyantları grupla
        if (!state.variantsByColor.has(variant.color_id)) {
            state.variantsByColor.set(variant.color_id, []);
        }
        state.variantsByColor.get(variant.color_id).push(variant);
        
        // Bedene göre varyantları grupla
        if (!state.variantsBySize.has(variant.size_id)) {
            state.variantsBySize.set(variant.size_id, []);
        }
        state.variantsBySize.get(variant.size_id).push(variant);
    });
    
    // Public API
    return {
        findVariant: (colorId, sizeId) => {
            const key = `${colorId}-${sizeId}`;
            return state.variantMap.get(key);
        },
        
        getAvailableSizesForColor: (colorId, sizeData) => {
            const variants = state.variantsByColor.get(colorId) || [];
            const sizeIds = [...new Set(variants.map(v => v.size_id))];
            
            const sizes = [];
            sizeIds.forEach(sizeId => {
                const sizeInfo = sizeData.find(s => s.id === sizeId);
                if (sizeInfo) sizes.push(sizeInfo);
            });
            
            return sizes.sort((a, b) => a.size_value.localeCompare(b.size_value, undefined, {numeric: true}));
        },
        
        updateStockStatus: (selectedColor, selectedSize, productVariants) => {
            const stockStatus = document.getElementById('stock-status');
            const currentPriceElement = document.getElementById('current-price');
            
            if (selectedColor && selectedSize) {
                const variant = productVariants.find(v => 
                    v.color_id === selectedColor && v.size_id === selectedSize
                );
                
                if (variant && variant.stock_quantity > 0) {
                    // Fiyatı güncelle
                    if (variant.price && currentPriceElement) {
                        currentPriceElement.textContent = '₺ ' + parseFloat(variant.price).toLocaleString('tr-TR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                    
                    if (stockStatus) {
                        stockStatus.textContent = variant.stock_quantity <= 3 ? 'Son ' + variant.stock_quantity + ' ürün!' : 'Stokta';
                        stockStatus.className = 'text-xs text-green-600';
                    }
                } else if (stockStatus) {
                    stockStatus.textContent = 'Tükendi';
                    stockStatus.className = 'text-xs text-red-600';
                }
            } else if (stockStatus) {
                stockStatus.textContent = '';
                stockStatus.className = 'text-xs text-gray-600';
            }
        }
    };
}