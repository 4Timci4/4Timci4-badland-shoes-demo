<?php
/**
 * Admin Panel - SEO Ayarları
 * SEO meta bilgileri ve sosyal medya ayarlarını yönetme sayfası
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';
require_once '../services/SettingsService.php';

$settingsService = new SettingsService();

// Sayfa bilgileri
$page_title = 'SEO Ayarları';
$breadcrumb_items = [
    ['title' => 'Ayarlar', 'url' => '#', 'icon' => 'fas fa-cog'],
    ['title' => 'SEO Ayarları', 'url' => 'seo-settings.php', 'icon' => 'fas fa-search']
];

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        
        // Meta ayarları güncelleme
        if (isset($_POST['action']) && $_POST['action'] === 'update_meta_settings') {
            $meta_keys = ['default_title', 'title_separator', 'default_description', 'default_keywords', 'author', 'robots'];
            
            $success_count = 0;
            foreach ($meta_keys as $key) {
                if (isset($_POST[$key])) {
                    if ($settingsService->updateSeoSetting($key, $_POST[$key], 'meta', true)) {
                        $success_count++;
                    }
                }
            }
            
            if ($success_count > 0) {
                set_flash_message('success', 'Meta ayarları başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'Meta ayarları güncellenirken bir hata oluştu.');
            }
            header('Location: seo-settings.php');
            exit;
        }
        
        // Sosyal medya ayarları güncelleme
        if (isset($_POST['action']) && $_POST['action'] === 'update_social_settings') {
            $social_keys = ['og_site_name', 'og_type', 'og_image', 'twitter_card', 'twitter_site', 'facebook_app_id', 'linkedin_company'];
            
            $success_count = 0;
            foreach ($social_keys as $key) {
                if (isset($_POST[$key])) {
                    if ($settingsService->updateSeoSetting($key, $_POST[$key], 'social', true)) {
                        $success_count++;
                    }
                }
            }
            
            if ($success_count > 0) {
                set_flash_message('success', 'Sosyal medya ayarları başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'Sosyal medya ayarları güncellenirken bir hata oluştu.');
            }
            header('Location: seo-settings.php#social');
            exit;
        }
        
        // Analytics ayarları güncelleme
        if (isset($_POST['action']) && $_POST['action'] === 'update_analytics_settings') {
            $analytics_keys = ['google_analytics_id', 'google_tag_manager_id', 'facebook_pixel_id', 'google_search_console', 'bing_webmaster', 'yandex_verification'];
            
            $success_count = 0;
            foreach ($analytics_keys as $key) {
                if (isset($_POST[$key])) {
                    if ($settingsService->updateSeoSetting($key, $_POST[$key], 'analytics', true)) {
                        $success_count++;
                    }
                }
            }
            
            if ($success_count > 0) {
                set_flash_message('success', 'Analytics ayarları başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'Analytics ayarları güncellenirken bir hata oluştu.');
            }
            header('Location: seo-settings.php#analytics');
            exit;
        }
        
        // Teknik SEO ayarları güncelleme
        if (isset($_POST['action']) && $_POST['action'] === 'update_technical_seo_settings') {
            $technical_keys = ['canonical_enabled', 'sitemap_enabled', 'schema_enabled', 'breadcrumbs_enabled', 'amp_enabled'];
            
            $success_count = 0;
            foreach ($technical_keys as $key) {
                $value = isset($_POST[$key]) ? 'true' : 'false';
                if ($settingsService->updateSeoSetting($key, $value, 'technical', true)) {
                    $success_count++;
                }
            }
            
            if ($success_count > 0) {
                set_flash_message('success', 'Teknik SEO ayarları başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'Teknik SEO ayarları güncellenirken bir hata oluştu.');
            }
            header('Location: seo-settings.php#technical');
            exit;
        }
    }
}

// Mevcut SEO ayarlarını getir
$meta_settings = $settingsService->getSeoSettingsByType('meta');
$social_settings = $settingsService->getSeoSettingsByType('social');
$analytics_settings = $settingsService->getSeoSettingsByType('analytics');
$technical_settings = $settingsService->getSeoSettingsByType('technical');

// Varsayılan değerlerle birleştir
$default_seo = $settingsService->getDefaultSeoSettings();

foreach ($default_seo['meta'] as $key => $value) {
    if (!isset($meta_settings[$key])) {
        $meta_settings[$key] = ['value' => $value, 'is_active' => true];
    }
}

foreach ($default_seo['social'] as $key => $value) {
    if (!isset($social_settings[$key])) {
        $social_settings[$key] = ['value' => $value, 'is_active' => true];
    }
}

foreach ($default_seo['analytics'] as $key => $value) {
    if (!isset($analytics_settings[$key])) {
        $analytics_settings[$key] = ['value' => $value, 'is_active' => true];
    }
}

foreach ($default_seo['technical'] as $key => $value) {
    if (!isset($technical_settings[$key])) {
        $technical_settings[$key] = ['value' => $value, 'is_active' => true];
    }
}

// Gerekli CSS ve JS
$additional_css = [];
$additional_js = [];

// Header dahil et
include 'includes/header.php';
?>

<!-- SEO Settings Content -->
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">SEO Ayarları</h1>
                <p class="text-gray-600">Arama motoru optimizasyonu ve sosyal medya ayarlarını yönetin</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6">
                <button onclick="showTab('meta')" 
                        id="tab-meta" 
                        class="tab-button py-4 px-1 border-b-2 border-primary-500 font-medium text-sm text-primary-600">
                    <i class="fas fa-tags mr-2"></i>
                    Meta Ayarları
                </button>
                <button onclick="showTab('social')" 
                        id="tab-social" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-share-alt mr-2"></i>
                    Sosyal Medya
                </button>
                <button onclick="showTab('analytics')" 
                        id="tab-analytics" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-chart-line mr-2"></i>
                    Analytics
                </button>
                <button onclick="showTab('technical')" 
                        id="tab-technical" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-cogs mr-2"></i>
                    Teknik SEO
                </button>
            </nav>
        </div>

        <!-- Meta Settings Tab -->
        <div id="content-meta" class="tab-content p-6">
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
                                       value="<?= htmlspecialchars($meta_settings['default_title']['value'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <p class="text-sm text-gray-500 mt-1">Sayfa title'ının varsayılan değeri</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Title Ayırıcısı</label>
                                <input type="text" 
                                       name="title_separator" 
                                       value="<?= htmlspecialchars($meta_settings['title_separator']['value'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <p class="text-sm text-gray-500 mt-1">Sayfa title'ı ile site adı arasında kullanılacak ayırıcı (örn: " | ")</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Varsayılan Meta Description</label>
                                <textarea name="default_description" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($meta_settings['default_description']['value'] ?? '') ?></textarea>
                                <p class="text-sm text-gray-500 mt-1">Arama motorlarında görünecek site açıklaması (maksimum 160 karakter)</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Varsayılan Keywords</label>
                                <input type="text" 
                                       name="default_keywords" 
                                       value="<?= htmlspecialchars($meta_settings['default_keywords']['value'] ?? '') ?>"
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
                                       value="<?= htmlspecialchars($meta_settings['author']['value'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Robots Meta Tag</label>
                                <select name="robots" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="index, follow" <?= ($meta_settings['robots']['value'] ?? '') === 'index, follow' ? 'selected' : '' ?>>index, follow</option>
                                    <option value="noindex, follow" <?= ($meta_settings['robots']['value'] ?? '') === 'noindex, follow' ? 'selected' : '' ?>>noindex, follow</option>
                                    <option value="index, nofollow" <?= ($meta_settings['robots']['value'] ?? '') === 'index, nofollow' ? 'selected' : '' ?>>index, nofollow</option>
                                    <option value="noindex, nofollow" <?= ($meta_settings['robots']['value'] ?? '') === 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow</option>
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

        <!-- Social Media Settings Tab -->
        <div id="content-social" class="tab-content p-6 hidden">
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
                                           value="<?= htmlspecialchars($social_settings['og_site_name']['value'] ?? '') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Tipi</label>
                                    <select name="og_type" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        <option value="website" <?= ($social_settings['og_type']['value'] ?? '') === 'website' ? 'selected' : '' ?>>Website</option>
                                        <option value="blog" <?= ($social_settings['og_type']['value'] ?? '') === 'blog' ? 'selected' : '' ?>>Blog</option>
                                        <option value="article" <?= ($social_settings['og_type']['value'] ?? '') === 'article' ? 'selected' : '' ?>>Article</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Varsayılan Paylaşım Görseli</label>
                                <input type="text" 
                                       name="og_image" 
                                       value="<?= htmlspecialchars($social_settings['og_image']['value'] ?? '') ?>"
                                       placeholder="assets/images/og-image.jpg"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <p class="text-sm text-gray-500 mt-1">1200x630 piksel boyutunda olmalı</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Facebook App ID</label>
                                <input type="text" 
                                       name="facebook_app_id" 
                                       value="<?= htmlspecialchars($social_settings['facebook_app_id']['value'] ?? '') ?>"
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
                                    <option value="summary" <?= ($social_settings['twitter_card']['value'] ?? '') === 'summary' ? 'selected' : '' ?>>Summary</option>
                                    <option value="summary_large_image" <?= ($social_settings['twitter_card']['value'] ?? '') === 'summary_large_image' ? 'selected' : '' ?>>Summary Large Image</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Twitter Kullanıcı Adı</label>
                                <input type="text" 
                                       name="twitter_site" 
                                       value="<?= htmlspecialchars($social_settings['twitter_site']['value'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($social_settings['linkedin_company']['value'] ?? '') ?>"
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

        <!-- Analytics Settings Tab -->
        <div id="content-analytics" class="tab-content p-6 hidden">
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
                                       value="<?= htmlspecialchars($analytics_settings['google_analytics_id']['value'] ?? '') ?>"
                                       placeholder="G-XXXXXXXXXX"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Google Tag Manager ID</label>
                                <input type="text" 
                                       name="google_tag_manager_id" 
                                       value="<?= htmlspecialchars($analytics_settings['google_tag_manager_id']['value'] ?? '') ?>"
                                       placeholder="GTM-XXXXXXX"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Google Search Console Doğrulama</label>
                                <input type="text" 
                                       name="google_search_console" 
                                       value="<?= htmlspecialchars($analytics_settings['google_search_console']['value'] ?? '') ?>"
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
                                       value="<?= htmlspecialchars($analytics_settings['facebook_pixel_id']['value'] ?? '') ?>"
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
                                       value="<?= htmlspecialchars($analytics_settings['bing_webmaster']['value'] ?? '') ?>"
                                       placeholder="msvalidate.01=..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Yandex Doğrulama</label>
                                <input type="text" 
                                       name="yandex_verification" 
                                       value="<?= htmlspecialchars($analytics_settings['yandex_verification']['value'] ?? '') ?>"
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

        <!-- Technical SEO Settings Tab -->
        <div id="content-technical" class="tab-content p-6 hidden">
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
                                       <?= ($technical_settings['canonical_enabled']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>
                                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="font-medium text-gray-900">XML Sitemap</label>
                                    <p class="text-sm text-gray-600">Arama motorları için site haritası oluşturur</p>
                                </div>
                                <input type="checkbox" 
                                       name="sitemap_enabled" 
                                       <?= ($technical_settings['sitemap_enabled']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>
                                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="font-medium text-gray-900">Schema.org Markup</label>
                                    <p class="text-sm text-gray-600">Yapılandırılmış veri ekler</p>
                                </div>
                                <input type="checkbox" 
                                       name="schema_enabled" 
                                       <?= ($technical_settings['schema_enabled']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>
                                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="font-medium text-gray-900">Breadcrumbs</label>
                                    <p class="text-sm text-gray-600">Sayfa hiyerarşisi için breadcrumb navigasyonu</p>
                                </div>
                                <input type="checkbox" 
                                       name="breadcrumbs_enabled" 
                                       <?= ($technical_settings['breadcrumbs_enabled']['value'] ?? 'true') === 'true' ? 'checked' : '' ?>
                                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="font-medium text-gray-900">AMP (Accelerated Mobile Pages)</label>
                                    <p class="text-sm text-gray-600">Mobil cihazlarda hızlı yüklenen sayfalar</p>
                                </div>
                                <input type="checkbox" 
                                       name="amp_enabled" 
                                       <?= ($technical_settings['amp_enabled']['value'] ?? 'false') === 'true' ? 'checked' : '' ?>
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
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active styles from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-primary-500', 'text-primary-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active styles to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.remove('border-transparent', 'text-gray-500');
    activeButton.classList.add('border-primary-500', 'text-primary-600');
}

// Check URL hash to show correct tab
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.substring(1);
    if (['social', 'analytics', 'technical'].includes(hash)) {
        showTab(hash);
    }
});
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
