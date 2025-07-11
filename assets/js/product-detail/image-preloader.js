// Görsel önbelleğe alma
export function initializeImagePreloader(state) {
    function preloadColorImages() {
        try {
            const colorImageDataElement = document.getElementById('color-image-data');
            if (!colorImageDataElement) {
                console.error('color-image-data elementi bulunamadı');
                return;
            }
            
            const colorImageData = JSON.parse(colorImageDataElement.textContent);
            const preloadPromises = [];
            
            // Önce seçili rengin görsellerini yükle
            if (state.selectedColor && colorImageData[state.selectedColor]) {
                const selectedColorImages = colorImageData[state.selectedColor];
                preloadSelectedColorImages(selectedColorImages, preloadPromises);
            }
            
            // Sonra diğer renklerin görsellerini yükle
            Object.keys(colorImageData).forEach(colorId => {
                if (colorId != state.selectedColor) {
                    const images = colorImageData[colorId];
                    preloadColorImagesWithLowPriority(images, preloadPromises);
                }
            });
            
            Promise.all(preloadPromises).then(() => {
                state.preloadComplete = true;
                console.log('Tüm görseller preload edildi');
            });
        } catch (error) {
            console.error('Görsel önbelleğe alma hatası:', error);
        }
    }
    
    function preloadSelectedColorImages(images, promises) {
        images.forEach(image => {
            // Ana görsel
            const imgPromise = new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    state.imageCache.set(image.image_url, img);
                    resolve();
                };
                img.onerror = () => resolve(); // Hata durumunda da devam et
                img.src = image.image_url;
                img.importance = "high"; // Tarayıcıya yüksek öncelik bildir
            });
            promises.push(imgPromise);
            
            // WebP varsa onu da yükle
            if (image.webp_url) {
                const webpPromise = new Promise((resolve) => {
                    const webpImg = new Image();
                    webpImg.onload = () => {
                        state.imageCache.set(image.webp_url, webpImg);
                        resolve();
                    };
                    webpImg.onerror = () => resolve();
                    webpImg.src = image.webp_url;
                    webpImg.importance = "high";
                });
                promises.push(webpPromise);
            }
            
            // Thumbnail varsa
            if (image.thumbnail_url) {
                const thumbPromise = new Promise((resolve) => {
                    const thumbImg = new Image();
                    thumbImg.onload = () => {
                        state.imageCache.set(image.thumbnail_url, thumbImg);
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
            // Ana görsel
            const imgPromise = new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    state.imageCache.set(image.image_url, img);
                    resolve();
                };
                img.onerror = () => resolve();
                img.src = image.image_url;
                img.importance = "low"; // Tarayıcıya düşük öncelik bildir
            });
            promises.push(imgPromise);
            
            // WebP varsa onu da yükle
            if (image.webp_url) {
                const webpPromise = new Promise((resolve) => {
                    const webpImg = new Image();
                    webpImg.onload = () => {
                        state.imageCache.set(image.webp_url, webpImg);
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
    
    // Preloading'i hemen başlat
    preloadColorImages();
    
    // Public API
    return {
        changeMainImage: (imageData, thumbnail, isPreview = false) => {
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
            
            // Önizleme modu için farklı geçiş efekti
            if (isPreview) {
                mainImage.classList.add('preview-mode');
            } else {
                mainImage.classList.remove('preview-mode');
            }
            
            // Fade out efekti
            mainImage.style.opacity = '0';
            
            // Yeni resim cache'de var mı kontrol et
            const cachedImage = state.imageCache.get(imageUrl);
            
            if (cachedImage) {
                // Cache'den hızlıca yükle
                setTimeout(() => {
                    updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl);
                    mainImage.style.opacity = '1';
                }, 50); // Daha hızlı geçiş için süreyi azalttık
            } else {
                // Yeni resmi yükle
                const newImage = new Image();
                newImage.onload = function() {
                    state.imageCache.set(imageUrl, newImage);
                    updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl);
                    mainImage.style.opacity = '1';
                };
                newImage.onerror = function() {
                    // Hata durumunda opacity'yi geri getir
                    mainImage.style.opacity = '1';
                };
                newImage.src = imageUrl;
            }
            
            // Thumbnail border'larını güncelle (sadece thumbnail varsa ve önizleme modu değilse)
            if (thumbnail && !isPreview) {
                document.querySelectorAll('.thumbnail, .thumbnail-item').forEach(thumb => {
                    thumb.classList.remove('border-primary', 'border-blue-500', 'border-opacity-100');
                    thumb.classList.add('border-transparent');
                });
                thumbnail.classList.remove('border-transparent');
                thumbnail.classList.add('border-primary', 'border-blue-500', 'border-opacity-100');
            }
        },
        
        previewColorImages: (colorId) => {
            try {
                const colorImageData = JSON.parse(document.getElementById('color-image-data').textContent);
                if (colorImageData[colorId] && colorImageData[colorId].length > 0) {
                    const firstImage = colorImageData[colorId][0];
                    return firstImage;
                }
            } catch (error) {
                console.error('Renk önizleme hatası:', error);
            }
            return null;
        }
    };
}