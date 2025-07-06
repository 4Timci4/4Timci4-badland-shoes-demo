<div class="p-6">
    <form method="POST">
        <input type="hidden" name="action" value="update_technical_seo_settings">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div class="space-y-8">
            
            <!-- SEO Features -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Özellikleri</h3>
                <div class="space-y-4">
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="font-medium text-gray-900">Canonical URL'ler</label>
                            <p class="text-sm text-gray-600">Duplicate content sorunlarını önler</p>
                        </div>
                        <input type="checkbox" 
                               name="canonical_enabled" 
                               <?= ($technicalSettings['canonical_enabled']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>
                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="font-medium text-gray-900">XML Sitemap</label>
                            <p class="text-sm text-gray-600">Arama motorları için site haritası oluşturur</p>
                        </div>
                        <input type="checkbox" 
                               name="sitemap_enabled" 
                               <?= ($technicalSettings['sitemap_enabled']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>
                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="font-medium text-gray-900">Schema.org Markup</label>
                            <p class="text-sm text-gray-600">Yapılandırılmış veri ekler</p>
                        </div>
                        <input type="checkbox" 
                               name="schema_enabled" 
                               <?= ($technicalSettings['schema_enabled']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>
                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="font-medium text-gray-900">Breadcrumbs</label>
                            <p class="text-sm text-gray-600">Sayfa hiyerarşisi için breadcrumb navigasyonu</p>
                        </div>
                        <input type="checkbox" 
                               name="breadcrumbs_enabled" 
                               <?= ($technicalSettings['breadcrumbs_enabled']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>
                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="font-medium text-gray-900">AMP (Accelerated Mobile Pages)</label>
                            <p class="text-sm text-gray-600">Mobil cihazlarda hızlı yüklenen sayfalar</p>
                        </div>
                        <input type="checkbox" 
                               name="amp_enabled" 
                               <?= ($technicalSettings['amp_enabled']['value'] ?? 'false') === 'true' ? 'checked' : '' ?>
                               class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    </div>
                </div>
            </div>

            <!-- SEO Status -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Durumu</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-blue-900 mb-2">SEO Optimizasyon İpuçları</h4>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Canonical URL'ler duplicate content sorunlarını önler</li>
                                <li>• XML sitemap arama motorlarının sitenizi daha iyi anlamasına yardımcı olur</li>
                                <li>• Schema.org markup rich snippets için gereklidir</li>
                                <li>• Breadcrumbs kullanıcı deneyimini ve SEO'yu iyileştirir</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <button type="submit" 
                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-save mr-2"></i>
                Teknik SEO Ayarlarını Kaydet
            </button>
        </div>
    </form>
</div>
