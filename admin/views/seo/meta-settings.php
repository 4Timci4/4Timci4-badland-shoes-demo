<div class="p-6">
    <form method="POST">
        <input type="hidden" name="action" value="update_meta_settings">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div class="space-y-8">
            
            <!-- Default Meta Tags -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Varsayılan Meta Etiketler</h3>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Varsayılan Title</label>
                        <input type="text" 
                               name="default_title" 
                               value="<?= htmlspecialchars($metaSettings['default_title']['value'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-sm text-gray-500 mt-1">Sayfa title'ının varsayılan değeri</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title Ayırıcısı</label>
                        <input type="text" 
                               name="title_separator" 
                               value="<?= htmlspecialchars($metaSettings['title_separator']['value'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-sm text-gray-500 mt-1">Sayfa title'ı ile site adı arasında kullanılacak ayırıcı (örn: " | ")</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Varsayılan Meta Description</label>
                        <textarea name="default_description" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($metaSettings['default_description']['value'] ?? '') ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Arama motorlarında görünecek site açıklaması (maksimum 160 karakter)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Varsayılan Keywords</label>
                        <input type="text" 
                               name="default_keywords" 
                               value="<?= htmlspecialchars($metaSettings['default_keywords']['value'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-sm text-gray-500 mt-1">Virgülle ayrılmış anahtar kelimeler</p>
                    </div>
                </div>
            </div>

            <!-- Site Author & Robots -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Site Bilgileri</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                        <input type="text" 
                               name="author" 
                               value="<?= htmlspecialchars($metaSettings['author']['value'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Robots Meta Tag</label>
                        <select name="robots" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="index, follow" <?= ($metaSettings['robots']['value'] ?? '') === 'index, follow' ? 'selected' : '' ?>>index, follow</option>
                            <option value="noindex, follow" <?= ($metaSettings['robots']['value'] ?? '') === 'noindex, follow' ? 'selected' : '' ?>>noindex, follow</option>
                            <option value="index, nofollow" <?= ($metaSettings['robots']['value'] ?? '') === 'index, nofollow' ? 'selected' : '' ?>>index, nofollow</option>
                            <option value="noindex, nofollow" <?= ($metaSettings['robots']['value'] ?? '') === 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <button type="submit" 
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-save mr-2"></i>
                Meta Ayarlarını Kaydet
            </button>
        </div>
    </form>
</div>
