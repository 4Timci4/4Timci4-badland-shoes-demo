<!-- Basic Information Card -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-xl font-bold text-gray-900 mb-1">Temel Bilgiler</h3>
        <p class="text-gray-600 text-sm">Ürünün temel bilgilerini güncelleyin</p>
    </div>
    <div class="p-6 space-y-6">
        
        <!-- Product Name -->
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-tag mr-2"></i>Ürün Adı *
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   required
                   value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                   placeholder="Örn: Nike Air Max 270"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-align-left mr-2"></i>Ürün Açıklaması *
            </label>
            <textarea id="description" 
                      name="description" 
                      required
                      rows="4"
                      placeholder="Ürününüzün detaylı açıklamasını yazın..."
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <!-- Features -->
        <div>
            <label for="features" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-list mr-2"></i>Özellikler
            </label>
            <textarea id="features" 
                      name="features" 
                      rows="3"
                      placeholder="Ürün özelliklerini listeleyin (her satıra bir özellik)..."
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($product['features'] ?? '') ?></textarea>
            <p class="text-xs text-gray-500 mt-2">Her satıra bir özellik yazın. Örn: "Su geçirmez", "Nefes alabilir kumaş"</p>
        </div>
    </div>
</div>