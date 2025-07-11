// Favori işlevselliği
export function initializeFavorites(state, favoriteVariantIds, isLoggedIn, notificationManager) {
    let isFavoriteLoading = false;
    
    // Favori durumunu kontrol et ve güncelle
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
    
    // Favori butonu click event'i
    function initFavoriteButton() {
        const favoriteBtn = document.getElementById('favorite-btn');
        console.log('initFavoriteButton çağrıldı', favoriteBtn, 'Giriş durumu:', isLoggedIn);
        
        if (!favoriteBtn || !isLoggedIn) {
            console.log('Favori butonu bulunamadı veya kullanıcı giriş yapmamış');
            return;
        }
        
        favoriteBtn.addEventListener('click', function() {
            console.log('Favori butonuna tıklandı', 'Yükleniyor:', isFavoriteLoading, 'Seçili varyant:', state.currentSelectedVariantId);
            
            if (isFavoriteLoading || !state.currentSelectedVariantId) {
                console.log('İşlem yapılamıyor: Yükleniyor veya varyant seçilmemiş');
                return;
            }
            
            toggleFavorite();
        });
        
        console.log('Favori butonu event listener eklendi');
    }
    
    // Favori ekleme/çıkarma
    function toggleFavorite() {
        console.log('toggleFavorite çağrıldı', 'Seçili varyant:', state.currentSelectedVariantId);
        
        if (!state.currentSelectedVariantId) {
            console.error('Varyant seçilmemiş, favori işlemi yapılamıyor');
            notificationManager.showNotification('Lütfen renk ve beden seçin', 'error');
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
        
        const isFavorite = favoriteVariantIds.includes(state.currentSelectedVariantId);
        const action = isFavorite ? 'remove' : 'add';
        
        console.log('Favori işlemi başlatılıyor:', action, 'Varyant ID:', state.currentSelectedVariantId);
        
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
            console.log('API yanıtı:', data);
            
            if (data.success) {
                if (action === 'add') {
                    favoriteVariantIds.push(state.currentSelectedVariantId);
                    console.log('Favorilere eklendi:', state.currentSelectedVariantId);
                } else {
                    const index = favoriteVariantIds.indexOf(state.currentSelectedVariantId);
                    if (index > -1) {
                        favoriteVariantIds.splice(index, 1);
                        console.log('Favorilerden çıkarıldı:', state.currentSelectedVariantId);
                    }
                }
                updateFavoriteStatus();
                
                // Başarı mesajı göster
                notificationManager.showNotification(data.message || 'İşlem başarılı', 'success');
            } else {
                console.error('API hatası:', data.message);
                notificationManager.showNotification(data.message || 'İşlem başarısız', 'error');
                
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
            notificationManager.showNotification('Bir hata oluştu: ' + error.message, 'error');
        })
        .finally(() => {
            isFavoriteLoading = false;
            favoriteBtn.disabled = false;
            favoriteIcon.classList.remove('fa-spin');
            console.log('Favori işlemi tamamlandı');
        });
    }
    
    // Varyant seçimi değiştiğinde favori durumunu güncelle
    document.addEventListener('variantSelected', function(event) {
        updateFavoriteStatus();
    });
    
    // Favori butonunu başlat
    initFavoriteButton();
    
    // Public API
    return {
        updateFavoriteStatus: updateFavoriteStatus,
        toggleFavorite: toggleFavorite
    };
}