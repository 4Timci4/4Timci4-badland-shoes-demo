
export function initializeImagePreloader(state) {
    function preloadColorImages() {
        try {
            const colorImageDataElement = document.getElementById('color-image-data');
            if (!colorImageDataElement) {
                return;
            }
            
            const colorImageData = JSON.parse(colorImageDataElement.textContent);
            const preloadPromises = [];
            
            
            if (state.selectedColor && colorImageData[state.selectedColor]) {
                const selectedColorImages = colorImageData[state.selectedColor];
                preloadSelectedColorImages(selectedColorImages, preloadPromises);
            }
            
            
            Object.keys(colorImageData).forEach(colorId => {
                if (colorId != state.selectedColor) {
                    const images = colorImageData[colorId];
                    preloadColorImagesWithLowPriority(images, preloadPromises);
                }
            });
            
            Promise.all(preloadPromises).then(() => {
                state.preloadComplete = true;
            });
        } catch (error) {
        }
    }
    
    function preloadSelectedColorImages(images, promises) {
        images.forEach(image => {
            
            const imgPromise = new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    state.imageCache.set(image.image_url, img);
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
                        state.imageCache.set(image.webp_url, webpImg);
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
            
            const imgPromise = new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    state.imageCache.set(image.image_url, img);
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
    
    
    preloadColorImages();
    
    
    return {
        changeMainImage: (imageData, thumbnail, isPreview = false) => {
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
            
            
            const cachedImage = state.imageCache.get(imageUrl);
            
            if (cachedImage) {
                
                setTimeout(() => {
                    updateImageSources(mainImage, mainPicture, imageUrl, originalUrl, altText, webpUrl);
                    mainImage.style.opacity = '1';
                }, 50); 
            } else {
                
                const newImage = new Image();
                newImage.onload = function() {
                    state.imageCache.set(imageUrl, newImage);
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
        },
        
        previewColorImages: (colorId) => {
            try {
                const colorImageData = JSON.parse(document.getElementById('color-image-data').textContent);
                if (colorImageData[colorId] && colorImageData[colorId].length > 0) {
                    const firstImage = colorImageData[colorId][0];
                    return firstImage;
                }
            } catch (error) {
            }
            return null;
        }
    };
}