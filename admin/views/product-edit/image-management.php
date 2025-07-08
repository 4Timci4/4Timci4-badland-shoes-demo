<!-- Product Images Management Card -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Varyant Görsel Yönetimi</h3>
                <p class="text-gray-600 text-sm">Her renk varyantı için görselleri yönetin</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-blue-600"><?= count($productImages) ?></div>
                <div class="text-xs text-gray-500">Toplam Görsel</div>
            </div>
        </div>
    </div>
    
    <div class="p-6 space-y-6">
        
        <!-- Quick Upload Section -->
        <div class="bg-gray-50 rounded-xl p-4 md:p-6 border-2 border-dashed border-gray-200">
            <div class="text-center">
                <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl md:text-3xl mb-3 md:mb-4"></i>
                <h4 class="text-base md:text-lg font-semibold text-gray-900 mb-2">Hızlı Görsel Yükleme</h4>
                <p class="text-sm md:text-base text-gray-600 mb-4">Birden fazla görsel yükleyip renklere atayın</p>
                
                <!-- Desktop: Yan yana | Mobile: Alt alta -->
                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    <button type="button"
                            id="quick-upload-btn"
                            class="w-full sm:w-auto px-4 md:px-6 py-2.5 md:py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors text-sm md:text-base">
                        <i class="fas fa-upload mr-2"></i>Görsel Yükle
                    </button>
                    
                    <a href="product-image-upload.php?product_id=<?= $product_id ?>"
                       target="_blank"
                       class="w-full sm:w-auto px-4 md:px-6 py-2.5 md:py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors text-sm md:text-base text-center">
                        <i class="fas fa-external-link-alt mr-2"></i>Gelişmiş Yönetim
                    </a>
                </div>
            </div>
        </div>

        <!-- Color-based Image Management -->
        <?php
        // Sadece mevcut varyantların renklerini al
        $variant_colors = [];
        if (!empty($variants)) {
            $temp_colors = [];
            foreach ($variants as $variant) {
                if (!empty($variant['color_id']) && !isset($temp_colors[$variant['color_id']])) {
                    $temp_colors[$variant['color_id']] = [
                        'id' => $variant['color_id'],
                        'name' => $variant['color_name'] ?? 'Bilinmeyen Renk',
                        'hex_code' => $variant['color_hex'] ?? '#cccccc'
                    ];
                }
            }
            $variant_colors = array_values($temp_colors);
        }
        ?>
        
        <?php if (!empty($variant_colors)): ?>
        <div class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-palette mr-2"></i>Varyant Renkleri - Görsel Yönetimi
            </h4>
            <p class="text-sm text-gray-600 mb-4">
                <i class="fas fa-info-circle mr-1"></i>
                Sadece oluşturulmuş varyant renklerine görsel ekleyebilirsiniz
            </p>
            
            <!-- Color Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-4 md:space-x-8 overflow-x-auto scrollbar-hide pb-2 md:pb-0">
                    <!-- Default/All Images Tab -->
                    <button type="button"
                            class="color-tab-btn py-2 px-1 border-b-2 font-medium text-xs md:text-sm whitespace-nowrap transition-colors flex-shrink-0
                                   <?= empty($productImagesByColor) ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>"
                            data-color-id="default">
                        <div class="flex items-center">
                            <div class="w-3 h-3 md:w-4 md:h-4 bg-gradient-to-br from-gray-300 to-gray-400 rounded-full mr-1.5 md:mr-2"></div>
                            <span class="hidden sm:inline">Varsayılan</span>
                            <span class="sm:hidden">Var.</span>
                            <span class="ml-1 md:ml-2 bg-gray-100 text-gray-600 py-0.5 px-1.5 md:px-2 rounded-full text-xs">
                                <?= count($productImagesByColor['default'] ?? []) ?>
                            </span>
                        </div>
                    </button>
                    
                    <!-- Variant Color Tabs -->
                    <?php foreach ($variant_colors as $color): ?>
                        <?php
                        $colorImages = $productImagesByColor[$color['id']] ?? [];
                        $colorImageCount = count($colorImages);
                        ?>
                        <button type="button"
                                class="color-tab-btn py-2 px-1 border-b-2 font-medium text-xs md:text-sm whitespace-nowrap transition-colors border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 flex-shrink-0"
                                data-color-id="<?= $color['id'] ?>"
                                data-color-name="<?= htmlspecialchars($color['name']) ?>">
                            <div class="flex items-center">
                                <div class="w-3 h-3 md:w-4 md:h-4 rounded-full mr-1.5 md:mr-2 border border-gray-300"
                                     style="background-color: <?= htmlspecialchars($color['hex_code']) ?>"></div>
                                <span class="truncate max-w-[60px] md:max-w-none"><?= htmlspecialchars($color['name']) ?></span>
                                <span class="ml-1 md:ml-2 bg-gray-100 text-gray-600 py-0.5 px-1.5 md:px-2 rounded-full text-xs">
                                    <?= $colorImageCount ?>
                                </span>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </nav>
            </div>
            
            <!-- Color Image Content -->
            <div id="color-images-content" class="mt-6">
                <!-- Default images content -->
                <div class="color-images-panel" data-color-id="default">
                    <?php
                    $defaultImages = $productImagesByColor['default'] ?? [];
                    if (!empty($defaultImages)):
                    ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
                            <?php foreach ($defaultImages as $image): ?>
                                <div class="relative group">
                                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                        <img src="<?= htmlspecialchars($image['thumbnail_url'] ?? $image['image_url']) ?>"
                                             alt="<?= htmlspecialchars($image['alt_text'] ?? '') ?>"
                                             class="w-full h-full object-cover">
                                    </div>
                                    
                                    <!-- Image Actions -->
                                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                        <div class="flex space-x-2">
                                            <?php if ($image['is_primary']): ?>
                                                <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded">Primary</span>
                                            <?php else: ?>
                                                <button type="button"
                                                        class="set-primary-btn bg-yellow-500 text-white text-xs px-2 py-1 rounded hover:bg-yellow-600"
                                                        data-image-id="<?= $image['id'] ?>">
                                                    Primary Yap
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="button"
                                                    class="delete-image-btn bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600"
                                                    data-image-id="<?= $image['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-6 md:py-8 text-gray-500">
                            <i class="fas fa-image text-2xl md:text-3xl mb-3 md:mb-4"></i>
                            <p class="text-sm md:text-base mb-3 md:mb-4">Bu renk için henüz görsel yüklenmemiş</p>
                            <button type="button"
                                    class="upload-for-color-btn px-3 md:px-4 py-2 bg-blue-600 text-white text-sm md:text-base rounded-lg hover:bg-blue-700"
                                    data-color-id="default">
                                <i class="fas fa-plus mr-1 md:mr-2"></i>Görsel Ekle
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Other color panels will be generated by JavaScript -->
            </div>
        </div>
        <?php else: ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-4"></i>
            <h4 class="text-lg font-semibold text-yellow-800 mb-2">Henüz Varyant Eklenmemiş</h4>
            <p class="text-yellow-700 mb-4">
                Görsel yönetimi için önce ürününüze renk varyantları eklemelisiniz.
            </p>
            <p class="text-sm text-yellow-600">
                Yukarıdaki "Varyant Yönetimi" bölümünden renk ve beden kombinasyonları ekleyin.
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Image Upload Progress -->
        <div id="upload-progress" class="hidden">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-3"></div>
                    <div class="flex-1">
                        <h4 class="text-blue-900 font-medium">Görseller yükleniyor...</h4>
                        <div class="mt-2 bg-blue-200 rounded-full h-2">
                            <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden File Input for Image Upload -->
<input type="file" id="hidden-file-input" multiple accept="image/*" style="display: none;">