<!-- Aksiyon Butonları -->
<div class="action-buttons space-y-3 mt-4">
    <button id="add-to-cart" 
            class="w-1/2 bg-brand text-secondary py-3 rounded text-sm font-bold hover:bg-opacity-80 transition-all duration-300 disabled:bg-gray-200 disabled:cursor-not-allowed flex items-center justify-center gap-2" 
            disabled
            aria-label="Ürünü sepete ekle" 
            aria-describedby="cart-button-description"
            title="Sepete eklemek için renk ve beden seçimi yapmalısınız">
        <span id="add-to-cart-text">Seçim Yapın</span>
        <span id="loading-indicator" class="hidden">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </span>
    </button>
    <span id="cart-button-description" class="sr-only">Sepete eklemek için önce renk ve beden seçimi yapmalısınız</span>
</div>

<!-- Stok Durumu -->
<div class="stock-info">
    <p class="text-xs text-green-600" id="stock-status"></p>
</div>
