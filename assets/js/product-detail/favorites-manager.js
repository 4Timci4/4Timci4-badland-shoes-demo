
export function initializeFavorites(state, favoriteVariantIds, isLoggedIn, notificationManager) {
    let isFavoriteLoading = false;
    
    
    function updateFavoriteStatus() {
        if (!isLoggedIn || !state.currentSelectedVariantId) return;
        
        const favoriteBtn = document.getElementById('favorite-btn');
        const favoriteIcon = document.getElementById('favorite-icon');
        
        if (!favoriteBtn || !favoriteIcon) return;
        
        const isFavorite = favoriteVariantIds.includes(state.currentSelectedVariantId);
        
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
        
        if (!favoriteBtn || !isLoggedIn) {
            return;
        }
        
        favoriteBtn.addEventListener('click', function() {
            if (isFavoriteLoading || !state.currentSelectedVariantId) {
                return;
            }
            
            toggleFavorite();
        });
    }
    
    
    function toggleFavorite() {
        if (!state.currentSelectedVariantId) {
            notificationManager.showNotification('Lütfen renk ve beden seçin', 'error');
            return;
        }
        
        if (isFavoriteLoading) {
            return;
        }
        
        isFavoriteLoading = true;
        const favoriteBtn = document.getElementById('favorite-btn');
        const favoriteIcon = document.getElementById('favorite-icon');
        
        if (!favoriteBtn || !favoriteIcon) {
            isFavoriteLoading = false;
            return;
        }
        
        
        favoriteBtn.disabled = true;
        favoriteIcon.classList.add('fa-spin');
        
        const isFavorite = favoriteVariantIds.includes(state.currentSelectedVariantId);
        const action = isFavorite ? 'remove' : 'add';
        
        fetch('/api/favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                variantId: state.currentSelectedVariantId,
                colorId: state.selectedColor
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Sunucu yanıt vermedi');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (action === 'add') {
                    favoriteVariantIds.push(state.currentSelectedVariantId);
                } else {
                    const index = favoriteVariantIds.indexOf(state.currentSelectedVariantId);
                    if (index > -1) {
                        favoriteVariantIds.splice(index, 1);
                    }
                }
                updateFavoriteStatus();
                
                
                notificationManager.showNotification(data.message || 'İşlem başarılı', 'success');
            } else {
                notificationManager.showNotification(data.message || 'İşlem başarısız', 'error');
                
                
                if (data.error_code === 'user_not_found' || data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect || 'logout.php';
                    }, 2000);
                }
            }
        })
        .catch(error => {
            notificationManager.showNotification('Bir hata oluştu: ' + error.message, 'error');
        })
        .finally(() => {
            isFavoriteLoading = false;
            favoriteBtn.disabled = false;
            favoriteIcon.classList.remove('fa-spin');
        });
    }
    
    
    document.addEventListener('variantSelected', function(event) {
        updateFavoriteStatus();
    });
    
    
    initFavoriteButton();
    
    
    return {
        updateFavoriteStatus: updateFavoriteStatus,
        toggleFavorite: toggleFavorite
    };
}