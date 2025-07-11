document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - Ürün detay sayfası yüklendi');
    console.log('Kullanıcı giriş durumu:', isLoggedIn);
    console.log('Favori varyant ID\'leri:', favoriteVariantIds);
    
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
        
        // Favori durumunu güncelle
        updateSelectedVariant();
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
                // Varyant seçimi sonrası favori durumunu güncelle
                updateSelectedVariant();
            } else {
                updateStockStatus(); // Uygun beden yoksa stok durumunu güncelle
            }
            
            // Favori butonunu hemen başlat
            initFavoriteButton();
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
            updateSelectedVariant(); // Favori durumunu güncelle
        });
    });
    
    // Stok durumunu güncelle
    function updateStockStatus() {
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
                
                stockStatus.textContent = variant.stock_quantity <= 3 ? 'Son ' + variant.stock_quantity + ' ürün!' : 'Stokta';
                stockStatus.className = 'text-xs text-green-600';
            } else {
                stockStatus.textContent = 'Tükendi';
                stockStatus.className = 'text-xs text-red-600';
            }
        } else {
            stockStatus.textContent = '';
            stockStatus.className = 'text-xs text-gray-600';
        }
    }
    
    // Favori işlevselliği
    let currentSelectedVariantId = null;
    let isFavoriteLoading = false;
    
    // Favori durumunu kontrol et ve güncelle
    function updateFavoriteStatus() {
        if (!isLoggedIn || !currentSelectedVariantId) return;
        
        const favoriteBtn = document.getElementById('favorite-btn');
        const favoriteIcon = document.getElementById('favorite-icon');
        
        if (!favoriteBtn || !favoriteIcon) return;
        
        const isFavorite = favoriteVariantIds.includes(currentSelectedVariantId);
        
        if (isFavorite) {
            favoriteIcon.classList.remove('far', 'text-gray-600');
            favoriteIcon.classList.add('fas', 'text-red-500');
            favoriteBtn.title = 'Favorilerden çıkar';
        } else {
            favoriteIcon.classList.remove('fas', 'text-red-500');
            favoriteIcon.classList.add('far', 'text-gray-600');
            favoriteBtn.title = 'Favorilere ekle';
        }
    }
    
    // Favori butonu click event'i
    function initFavoriteButton() {
        const favoriteBtn = document.getElementById('favorite-btn');
        console.log('initFavoriteButton çağrıldı', favoriteBtn, 'Giriş durumu:', isLoggedIn);
        
        if (!favoriteBtn || !isLoggedIn) {
            console.log('Favori butonu bulunamadı veya kullanıcı giriş yapmamış');
            return;
        }
        
        favoriteBtn.addEventListener('click', function() {
            console.log('Favori butonuna tıklandı', 'Yükleniyor:', isFavoriteLoading, 'Seçili varyant:', currentSelectedVariantId);
            
            if (isFavoriteLoading || !currentSelectedVariantId) {
                console.log('İşlem yapılamıyor: Yükleniyor veya varyant seçilmemiş');
                return;
            }
            
            toggleFavorite();
        });
        
        console.log('Favori butonu event listener eklendi');
    }
    
    // Favori ekleme/çıkarma
    function toggleFavorite() {
        console.log('toggleFavorite çağrıldı', 'Seçili varyant:', currentSelectedVariantId);
        
        if (!currentSelectedVariantId) {
            console.error('Varyant seçilmemiş, favori işlemi yapılamıyor');
            showNotification('Lütfen renk ve beden seçin', 'error');
            return;
        }
        
        if (isFavoriteLoading) {
            console.log('Zaten bir favori işlemi devam ediyor');
            return;
        }
        
        isFavoriteLoading = true;
        const favoriteBtn = document.getElementById('favorite-btn');
        const favoriteIcon = document.getElementById('favorite-icon');
        
        if (!favoriteBtn || !favoriteIcon) {
            console.error('Favori butonları bulunamadı');
            isFavoriteLoading = false;
            return;
        }
        
        // Loading state
        favoriteBtn.disabled = true;
        favoriteIcon.classList.add('fa-spin');
        
        const isFavorite = favoriteVariantIds.includes(currentSelectedVariantId);
        const action = isFavorite ? 'remove' : 'add';
        
        console.log('Favori işlemi başlatılıyor:', action, 'Varyant ID:', currentSelectedVariantId);
        
        fetch('/api/favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                variantId: currentSelectedVariantId,
                colorId: selectedColor
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Sunucu yanıt vermedi');
            }
            return response.json();
        })
        .then(data => {
            console.log('API yanıtı:', data);
            
            if (data.success) {
                if (action === 'add') {
                    favoriteVariantIds.push(currentSelectedVariantId);
                    console.log('Favorilere eklendi:', currentSelectedVariantId);
                } else {
                    const index = favoriteVariantIds.indexOf(currentSelectedVariantId);
                    if (index > -1) {
                        favoriteVariantIds.splice(index, 1);
                        console.log('Favorilerden çıkarıldı:', currentSelectedVariantId);
                    }
                }
                updateFavoriteStatus();
                
                // Başarı mesajı göster
                showNotification(data.message || 'İşlem başarılı', 'success');
            } else {
                console.error('API hatası:', data.message);
                showNotification(data.message || 'İşlem başarısız', 'error');
                
                // Kullanıcı bulunamadı hatası - yönlendirme
                if (data.error_code === 'user_not_found' || data.redirect) {
                    console.log('Kullanıcı bulunamadı, yönlendirme yapılıyor...');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'logout.php';
                    }, 2000);
                }
            }
        })
        .catch(error => {
            console.error('Favori işlemi hatası:', error);
            showNotification('Bir hata oluştu: ' + error.message, 'error');
        })
        .finally(() => {
            isFavoriteLoading = false;
            favoriteBtn.disabled = false;
            favoriteIcon.classList.remove('fa-spin');
            console.log('Favori işlemi tamamlandı');
        });
    }
    
    // Bildirim gösterme
    function showNotification(message, type = 'info') {
        // Mevcut bildirim varsa kaldır
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = `notification fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // 3 saniye sonra kaldır
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Seçili varyantı güncelle
    function updateSelectedVariant() {
        console.log('updateSelectedVariant çağrıldı', selectedColor, selectedSize);
        
        if (selectedColor && selectedSize) {
            const variant = productVariants.find(v =>
                v.color_id === selectedColor && v.size_id === selectedSize
            );
            
            if (variant) {
                currentSelectedVariantId = variant.id;
                console.log('Varyant bulundu:', variant.id, 'Favori mi:', favoriteVariantIds.includes(variant.id));
                updateFavoriteStatus();
            } else {
                console.log('Eşleşen varyant bulunamadı');
            }
        } else {
            console.log('Renk veya beden seçilmemiş');
        }
    }
    
    // Favori butonunu başlat
    initFavoriteButton();
    
    // Renk veya beden değişikliğinde favori durumunu güncelle
    const originalSelectColor = selectColor;
    selectColor = function(colorId, colorName) {
        originalSelectColor(colorId, colorName);
        updateSelectedVariant();
    };
    
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
