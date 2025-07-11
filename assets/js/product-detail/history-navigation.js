// Tarayıcı gezinme işlevselliği
export function initializeHistoryNavigation(state, colorSelector) {
    // Tarayıcı geri/ileri butonları için
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const colorSlug = urlParams.get('color');
        
        if (colorSlug && event.state && event.state.colorId) {
            // State'den renk bilgisini al
            const colorId = event.state.colorId;
            const colorButton = document.querySelector('.color-option[data-color-id="' + colorId + '"]');
            if (colorButton) {
                colorSelector.selectColor(colorId, colorButton.dataset.colorName);
            }
        } else if (!colorSlug) {
            // Renk parametresi yoksa ilk rengi seç
            const firstColorButton = document.querySelector('.color-option');
            if (firstColorButton) {
                colorSelector.selectColor(parseInt(firstColorButton.dataset.colorId), firstColorButton.dataset.colorName);
            }
        }
    });
    
    // Sayfa yüklendiğinde URL'den renk parametresini kontrol et
    function checkInitialUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const urlColorSlug = urlParams.get('color');
        if (urlColorSlug) {
            const colorButton = document.querySelector('.color-option[data-color-slug="' + urlColorSlug + '"]');
            if (colorButton) {
                colorSelector.selectColor(parseInt(colorButton.dataset.colorId), colorButton.dataset.colorName);
            }
        }
    }
    
    // URL parametrelerini kontrol et
    checkInitialUrlParams();
    
    // Public API
    return {
        checkInitialUrlParams: checkInitialUrlParams
    };
}