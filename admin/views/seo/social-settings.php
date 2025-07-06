<div class="p-6">
    <form method="POST">
        <input type="hidden" name="action" value="update_social_settings">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div class="space-y-8">
            
            <!-- Open Graph (Facebook) -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fab fa-facebook text-blue-600 mr-2"></i>
                    Open Graph (Facebook)
                </h3>
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Adı</label>
                            <input type="text" 
                                   name="og_site_name" 
                                   value="<?= htmlspecialchars($socialSettings['og_site_name']['value'] ?? '') ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Tipi</label>
                            <select name="og_type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="website" <?= ($socialSettings['og_type']['value'] ?? '') === 'website' ? 'selected' : '' ?>>Website</option>
                                <option value="blog" <?= ($socialSettings['og_type']['value'] ?? '') === 'blog' ? 'selected' : '' ?>>Blog</option>
                                <option value="article" <?= ($socialSettings['og_type']['value'] ?? '') === 'article' ? 'selected' : '' ?>>Article</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Varsayılan Paylaşım Görseli</label>
                        <input type="text" 
                               name="og_image" 
                               value="<?= htmlspecialchars($socialSettings['og_image']['value'] ?? '') ?>"
                               placeholder="assets/images/og-image.jpg"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-sm text-gray-500 mt-1">1200x630 piksel boyutunda olmalı</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facebook App ID</label>
                        <input type="text" 
                               name="facebook_app_id" 
                               value="<?= htmlspecialchars($socialSettings['facebook_app_id']['value'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <!-- Twitter -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fab fa-twitter text-blue-400 mr-2"></i>
                    Twitter
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Twitter Card Tipi</label>
                        <select name="twitter_card" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="summary" <?= ($socialSettings['twitter_card']['value'] ?? '') === 'summary' ? 'selected' : '' ?>>Summary</option>
                            <option value="summary_large_image" <?= ($socialSettings['twitter_card']['value'] ?? '') === 'summary_large_image' ? 'selected' : '' ?>>Summary Large Image</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Twitter Kullanıcı Adı</label>
                        <input type="text" 
                               name="twitter_site" 
                               value="<?= htmlspecialchars($socialSettings['twitter_site']['value'] ?? '') ?>"
                               placeholder="@kullaniciadi"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <!-- LinkedIn -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fab fa-linkedin text-blue-700 mr-2"></i>
                    LinkedIn
                </h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Şirket Adı</label>
                    <input type="text" 
                           name="linkedin_company" 
                           value="<?= htmlspecialchars($socialSettings['linkedin_company']['value'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <button type="submit" 
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-save mr-2"></i>
                Sosyal Medya Ayarlarını Kaydet
            </button>
        </div>
    </form>
</div>
