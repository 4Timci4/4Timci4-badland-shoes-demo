document.addEventListener('DOMContentLoaded', function() {
    // Ürün varyantları ve renkleri global değişkenlerden al
    const productVariants = productVariantsData;
    const productColors = productColorsData;
    
    let selectedColor = null;
    let selectedSize = null;
    
    // Görsel preloading için cache
    const imageCache = new Map();
    let preloadComplete = false;
    
    // Tüm renk görsellerini arka planda yükle
    function preloadColorImages() {
        const colorImageData = JSON.parse(document.getElementById('color-image-data').textContent);
        const preloadPromises = [];
        
        Object.keys(colorImageData).forEach(colorId => {
            colorImageData[colorId].forEach(image => {
                // Ana görsel
                const imgPromise = new Promise((resolve) => {
                    const img = new Image();
                    img.onload = () => {
                        imageCache.set(image.image_url, img);
                        resolve();
                    };
                    img.onerror = () => resolve(); // Hata durumunda da devam et
                    img.src = image.image_url;
                });
                preloadPromises.push(imgPromise);
                
                // WebP varsa onu da yükle
                if (image.webp_url) {
                    const webpPromise = new Promise((resolve) => {
                        const webpImg = new Image();
                        webpImg.onload = () => {
                            imageCache.set(image.webp_url, webpImg);
                            resolve();
                        };
                        webpImg.onerror = () => resolve();
                        webpImg.src = image.webp_url;
                    });
                    preloadPromises.push(webpPromise);
                }
                
                // Thumbnail varsa
                if (image.thumbnail_url) {
                    const thumbPromise = new Promise((resolve) => {
                        const thumbImg = new Image();
                        thumbImg.onload = () => {
                            imageCache.set(image.thumbnail_url, thumbImg);
                            resolve();
                        };
                        thumbImg.onerror = () => resolve();
                        thumbImg.src = image.thumbnail_url;
                    });
                    preloadPromises.push(thumbPromise);
                }
            });
        });
        
        Promise.all(preloadPromises).then(() => {
            preloadComplete = true;
            console.log('Tüm görseller preload edildi');
        });
    }
    
    // Preloading'i başlat
    setTimeout(preloadColorImages, 100);
    
    // Sayfa yüklendiğinde ilk rengi seç
    if (productColors.length > 0) {
        selectedColor = productColors[0].id;
        // Seçili rengi UI'da da göster
        const firstColorButton = document.querySelector('.color-option[data-color-id="'+selectedColor+'"]');
        if (firstColorButton) {
            firstColorButton.classList.remove('border-gray-300');
            firstColorButton.classList.add('border-secondary');
            document.getElementById('selected-color').textContent = firstColorButton.dataset.colorName;
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
    
    // Ana resim değiştirme - Fade transition ile
    window.changeMainImage = function(imageData, thumbnail) {
        const mainImage = document.getElementById('main-product-image');
        const mainPicture = document.getElementById('main-product-picture');
        if (!mainImage) return;
        
        let imageUrl, originalUrl, altText, webpUrl;
        
        // imageData bir obje mi yoksa string mi kontrol et
        if (typeof imageData === 'string') {
            imageUrl = imageData;
            originalUrl = imageData;
            altText = mainImage.alt;
        } else if (imageData && imageData.image_url) {
            imageUrl = imageData.image_url;
            originalUrl = imageData.original_url || imageData.image_url;
            altText = imageData.alt_text || mainImage.alt;
            webpUrl = imageData.webp_url;
        }
        
        // Fade out efekti
        mainImage.style.opacity = '0';
        
        // Yeni resim cache'de var mı kontrol et
        const cachedImage = imageCache.get(imageUrl);
        
        if (cachedImage) {
            // Cache'den hızlıca yükle
            setTimeout(() => {
                updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl);
                mainImage.style.opacity = '1';
            }, 100);
        } else {
            // Yeni resmi yükle
            const newImage = new Image();
            newImage.onload = function() {
                imageCache.set(imageUrl, newImage);
                updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl);
                mainImage.style.opacity = '1';
            };
            newImage.onerror = function() {
                // Hata durumunda opacity'yi geri getir
                mainImage.style.opacity = '1';
            };
            newImage.src = imageUrl;
        }
        
        // Thumbnail border'larını güncelle (sadece thumbnail varsa)
        if (thumbnail) {
            document.querySelectorAll('.thumbnail, .thumbnail-item').forEach(thumb => {
                thumb.classList.remove('border-primary', 'border-blue-500', 'border-opacity-100');
                thumb.classList.add('border-transparent');
            });
            thumbnail.classList.remove('border-transparent');
            thumbnail.classList.add('border-primary', 'border-blue-500', 'border-opacity-100');
        }
    };
    
    // Görsel kaynaklarını güncelleme helper fonksiyonu
    function updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl) {
        // WebP source'u güncelle
        if (webpUrl) {
            const existingSource = mainPicture.querySelector('source[type="image/webp"]');
            if (existingSource) {
                existingSource.srcset = webpUrl;
            } else {
                const source = document.createElement('source');
                source.srcset = webpUrl;
                source.type = 'image/webp';
                mainPicture.insertBefore(source, mainImage);
            }
        }
        
        // Ana resim özelliklerini güncelle
        mainImage.src = imageUrl;
        mainImage.setAttribute('data-original', originalUrl);
        mainImage.alt = altText;
    }
    
    // Renk seçimi - Soft geçiş ile
    document.querySelectorAll('.color-option').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Link davranışını engelle
            
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
        
        selectedColor = colorId;
        document.getElementById('selected-color').textContent = colorName;
        
        // Görselleri güncelle
        if (typeof updateImagesForColor === 'function') {
            updateImagesForColor(colorId);
        }
        
        // Önce bedenlerin görünümünü güncelle
        updateSizeButtonsBasedOnStock();
        
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
    }
    
    // Tarayıcı geri/ileri butonları için
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const colorSlug = urlParams.get('color');
        
        if (colorSlug && event.state && event.state.colorId) {
            // State'den renk bilgisini al
            const colorId = event.state.colorId;
            const colorButton = document.querySelector('.color-option[data-color-id="' + colorId + '"]');
            if (colorButton) {
                selectColor(colorId, colorButton.dataset.colorName);
            }
        } else if (!colorSlug) {
            // Renk parametresi yoksa ilk rengi seç
            const firstColorButton = document.querySelector('.color-option');
            if (firstColorButton) {
                selectColor(parseInt(firstColorButton.dataset.colorId), firstColorButton.dataset.colorName);
            }
        }
    });
    
    // Sayfa yüklendiğinde URL'den renk parametresini kontrol et
    const urlParams = new URLSearchParams(window.location.search);
    const urlColorSlug = urlParams.get('color');
    if (urlColorSlug) {
        const colorButton = document.querySelector('.color-option[data-color-slug="' + urlColorSlug + '"]');
        if (colorButton) {
            selectColor(parseInt(colorButton.dataset.colorId), colorButton.dataset.colorName);
        }
    }
    
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
                // Fiyatı güncelle
                if (variant.price) {
                    currentPriceElement.textContent = '₺ ' + parseFloat(variant.price).toLocaleString('tr-TR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
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
