// Ana modül - Diğer modülleri yükler ve başlatır
import { initializeVariantData } from './variant-manager.js';
import { initializeImagePreloader } from './image-preloader.js';
import { initializeColorSelector } from './color-selector.js';
import { initializeSizeSelector } from './size-selector.js';
import { initializeColorSizeGrid } from './color-size-grid.js';
import { initializeHistoryNavigation } from './history-navigation.js';
import { initializeFavorites } from './favorites-manager.js';
import { initializeTabSystem } from './tab-system.js';
import { initializeNotifications } from './notification.js';

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - Ürün detay sayfası yüklendi');
    console.log('Kullanıcı giriş durumu:', window.isLoggedIn);
    console.log('Favori varyant ID\'leri:', window.favoriteVariantIds);
    
    // Global durum nesnesi
    const state = {
        selectedColor: null,
        selectedSize: null,
        currentSelectedVariantId: null,
        imageCache: new Map(),
        variantMap: new Map(),
        variantsByColor: new Map(),
        variantsBySize: new Map(),
        preloadComplete: false,
        isFavoriteLoading: false
    };
    
    // Modülleri başlat ve API'leri al
    const variantManager = initializeVariantData(state, window.productVariantsData);
    const imageManager = initializeImagePreloader(state);
    const notificationManager = initializeNotifications();
    
    // Modülleri bağımlılıklarıyla başlat
    const colorSelector = initializeColorSelector(state, window.productColorsData, imageManager, variantManager);
    const sizeSelector = initializeSizeSelector(state, variantManager);
    const gridManager = initializeColorSizeGrid(state, window.productColorsData, window.productSizesData, variantManager, colorSelector, sizeSelector);
    const historyManager = initializeHistoryNavigation(state, colorSelector);
    const favoriteManager = initializeFavorites(state, window.favoriteVariantIds, window.isLoggedIn, notificationManager);
    const tabSystem = initializeTabSystem();
    
    // Sayfa yüklendiğinde ilk rengi seç
    if (window.productColorsData.length > 0) {
        const firstColorButton = document.querySelector(`.color-option[data-color-id="${window.productColorsData[0].id}"]`);
        if (firstColorButton) {
            firstColorButton.click();
        }
    }
});