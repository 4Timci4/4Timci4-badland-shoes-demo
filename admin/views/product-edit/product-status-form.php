<!-- Product Status Card -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-xl font-bold text-gray-900 mb-1">Ürün Durumu</h3>
        <p class="text-gray-600 text-sm">Ürünün görünürlük ayarlarını güncelleyin</p>
    </div>
    <div class="p-6">
        
        <!-- Featured Status -->
        <div class="flex items-start space-x-4">
            <div class="flex items-center h-5">
                <input type="checkbox" 
                       id="is_featured" 
                       name="is_featured" 
                       value="1"
                       <?= $product['is_featured'] ? 'checked' : '' ?>
                       class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2">
            </div>
            <div class="text-sm">
                <label for="is_featured" class="font-semibold text-gray-700 cursor-pointer">
                    <i class="fas fa-star text-yellow-500 mr-2"></i>Öne Çıkarılmış Ürün
                </label>
                <p class="text-gray-500">Bu ürün ana sayfada öne çıkarılmış ürünler bölümünde görünecektir.</p>
            </div>
        </div>
    </div>
</div>