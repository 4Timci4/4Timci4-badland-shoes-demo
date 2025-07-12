document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - Ürün detay sayfası yüklendi');
    console.log('Kullanıcı giriş durumu:', isLoggedIn);
    console.log('Favori varyant ID\'leri:', favoriteVariantIds);
    
    
    const productVariants = productVariantsData;
    const productColors = productColorsData;
    
    let selectedColor = null;
    let selectedSize = null;
    
    
    const imageCache = new Map();
    let preloadComplete = false;
    
    
    const variantMap = new Map();
    const variantsByColor = new Map();
    const variantsBySize = new Map();
    
    
    function indexVariantData() {
        productVariants.forEach(variant => {
            
            const key = `${variant.color_id}-${variant.size_id}`;
            variantMap.set(key, variant);
            
            
            if (!variantsByColor.has(variant.color_id)) {
                variantsByColor.set(variant.color_id, []);
            }
            variantsByColor.get(variant.color_id).push(variant);
            
            
            if (!variantsBySize.has(variant.size_id)) {
                variantsBySize.set(variant.size_id, []);
            }
            variantsBySize.get(variant.size_id).push(variant);
        });
    }
    
    
    indexVariantData();
    
    
    function preloadColorImages() {
        const colorImageData = JSON.parse(document.getElementById('color-image-data').textContent);
        const preloadPromises = [];
        
        
        if (selectedColor && colorImageData[selectedColor]) {
            const selectedColorImages = colorImageData[selectedColor];
            preloadSelectedColorImages(selectedColorImages, preloadPromises);
        }
        
        
        Object.keys(colorImageData).forEach(colorId => {
            if (colorId != selectedColor) {
                const images = colorImageData[colorId];
                preloadColorImagesWithLowPriority(images, preloadPromises);
            }
        });
        
        Promise.all(preloadPromises).then(() => {
            preloadComplete = true;
            console.log('Tüm görseller preload edildi');
        });
    }
    
    
    function preloadSelectedColorImages(images, promises) {
        images.forEach(image => {
            
            const imgPromise = new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    imageCache.set(image.image_url, img);
                    resolve();
                };
                img.onerror = () => resolve(); 
                img.src = image.image_url;
                img.importance = "high"; 
            });
            promises.push(imgPromise);
            
            
            if (image.webp_url) {
                const webpPromise = new Promise((resolve) => {
                    const webpImg = new Image();
                    webpImg.onload = () => {
                        imageCache.set(image.webp_url, webpImg);
                        resolve();
                    };
                    webpImg.onerror = () => resolve();
                    webpImg.src = image.webp_url;
                    webpImg.importance = "high";
                });
                promises.push(webpPromise);
            }
            
            
            if (image.thumbnail_url) {
                const thumbPromise = new Promise((resolve) => {
                    const thumbImg = new Image();
                    thumbImg.onload = () => {
                        imageCache.set(image.thumbnail_url, thumbImg);
                        resolve();
                    };
                    thumbImg.onerror = () => resolve();
                    thumbImg.src = image.thumbnail_url;
                    thumbImg.importance = "high";
                });
                promises.push(thumbPromise);
            }
        });
    }
    
    
    function preloadColorImagesWithLowPriority(images, promises) {
        images.forEach(image => {
            
            const imgPromise = new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    imageCache.set(image.image_url, img);
                    resolve();
                };
                img.onerror = () => resolve();
                img.src = image.image_url;
                img.importance = "low"; 
            });
            promises.push(imgPromise);
            
            
            if (image.webp_url) {
                const webpPromise = new Promise((resolve) => {
                    const webpImg = new Image();
                    webpImg.onload = () => {
                        imageCache.set(image.webp_url, webpImg);
                        resolve();
                    };
                    webpImg.onerror = () => resolve();
                    webpImg.src = image.webp_url;
                    webpImg.importance = "low";
                });
                promises.push(webpPromise);
            }
        });
    }
    
    
    preloadColorImages();
    
    
    if (productColors.length > 0) {
        selectedColor = productColors[0].id;
        
        const firstColorButton = document.querySelector('.color-option[data-color-id="'+selectedColor+'"]');
        if (firstColorButton) {
            firstColorButton.classList.remove('border-gray-300');
            firstColorButton.classList.add('border-secondary');
            document.getElementById('selected-color').textContent = firstColorButton.dataset.colorName;
        }
    }
    
    
    function findVariant(colorId, sizeId) {
        const key = `${colorId}-${sizeId}`;
        return variantMap.get(key);
    }
    
    
    function updateSizeButtonsBasedOnStock() {
        if (!selectedColor) return;
        
        document.querySelectorAll('.size-option').forEach(button => {
            const sizeId = parseInt(button.dataset.size);
            const variant = findVariant(selectedColor, sizeId);
            
            if (!variant || variant.stock_quantity <= 0) {
                button.classList.add('line-through', 'opacity-50');
                button.disabled = true;
            } else {
                button.classList.remove('line-through', 'opacity-50');
                button.disabled = false;
            }
        });
    }
    
    
    window.changeMainImage = function(imageData, thumbnail, isPreview = false) {
        const mainImage = document.getElementById('main-product-image');
        const mainPicture = document.getElementById('main-product-picture');
        if (!mainImage) return;
        
        let imageUrl, originalUrl, altText, webpUrl;
        
        
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
        
        
        if (isPreview) {
            mainImage.classList.add('preview-mode');
        } else {
            mainImage.classList.remove('preview-mode');
        }
        
        
        mainImage.style.opacity = '0';
        
        
        const cachedImage = imageCache.get(imageUrl);
        
        if (cachedImage) {
            
            setTimeout(() => {
                updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl);
                mainImage.style.opacity = '1';
            }, 50); 
        } else {
            
            const newImage = new Image();
            newImage.onload = function() {
                imageCache.set(imageUrl, newImage);
                updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl);
                mainImage.style.opacity = '1';
            };
            newImage.onerror = function() {
                
                mainImage.style.opacity = '1';
            };
            newImage.src = imageUrl;
        }
        
        
        if (thumbnail && !isPreview) {
            document.querySelectorAll('.thumbnail, .thumbnail-item').forEach(thumb => {
                thumb.classList.remove('border-primary', 'border-blue-500', 'border-opacity-100');
                thumb.classList.add('border-transparent');
            });
            thumbnail.classList.remove('border-transparent');
            thumbnail.classList.add('border-primary', 'border-blue-500', 'border-opacity-100');
        }
    };
    
    
    function updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl) {
        
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
        
        
        mainImage.src = imageUrl;
        mainImage.setAttribute('data-original', originalUrl);
        mainImage.alt = altText;
    }
    
    
    document.querySelectorAll('.color-option').forEach(button => {
        
        button.addEventListener('mouseenter', function() {
            const colorId = parseInt(this.dataset.colorId);
            previewColorImages(colorId);
        });
        
        
        button.addEventListener('mouseleave', function() {
            if (selectedColor) {
                updateImagesForColor(selectedColor);
            }
        });
        
        
        button.addEventListener('click', function(e) {
            e.preventDefault(); 
            
            const colorId = parseInt(this.dataset.colorId);
            const colorName = this.dataset.colorName;
            const colorSlug = this.dataset.colorSlug;
            
            
            const url = new URL(window.location);
            url.searchParams.set('color', colorSlug);
            history.pushState({colorId: colorId, colorSlug: colorSlug}, '', url);
            
            
            selectColor(colorId, colorName);
        });
    });
    
    
    function previewColorImages(colorId) {
        const colorImageData = JSON.parse(document.getElementById('color-image-data').textContent);
        if (colorImageData[colorId] && colorImageData[colorId].length > 0) {
            const firstImage = colorImageData[colorId][0];
            changeMainImage(firstImage, null, true); 
        }
    }
    
    
    function selectColor(colorId, colorName) {
        
        document.querySelectorAll('.color-option').forEach(btn => {
            btn.classList.remove('border-secondary');
            btn.classList.add('border-gray-300');
        });
        
        
        const selectedButton = document.querySelector('.color-option[data-color-id="' + colorId + '"]');
        if (selectedButton) {
            selectedButton.classList.remove('border-gray-300');
            selectedButton.classList.add('border-secondary');
        }
        
        selectedColor = colorId;
        document.getElementById('selected-color').textContent = colorName;
        
        
        if (typeof updateImagesForColor === 'function') {
            updateImagesForColor(colorId);
        }
        
        
        updateSizeButtonsBasedOnStock();
        
        
        selectedSize = null;
        document.getElementById('selected-size').textContent = '-';
        document.querySelectorAll('.size-option').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white', 'border-primary');
            btn.classList.add('border-gray-300');
        });
        
        
        const firstAvailableSizeButton = document.querySelector('.size-option:not([disabled])');
        if (firstAvailableSizeButton) {
            firstAvailableSizeButton.click(); 
        } else {
            updateStockStatus(); 
        }
        
        
        updateSelectedVariant();
    }
    
    
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const colorSlug = urlParams.get('color');
        
        if (colorSlug && event.state && event.state.colorId) {
            
            const colorId = event.state.colorId;
            const colorButton = document.querySelector('.color-option[data-color-id="' + colorId + '"]');
            if (colorButton) {
                selectColor(colorId, colorButton.dataset.colorName);
            }
        } else if (!colorSlug) {
            
            const firstColorButton = document.querySelector('.color-option');
            if (firstColorButton) {
                selectColor(parseInt(firstColorButton.dataset.colorId), firstColorButton.dataset.colorName);
            }
        }
    });
    
    
    const urlParams = new URLSearchParams(window.location.search);
    const urlColorSlug = urlParams.get('color');
    if (urlColorSlug) {
        const colorButton = document.querySelector('.color-option[data-color-slug="' + urlColorSlug + '"]');
        if (colorButton) {
            selectColor(parseInt(colorButton.dataset.colorId), colorButton.dataset.colorName);
        }
    }
    
    
    if (selectedColor) {
        updateSizeButtonsBasedOnStock();
        
        setTimeout(() => {
            const firstAvailableSizeButton = document.querySelector('.size-option:not([disabled])');
            if (firstAvailableSizeButton) {
                firstAvailableSizeButton.click(); 
                
                updateSelectedVariant();
            } else {
                updateStockStatus(); 
            }
            
            
            initFavoriteButton();
        }, 100); 
    }
    
    
    function createColorSizeGrid() {
        const gridContainer = document.getElementById('color-size-grid');
        if (!gridContainer) return;
        
        let gridHTML = '<div class="grid grid-cols-4 gap-2 mt-4">';
        
        productColors.forEach(color => {
            const availableSizes = getAvailableSizesForColor(color.id);
            
            availableSizes.forEach(size => {
                const variant = findVariant(color.id, size.id);
                const isInStock = variant && variant.stock_quantity > 0;
                const isSelected = selectedColor === color.id && selectedSize === size.id;
                
                gridHTML += `
                    <div
                        class="variant-grid-item p-2 border rounded-md text-center cursor-pointer ${isInStock ? '' : 'opacity-50'} ${isSelected ? 'border-primary bg-primary/10' : 'border-gray-300'}"
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
                
                
                if (colorId !== selectedColor) {
                    selectColor(colorId, colorName);
                }
                
                
                selectSize(sizeId, sizeValue);
            });
        });
    }
    
    
    function updateColorSizeGrid() {
        createColorSizeGrid();
    }
    
    
    function getAvailableSizesForColor(colorId) {
        const variants = variantsByColor.get(colorId) || [];
        const sizeIds = [...new Set(variants.map(v => v.size_id))];
        
        const sizes = [];
        sizeIds.forEach(sizeId => {
            const sizeInfo = productSizesData.find(s => s.id === sizeId);
            if (sizeInfo) sizes.push(sizeInfo);
        });
        
        return sizes.sort((a, b) => a.size_value.localeCompare(b.size_value, undefined, {numeric: true}));
    }
    
    
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
        
        selectedSize = sizeId;
        document.getElementById('selected-size').textContent = sizeValue;
        
        updateStockStatus();
        updateSelectedVariant(); 
        updateColorSizeGrid(); 
    }
    
    
    document.querySelectorAll('.size-option').forEach(button => {
        button.addEventListener('click', function() {
            if (this.disabled) return;
            
            const sizeId = parseInt(this.dataset.size);
            const sizeValue = this.dataset.sizeValue;
            
            selectSize(sizeId, sizeValue);
        });
    });
    
    
    createColorSizeGrid();
    
    
    function updateStockStatus() {
        const stockStatus = document.getElementById('stock-status');
        const currentPriceElement = document.getElementById('current-price');
        
        if (selectedColor && selectedSize) {
            const variant = productVariants.find(v => 
                v.color_id === selectedColor && v.size_id === selectedSize
            );
            
            if (variant && variant.stock_quantity > 0) {
                
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
    
    
    let currentSelectedVariantId = null;
    let isFavoriteLoading = false;
    
    
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
                
                
                showNotification(data.message || 'İşlem başarılı', 'success');
            } else {
                console.error('API hatası:', data.message);
                showNotification(data.message || 'İşlem başarısız', 'error');
                
                
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
    
    
    function showNotification(message, type = 'info') {
        
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
        
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    
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
    
    
    initFavoriteButton();
    
    
    const originalSelectColor = selectColor;
    selectColor = function(colorId, colorName) {
        originalSelectColor(colorId, colorName);
        updateSelectedVariant();
    };
    
    
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-primary', 'text-primary');
            
            
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });
            
            
            document.getElementById(tabId).classList.remove('hidden');
        });
    });
});
