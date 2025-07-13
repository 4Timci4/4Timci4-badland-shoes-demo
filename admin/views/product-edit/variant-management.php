<!-- Variant Management Card -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Varyant Yönetimi</h3>
                <p class="text-gray-600 text-sm">Renk/beden kombinasyonları ve stok yönetimi</p>
            </div>
            <div class="text-right">
                <div id="total-stock-display" class="text-2xl font-bold text-green-600"><?= $total_stock ?></div>
                <div class="text-xs text-gray-500">Toplam Stok</div>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">

        <!-- Existing Variants -->
        <?php if (!empty($variants)): ?>
            <div class="space-y-4">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-list mr-2"></i>Mevcut Varyantlar (<span
                        id="variant-count"><?= count($variants) ?></span>)
                </h4>

                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-hidden border border-gray-200 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Renk</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Beden</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stok</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Durum</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($variants as $variant): ?>
                                <tr class="hover:bg-gray-50" data-variant-id="<?= $variant['id'] ?>"
                                    data-color-id="<?= $variant['color_id'] ?? '' ?>">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 rounded-full border border-gray-300"
                                                style="background-color: <?= htmlspecialchars($variant['color_hex'] ?? '#cccccc') ?>">
                                            </div>
                                            <span
                                                class="ml-3 text-sm text-gray-900"><?= htmlspecialchars($variant['color_name'] ?? 'Renk Yok') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($variant['size_value'] ?? 'Beden Yok') ?>
                                            <?= htmlspecialchars($variant['size_type'] ?? '') ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm text-gray-900 font-mono"><?= htmlspecialchars($variant['sku']) ?></span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <input type="number" value="<?= htmlspecialchars($variant['stock_quantity']) ?>" min="0"
                                            class="variant-stock w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                            data-variant-id="<?= $variant['id'] ?>">
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" <?= $variant['is_active'] ? 'checked' : '' ?>
                                                class="variant-active form-checkbox h-4 w-4 text-primary-600"
                                                data-variant-id="<?= $variant['id'] ?>">
                                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                        </label>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <button type="button" class="save-variant-btn text-green-600 hover:text-green-900 mr-3"
                                            data-variant-id="<?= $variant['id'] ?>">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <button type="button" class="delete-variant-btn text-red-600 hover:text-red-900"
                                            data-variant-id="<?= $variant['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden space-y-4">
                    <?php foreach ($variants as $variant): ?>
                        <?php
                        $colorId = $variant['color_id'] ?? 'default';
                        $colorImages = $productImagesByColor[$colorId] ?? [];
                        $imageCount = count($colorImages);
                        ?>
                        <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-4"
                            data-variant-id="<?= $variant['id'] ?>" data-color-id="<?= $colorId ?>">
                            <!-- Header -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full border border-gray-300"
                                        style="background-color: <?= htmlspecialchars($variant['color_hex'] ?? '#cccccc') ?>">
                                    </div>
                                    <div>
                                        <h5 class="font-medium text-gray-900">
                                            <?= htmlspecialchars($variant['color_name'] ?? 'Renk Yok') ?></h5>
                                        <p class="text-sm text-gray-500">
                                            <?= htmlspecialchars($variant['size_value'] ?? 'Beden Yok') ?>
                                            <?= htmlspecialchars($variant['size_type'] ?? '') ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="button" class="save-variant-btn text-green-600 hover:text-green-700 p-2"
                                        data-variant-id="<?= $variant['id'] ?>">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button type="button" class="delete-variant-btn text-red-600 hover:text-red-700 p-2"
                                        data-variant-id="<?= $variant['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">SKU</label>
                                    <p class="text-sm font-mono text-gray-900"><?= htmlspecialchars($variant['sku']) ?></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Durum</label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" <?= $variant['is_active'] ? 'checked' : '' ?>
                                            class="variant-active form-checkbox h-4 w-4 text-primary-600"
                                            data-variant-id="<?= $variant['id'] ?>">
                                        <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Stok</label>
                                    <input type="number" value="<?= htmlspecialchars($variant['stock_quantity']) ?>" min="0"
                                        class="variant-stock w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                        data-variant-id="<?= $variant['id'] ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-8 bg-gray-50 rounded-xl">
                <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Henüz varyant eklenmemiş</h4>
                <p class="text-gray-500">Bu ürün için renk ve beden kombinasyonları ekleyin.</p>
            </div>
        <?php endif; ?>

        <!-- Add New Variant -->
        <div class="border-t pt-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-plus mr-2"></i>Yeni Varyant Ekle
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Renk</label>
                    <select id="new-variant-color"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Renk Seçin</option>
                        <?php foreach ($all_colors as $color): ?>
                            <option value="<?= $color['id'] ?>" data-hex="<?= $color['hex_code'] ?>">
                                <?= htmlspecialchars($color['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Beden</label>
                    <select id="new-variant-size"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Beden Seçin</option>
                        <?php foreach ($all_sizes as $size): ?>
                            <option value="<?= $size['id'] ?>">
                                <?= htmlspecialchars($size['size_value']) ?>     <?= htmlspecialchars($size['size_type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stok Miktarı</label>
                    <input type="number" id="new-variant-stock" value="0" min="0" placeholder="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="new-variant-active" checked
                        class="form-checkbox h-4 w-4 text-primary-600">
                    <span class="ml-2 text-sm text-gray-700">Aktif varyant</span>
                </label>

                <button type="button" id="add-variant-btn"
                    class="px-6 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Varyant Ekle
                </button>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="border-t pt-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-tools mr-2"></i>Toplu İşlemler
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <button type="button" id="bulk-activate-btn"
                    class="px-4 py-2 bg-green-100 text-green-700 font-medium rounded-lg hover:bg-green-200 transition-colors">
                    <i class="fas fa-check mr-2"></i>Tümünü Aktif Et
                </button>

                <button type="button" id="bulk-deactivate-btn"
                    class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-ban mr-2"></i>Tümünü Pasif Et
                </button>

                <button type="button" id="bulk-delete-btn"
                    class="px-4 py-2 bg-red-100 text-red-700 font-medium rounded-lg hover:bg-red-200 transition-colors">
                    <i class="fas fa-trash mr-2"></i>Tümünü Sil
                </button>
            </div>
        </div>
    </div>
</div>