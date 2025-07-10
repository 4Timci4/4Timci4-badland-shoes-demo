<script>
// Verileri global değişkenler olarak tanımlayalım
const productData = <?php echo json_encode($product); ?>;
const productVariantsData = productData.variants || [];
const productColorsData = <?php echo json_encode($all_colors ?? []); ?>; // Bu, controller'dan gelmeye devam ediyor
const productName = "<?php echo addslashes($product['name']); ?>";
const isLoggedIn = <?php echo isset($_SESSION['user_session']) && isset($_SESSION['user_session']['user']) ? 'true' : 'false'; ?>;
</script>
<script src="/assets/js/product-detail.js"></script>

<?php if (isset($_SESSION['user_session']) && isset($_SESSION['user_session']['user'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteButton = document.getElementById('favorite-button');
    const favoriteIcon = document.getElementById('favorite-icon');
    let currentVariantId = null;
    let isFavorite = false;

    // Renk ve beden seçildiğinde çalışacak fonksiyon
    function checkFavoriteStatus() {
        // product-detail.js'de tanımlı değişkenlere doğrudan erişemiyoruz
        // O yüzden aktif varyantı bulmak için farklı bir yöntem kullanacağız
        
        // Seçili renk ve beden butonlarını bul
        const selectedColorButton = document.querySelector('.color-option.border-secondary');
        const selectedSizeButton = document.querySelector('.size-option.bg-primary');
        
        if (!selectedColorButton || !selectedSizeButton) return;
        
        const colorId = parseInt(selectedColorButton.dataset.colorId);
        const sizeId = parseInt(selectedSizeButton.dataset.size);
        
        if (!colorId || !sizeId) return;
        
        // Seçili varyantı bul
        const variant = productVariantsData.find(v =>
            v.color_id === colorId && v.size_id === sizeId
        );
        
        if (!variant) return;
        
        // Varyant ID'sini güncelle
        currentVariantId = variant.id;
        
        // Favori durumunu kontrol et
        fetch(`/api/favorites.php?variant_id=${currentVariantId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    isFavorite = data.is_favorite;
                    updateFavoriteIcon();
                }
            })
            .catch(error => {
                console.error('Favori durumu kontrol edilirken hata oluştu:', error);
            });
    }
    
    // Favori ikonunu güncelle
    function updateFavoriteIcon() {
        if (isFavorite) {
            favoriteIcon.classList.remove('far');
            favoriteIcon.classList.add('fas');
        } else {
            favoriteIcon.classList.remove('fas');
            favoriteIcon.classList.add('far');
        }
    }
    
    // Favori butonuna tıklandığında
    favoriteButton.addEventListener('click', function() {
        if (!currentVariantId) return;
        
        const formData = new FormData();
        formData.append('variant_id', currentVariantId);
        const action = isFavorite ? 'remove' : 'add';
        formData.append('action', action);

        if (action === 'add') {
            const selectedColorButton = document.querySelector('.color-option.border-secondary');
            if (selectedColorButton) {
                formData.append('color_id', selectedColorButton.dataset.colorId);
            }
        }
        
        fetch('/api/favorites.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                isFavorite = !isFavorite;
                updateFavoriteIcon();
                
                // Kullanıcıya bildirim göster
                const message = isFavorite ? 'Ürün favorilerinize eklendi' : 'Ürün favorilerinizden kaldırıldı';
                const icon = isFavorite ? 'success' : 'info';
                window.modal.alert(message, isFavorite ? 'Başarılı' : 'Bilgi', icon);
            } else {
                window.modal.error(data.message || 'Bir hata oluştu');
            }
        })
        .catch(error => {
            console.error('Favori işlemi sırasında hata oluştu:', error);
            window.modal.error('İşlem sırasında bir hata oluştu');
        });
    });
    
    // Beden seçildiğinde favori durumunu kontrol et
    const originalSizeClickHandler = document.querySelectorAll('.size-option').forEach(button => {
        const originalClickEvent = button.onclick;
        
        button.addEventListener('click', function() {
            // Orijinal click event'ini çalıştır
            if (originalClickEvent) {
                originalClickEvent.call(this);
            }
            
            // Favori durumunu kontrol et
            setTimeout(checkFavoriteStatus, 100);
        });
    });
    
    // Renk seçildiğinde favori durumunu kontrol et
    const originalSelectColor = window.selectColor;
    if (typeof originalSelectColor === 'function') {
        window.selectColor = function(colorId, colorName) {
            originalSelectColor(colorId, colorName);
            setTimeout(checkFavoriteStatus, 100);
        };
    }
});
</script>
<?php endif; ?>
