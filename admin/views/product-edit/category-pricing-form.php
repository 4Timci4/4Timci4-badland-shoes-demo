<?php
// "Add" modunda bu değişkenler tanımsız olabileceğinden, boş bir dizi olarak başlat
$selected_categories = $selected_categories ?? [];
$selected_genders = $selected_genders ?? [];
?>
<!-- Category and Pricing Card -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h3 class="text-xl font-bold text-gray-900 mb-1">Kategori ve Fiyatlandırma</h3>
        <p class="text-gray-600 text-sm">Ürün kategorisi ve fiyat bilgilerini güncelleyin</p>
    </div>
    <div class="p-6 space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Multi-Category Selection -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-box mr-2"></i>Ürün Kategorileri *
                </label>
                <p class="text-xs text-gray-500 mb-4">Ürününüzün tipini seçin (örn: Sneaker, Bot, Sandalet, vb.)</p>
                
                <div class="space-y-6" id="category-selection-container">
                    <div class="border border-gray-200 rounded-xl p-4">
                        <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-box text-blue-500 mr-2"></i>
                            Ürün Tipleri
                            <span class="ml-2 text-xs text-gray-500">(<?= count($categories) ?> kategori)</span>
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <?php foreach ($categories as $category): ?>
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="checkbox"
                                           name="category_ids[]"
                                           value="<?= htmlspecialchars($category['category_id']) ?>"
                                           <?= in_array($category['category_id'], $selected_categories ?? []) ? 'checked' : '' ?>
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Selected Categories Preview -->
                <div id="selected-categories-preview" class="mt-4 p-3 bg-gray-50 rounded-lg hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">Seçilen Kategoriler:</p>
                    <div id="selected-categories-list" class="flex flex-wrap gap-2"></div>
                </div>
            </div>
            
            <!-- Gender Selection -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-venus-mars mr-2"></i>Cinsiyetler *
                </label>
                <p class="text-xs text-gray-500 mb-4">Ürününüzün hitap ettiği cinsiyetleri seçin (örn: Erkek, Kadın, Çocuk, Unisex)</p>
                
                <div class="border border-gray-200 rounded-xl p-4" id="gender-selection-container">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-venus-mars text-purple-500 mr-2"></i>
                        Cinsiyetler
                        <span class="ml-2 text-xs text-gray-500">(<?= count($genders) ?> cinsiyet)</span>
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php foreach ($genders as $gender): ?>
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox" 
                                       name="gender_ids[]" 
                                       value="<?= htmlspecialchars($gender['id']) ?>"
                                       <?= in_array($gender['id'], $selected_genders ?? []) ? 'checked' : '' ?>
                                       class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2">
                                <span class="ml-2 text-sm font-medium text-gray-700">
                                    <?= htmlspecialchars($gender['name']) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Selected Genders Preview -->
                <div id="selected-genders-preview" class="mt-4 p-3 bg-gray-50 rounded-lg hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">Seçilen Cinsiyetler:</p>
                    <div id="selected-genders-list" class="flex flex-wrap gap-2"></div>
                </div>
            </div>

        </div>
    </div>
</div>