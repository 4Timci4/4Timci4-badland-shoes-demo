export function initializeFavorites(state, favoriteVariantIds, isLoggedIn, notificationManager) {
    let isFavoriteLoading = false;
    
    // Çerezlerden mevcut favorileri yükle
    function loadFavoritesFromCookies() {
        if (typeof window.FavoritesManager !== 'undefined') {
            return window.FavoritesManager.getFavoriteVariantIds();
        }
        return [];
    }
    
    // Favori durumunu güncelle
    function updateFavoriteStatus() {
        if (!state.currentSelectedVariantId) return;
        
        const favoriteBtn = document.getElementById('favorite-btn');
        const favoriteIcon = document.getElementById('favorite-icon');
        
        if (!favoriteBtn || !favoriteIcon) return;
        
        // Çerezlerden güncel favori listesini al
        const currentFavorites = loadFavoritesFromCookies();
        const isFavorite = currentFavorites.includes(state.currentSelectedVariantId);
        
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
    
    // Favori butonunu başlat
    function initFavoriteButton() {
        const favoriteBtn = document.getElementById('favorite-btn');
        
        if (!favoriteBtn) {
            return;
        }
        
        // Artık giriş kontrolü yok - herkes favori ekleyebilir
        favoriteBtn.addEventListener('click', function() {
            if (isFavoriteLoading || !state.currentSelectedVariantId) {
                return;
            }
            
            toggleFavorite();
        });
    }
    
    // Favori toggle işlemi
    function toggleFavorite() {
        if (!state.currentSelectedVariantId) {
            notificationManager.showNotification('Lütfen renk ve beden seçin', 'error');
            return;
        }
        
        if (isFavoriteLoading) {
            return;
        }
        
        // FavoritesManager'ın yüklendiğinden emin ol
        if (typeof window.FavoritesManager === 'undefined') {
            notificationManager.showNotification('Favori sistemi yüklenemedi', 'error');
            return;
        }
        
        isFavoriteLoading = true;
        const favoriteBtn = document.getElementById('favorite-btn');
        const favoriteIcon = document.getElementById('favorite-icon');
        
        if (!favoriteBtn || !favoriteIcon) {
            isFavoriteLoading = false;
            return;
        }
        
        // Loading durumunu göster
        favoriteBtn.disabled = true;
        favoriteIcon.classList.add('fa-spin');
        
        // Çerez tabanlı favori toggle işlemi
        const result = window.FavoritesManager.toggleFavorite(
            state.currentSelectedVariantId,
            state.selectedColor
        );
        
        // Sonucu işle
        if (result.success) {
            updateFavoriteStatus();
            notificationManager.showNotification(result.message, 'success');
        } else {
            notificationManager.showNotification(result.message, 'error');
        }
        
        // Loading durumunu kaldır
        isFavoriteLoading = false;
        favoriteBtn.disabled = false;
        favoriteIcon.classList.remove('fa-spin');
    }
    
    // Varyant seçildiğinde favori durumunu güncelle
    document.addEventListener('variantSelected', function(event) {
        updateFavoriteStatus();
    });
    
    // Favori butonunu başlat
    initFavoriteButton();
    
    // İlk yüklemede favori durumunu güncelle
    updateFavoriteStatus();
    
    return {
        updateFavoriteStatus: updateFavoriteStatus,
        toggleFavorite: toggleFavorite,
        loadFavoritesFromCookies: loadFavoritesFromCookies
    };
}