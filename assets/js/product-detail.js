document.addEventListener('DOMContentLoaded', function() {
    // Ürün varyantları ve renkleri global değişkenlerden al
    const productVariants = productVariantsData;
    const productColors = productColorsData;
    
    let selectedColor = null;
    let selectedSize = null;
    
    // Sayfa yüklendiğinde ilk rengi seç
    if (productColors.length > 0) {
        selectedColor = productColors[0].id;
        // Seçili rengi UI'da da göster
        const firstColorButton = document.querySelector('.color-option[data-color-id="'+selectedColor+'"]');
        if (firstColorButton) {
            firstColorButton.classList.remove('border-gray-300');
            firstColorButton.classList.add('border-secondary');
            document.getElementById('selected-color').textContent = firstColorButton.dataset.colorName;
            
            // İlk rengin görsellerini göster
            if (typeof window.updateImagesForColor === 'function') {
                window.updateImagesForColor(selectedColor);
            }
            
            // İlk rengin taban fiyatını güncelle
            updateBasePrice();
        }
    }
    
    // Stok olmayan bedenlerin üstünü çiz
    function updateSizeButtonsBasedOnStock() {
        if (!selectedColor) return;
        
        document.querySelectorAll('.size-option').forEach(button => {
            const sizeId = parseInt(button.dataset.size);
            const variant = productVariants.find(v => 
                v.color_id === selectedColor && v.size_id === sizeId
            );
            
            if (!variant || variant.stock_quantity <= 0) {
                button.classList.add('line-through', 'opacity-50');
                button.disabled = true;
            } else {
                button.classList.remove('line-through', 'opacity-50');
                button.disabled = false;
            }
        });
    }
    
    // Ana resim değiştirme
    window.changeMainImage = function(imageUrl, thumbnail) {
        document.getElementById('main-product-image').src = imageUrl;
        
        // Thumbnail border'larını güncelle
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('border-primary');
            thumb.classList.add('border-transparent');
        });
        thumbnail.classList.remove('border-transparent');
        thumbnail.classList.add('border-primary');
    };
    
    // Seçilen renge göre taban fiyatını güncelle
    function updateBasePrice() {
        if (!selectedColor) return;
        
        const currentPriceElement = document.getElementById('current-price');
        
        // Seçilen renkteki varyantları bul
        const colorVariants = productVariants.filter(v => v.color_id === selectedColor);
        
        if (colorVariants.length > 0) {
            // En düşük fiyatlı varyantı bul
            let lowestPrice = null;
            colorVariants.forEach(variant => {
                if (variant.price && (lowestPrice === null || variant.price < lowestPrice)) {
                    lowestPrice = variant.price;
                }
            });
            
            // Fiyatı güncelle
            if (lowestPrice !== null) {
                currentPriceElement.textContent = '₺ ' + parseFloat(lowestPrice).toLocaleString('tr-TR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            
            // İndirim varsa indirim göstergesini güncelle
            updateDiscountDisplay(colorVariants);
        }
    }
    
    // İndirim göstergesini güncelle
    function updateDiscountDisplay(variants) {
        const discountElement = document.querySelector('.price-section .line-through');
        const discountBadge = document.querySelector('.price-section .bg-green-500');
        
        if (!discountElement || !discountBadge) return;
        
        // İndirimli varyant bul
        const discountedVariant = variants.find(v => v.original_price && v.original_price > 0);
        
        if (discountedVariant) {
            discountElement.textContent = '₺ ' + parseFloat(discountedVariant.original_price).toLocaleString('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            discountElement.style.display = '';
            discountBadge.style.display = '';
        } else {
            discountElement.style.display = 'none';
            discountBadge.style.display = 'none';
        }
    }
    
    // Renk seçimi
    document.querySelectorAll('.color-option').forEach(button => {
        button.addEventListener('click', function() {
            // Önceki seçimi temizle
            document.querySelectorAll('.color-option').forEach(btn => {
                btn.classList.remove('border-secondary');
                btn.classList.add('border-gray-300');
            });
            
            // Yeni seçimi işaretle
            this.classList.remove('border-gray-300');
            this.classList.add('border-secondary');
            
            selectedColor = parseInt(this.dataset.colorId);
            document.getElementById('selected-color').textContent = this.dataset.colorName;
            
            // Seçilen renk değiştiğinde görselleri güncelle
            if (typeof window.updateImagesForColor === 'function') {
                window.updateImagesForColor(selectedColor);
            }
            
            // Önce bedenlerin görünümünü güncelle
            updateSizeButtonsBasedOnStock();
            
            // Seçilen renge göre fiyatı güncelle
            updateBasePrice();
            
            // Beden seçimini sıfırla
            selectedSize = null;
            document.getElementById('selected-size').textContent = '-';
            document.querySelectorAll('.size-option').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white', 'border-primary');
                btn.classList.add('border-gray-300');
            });
            
            // Stok olmayan ilk uygun bedeni otomatik seç
            const firstAvailableSizeButton = document.querySelector('.size-option:not([disabled])');
            if (firstAvailableSizeButton) {
                firstAvailableSizeButton.click(); // Otomatik olarak ilk uygun bedeni seç
            } else {
                updateStockStatus(); // Uygun beden yoksa stok durumunu güncelle
            }
        });
    });
    
    // Sayfa yüklendiğinde bedenleri güncelle ve ilk uygun bedeni seç
    if (selectedColor) {
        updateSizeButtonsBasedOnStock();
        // Stokta olan ilk bedeni otomatik seç
        setTimeout(() => {
            const firstAvailableSizeButton = document.querySelector('.size-option:not([disabled])');
            if (firstAvailableSizeButton) {
                firstAvailableSizeButton.click(); // Otomatik olarak ilk uygun bedeni seç
            } else {
                updateStockStatus(); // Uygun beden yoksa stok durumunu güncelle
            }
        }, 100); // Kısa bir gecikme ile çalıştır
    }
    
    // Beden seçimi
    document.querySelectorAll('.size-option').forEach(button => {
        button.addEventListener('click', function() {
            // Önceki seçimi temizle
            document.querySelectorAll('.size-option').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white', 'border-primary');
                btn.classList.add('border-gray-300');
            });
            
            // Yeni seçimi işaretle
            this.classList.remove('border-gray-300');
            this.classList.add('bg-primary', 'text-white', 'border-primary');
            
            selectedSize = parseInt(this.dataset.size);
            document.getElementById('selected-size').textContent = this.dataset.sizeValue;
            
            updateStockStatus();
        });
    });
    
    // Stok durumunu güncelle
    function updateStockStatus() {
        const addToCartBtn = document.getElementById('add-to-cart');
        const addToCartText = document.getElementById('add-to-cart-text');
        const stockStatus = document.getElementById('stock-status');
        const currentPriceElement = document.getElementById('current-price');
        
        if (selectedColor && selectedSize) {
            const variant = productVariants.find(v => 
                v.color_id === selectedColor && v.size_id === selectedSize
            );
            
            if (variant && variant.stock_quantity > 0) {
                // Seçili varyantın fiyatını güncelle
                if (variant.price) {
                    currentPriceElement.textContent = '₺ ' + parseFloat(variant.price).toLocaleString('tr-TR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    
                    // İndirim göstergesini güncelle
                    const discountElement = document.querySelector('.price-section .line-through');
                    const discountBadge = document.querySelector('.price-section .bg-green-500');
                    
                    if (discountElement && discountBadge) {
                        if (variant.original_price && variant.original_price > 0) {
                            discountElement.textContent = '₺ ' + parseFloat(variant.original_price).toLocaleString('tr-TR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            discountElement.style.display = '';
                            discountBadge.style.display = '';
                        } else {
                            discountElement.style.display = 'none';
                            discountBadge.style.display = 'none';
                        }
                    }
                }
                
                stockStatus.textContent = variant.stock_quantity <= 3 ? 'Son ' + variant.stock_quantity + ' ürün!' : ''; 
                stockStatus.className = 'text-xs text-green-600';
                addToCartBtn.disabled = false;
                addToCartBtn.title = 'Ürünü sepete eklemek için tıklayın';
                addToCartText.textContent = 'Sepete Ekle';
            } else {
                stockStatus.textContent = 'Tükendi';
                stockStatus.className = 'text-xs text-red-600';
                addToCartBtn.disabled = true;
                addToCartBtn.title = 'Bu ürün tükenmiştir';
                addToCartText.textContent = 'Stokta Yok';
            }
        } else {
            stockStatus.textContent = '';
            stockStatus.className = 'text-xs text-gray-600';
            addToCartBtn.disabled = true;
            addToCartBtn.title = 'Sepete eklemek için renk ve beden seçimi yapmalısınız';
            addToCartText.textContent = 'Seçim Yapın';
        }
    }
    
    // Sepete ekle
    document.getElementById('add-to-cart').addEventListener('click', function() {
        if (selectedColor && selectedSize) {
            const colorName = productColors.find(c => c.id === selectedColor).name;
            const sizeValue = document.getElementById('selected-size').textContent;
            
            // Yükleme göstergesini göster
            const loadingIndicator = document.getElementById('loading-indicator');
            const addToCartText = document.getElementById('add-to-cart-text');
            
            loadingIndicator.classList.remove('hidden');
            this.disabled = true;
            addToCartText.textContent = 'Ekleniyor...';
            
            // Sepete ekleme işlemini simüle et (gerçek uygulamada AJAX isteği olabilir)
            setTimeout(() => {
                loadingIndicator.classList.add('hidden');
                this.disabled = false;
                addToCartText.textContent = 'Sepete Ekle';
                
                // Başarılı mesajı göster
                alert(`Sepete eklendi:\n${productName}\nRenk: ${colorName}\nBeden: ${sizeValue}`);
                
                // Stok durumunu güncelle (gerçek uygulamada API'den güncel stok bilgisi alınabilir)
                updateStockStatus();
            }, 800); // 800ms gecikme ile işlemi simüle et
        }
    });
    
    // Tab sistemi
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Tüm tab butonlarını pasif yap
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Aktif tab butonunu işaretle
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-primary', 'text-primary');
            
            // Tüm tab içeriklerini gizle
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });
            
            // Seçili tab içeriğini göster
            document.getElementById(tabId).classList.remove('hidden');
        });
    });
});
