<div class="p-6">
    <form method="POST">
        <input type="hidden" name="action" value="update_analytics_settings">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div class="space-y-8">
            
            <!-- Google Analytics -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fab fa-google text-red-500 mr-2"></i>
                    Google Analytics & Tools
                </h3>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Google Analytics ID</label>
                        <input type="text" 
                               name="google_analytics_id" 
                               value="<?= htmlspecialchars($analyticsSettings['google_analytics_id']['value'] ?? '') ?>"
                               placeholder="G-XXXXXXXXXX"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Google Tag Manager ID</label>
                        <input type="text" 
                               name="google_tag_manager_id" 
                               value="<?= htmlspecialchars($analyticsSettings['google_tag_manager_id']['value'] ?? '') ?>"
                               placeholder="GTM-XXXXXXX"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Google Search Console Doğrulama</label>
                        <input type="text" 
                               name="google_search_console" 
                               value="<?= htmlspecialchars($analyticsSettings['google_search_console']['value'] ?? '') ?>"
                               placeholder="google-site-verification=..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <!-- Social Media Analytics -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sosyal Medya Analytics</h3>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facebook Pixel ID</label>
                        <input type="text" 
                               name="facebook_pixel_id" 
                               value="<?= htmlspecialchars($analyticsSettings['facebook_pixel_id']['value'] ?? '') ?>"
                               placeholder="000000000000000"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <!-- Other Search Engines -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Diğer Arama Motorları</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bing Webmaster Doğrulama</label>
                        <input type="text" 
                               name="bing_webmaster" 
                               value="<?= htmlspecialchars($analyticsSettings['bing_webmaster']['value'] ?? '') ?>"
                               placeholder="msvalidate.01=..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Yandex Doğrulama</label>
                        <input type="text" 
                               name="yandex_verification" 
                               value="<?= htmlspecialchars($analyticsSettings['yandex_verification']['value'] ?? '') ?>"
                               placeholder="yandex-verification=..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <button type="submit" 
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-save mr-2"></i>
                Analytics Ayarlarını Kaydet
            </button>
        </div>
    </form>
</div>
