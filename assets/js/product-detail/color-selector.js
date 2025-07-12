// Renk seçimi işlevselliği
export function initializeColorSelector(state, productColors, imageManager, variantManager) {
    // Renk seçme fonksiyonu
    async function selectColor(colorId, colorName) {
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

        // API'den yeni varyant verilerini çek
        try {
            const productId = window.productData.id;
            const response = await fetch(`/api/get_variants.php?product_id=${productId}&color_id=${colorId}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const newVariants = await response.json();

            // Global veriyi ve state'i güncelle
            window.productVariantsData = newVariants;
            variantManager.reinitialize(newVariants);

            // Görselleri güncelle
            if (typeof updateImagesForColor === 'function') {
                updateImagesForColor(colorId);
            }
            
            // Tüm bedenlerin görünümünü güncelle
            updateAllSizeButtons();
            
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
            const firstAvailableSizeButton = document.querySelector('.size-option:not(.unavailable)');
            if (firstAvailableSizeButton) {
                firstAvailableSizeButton.click(); // Otomatik olarak ilk uygun bedeni seç
            } else {
                variantManager.updateStockStatus(state.selectedColor, state.selectedSize, window.productVariantsData);
            }
        } catch (error) {
            console.error('Varyant verileri alınırken hata oluştu:', error);
        }
    }
    
    // Tüm bedenleri göster, stokta olmayanların üstünü çiz
    function updateAllSizeButtons() {
        if (!state.selectedColor) return;
        
        // Tüm bedenleri al ve durumlarını işaretle
        const sizesWithAvailability = variantManager.getAllSizesWithAvailability(state.selectedColor, window.productSizesData);
        
        // Beden butonlarını güncelle
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
    
    // Renk seçimi için event listener'lar ekle
    document.querySelectorAll('.color-option').forEach(button => {
        // Hover olduğunda önizleme göster ve bedenleri güncelle
        button.addEventListener('mouseenter', async function() {
            const colorId = parseInt(this.dataset.colorId);
            
            // Görsel önizlemesi göster
            const firstImage = imageManager.previewColorImages(colorId);
            if (firstImage) {
                imageManager.changeMainImage(firstImage, null, true); // true parametresi önizleme modunu belirtir
            }

            // API'den geçici varyant verilerini çek
            try {
                const productId = window.productData.id;
                const response = await fetch(`/api/get_variants.php?product_id=${productId}&color_id=${colorId}`);
                if (!response.ok) return; // Hata durumunda sessizce devam et
                const tempVariants = await response.json();

                // Geçici verilerle bedenlerin stok durumunu hesapla
                const tempAvailableSizeIds = [...new Set(tempVariants.map(v => parseInt(v.size_id)))];
                
                // Beden butonlarını geçici olarak güncelle
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
                // Hata durumunda önizleme çalışmaz, sorun değil.
            }
        });
        
        // Hover'dan çıkıldığında seçili rengin görsellerini ve bedenlerini göster
        button.addEventListener('mouseleave', function() {
            if (state.selectedColor) {
                // Görselleri güncelle
                if (typeof updateImagesForColor === 'function') {
                    updateImagesForColor(state.selectedColor);
                }
                
                // Bedenleri güncelle
                updateAllSizeButtons();
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
        updateAllSizeButtons: updateAllSizeButtons
    };
}