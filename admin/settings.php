<?php
/**
 * Admin Panel - Genel Ayarlar
 * Site genel ayarlarını yönetme sayfası
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';
require_once '../services/SettingsService.php';

$settingsService = new SettingsService();

// Sayfa bilgileri
$page_title = 'Genel Ayarlar';
$breadcrumb_items = [
    ['title' => 'Ayarlar', 'url' => '#', 'icon' => 'fas fa-cog'],
    ['title' => 'Genel Ayarlar', 'url' => 'settings.php', 'icon' => 'fas fa-sliders-h']
];

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        
        // Site genel bilgileri güncelleme
        if (isset($_POST['action']) && $_POST['action'] === 'update_general_settings') {
            $general_settings = [
                'site_name' => $_POST['site_name'] ?? '',
                'site_tagline' => $_POST['site_tagline'] ?? '',
                'site_description' => $_POST['site_description'] ?? '',
                'site_logo' => $_POST['site_logo'] ?? '',
                'site_favicon' => $_POST['site_favicon'] ?? '',
                'primary_color' => $_POST['primary_color'] ?? '',
                'secondary_color' => $_POST['secondary_color'] ?? '',
                'footer_copyright' => $_POST['footer_copyright'] ?? ''
            ];
            
            if ($settingsService->updateMultipleSettings($general_settings, 'general')) {
                set_flash_message('success', 'Site genel bilgileri başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'Site genel bilgileri güncellenirken bir hata oluştu.');
            }
            header('Location: settings.php');
            exit;
        }
        
        // Teknik ayarlar güncelleme
        if (isset($_POST['action']) && $_POST['action'] === 'update_technical_settings') {
            $technical_settings = [
                'products_per_page' => $_POST['products_per_page'] ?? '',
                'blogs_per_page' => $_POST['blogs_per_page'] ?? '',
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 'true' : 'false',
                'site_language' => $_POST['site_language'] ?? '',
                'timezone' => $_POST['timezone'] ?? '',
                'comments_enabled' => isset($_POST['comments_enabled']) ? 'true' : 'false'
            ];
            
            if ($settingsService->updateMultipleSettings($technical_settings, 'technical')) {
                set_flash_message('success', 'Teknik ayarlar başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'Teknik ayarlar güncellenirken bir hata oluştu.');
            }
            header('Location: settings.php#technical');
            exit;
        }
    }
}

// Mevcut ayarları getir
$general_settings = $settingsService->getSettingsByGroup('general');
$technical_settings = $settingsService->getSettingsByGroup('technical');

// Varsayılan değerlerle birleştir
$defaults = $settingsService->getDefaultSiteSettings();
$general_settings = array_merge($defaults, $general_settings);
$technical_settings = array_merge($defaults, $technical_settings);

// Gerekli CSS ve JS
$additional_css = [];
$additional_js = [];

// Header dahil et
include 'includes/header.php';
?>

<!-- Settings Content -->
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Genel Ayarlar</h1>
                <p class="text-gray-600">Web sitesinin genel ayarlarını yönetin</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6">
                <button onclick="showTab('general')" 
                        id="tab-general" 
                        class="tab-button py-4 px-1 border-b-2 border-primary-500 font-medium text-sm text-primary-600">
                    <i class="fas fa-globe mr-2"></i>
                    Site Bilgileri
                </button>
                <button onclick="showTab('technical')" 
                        id="tab-technical" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-cogs mr-2"></i>
                    Teknik Ayarlar
                </button>
            </nav>
        </div>

        <!-- General Settings Tab -->
        <div id="content-general" class="tab-content p-6">
            <form method="POST">
                <input type="hidden" name="action" value="update_general_settings">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="space-y-8">
                    
                    <!-- Site Identity -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Site Kimliği</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Adı</label>
                                <input type="text" 
                                       name="site_name" 
                                       value="<?= htmlspecialchars($general_settings['site_name'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Sloganı</label>
                                <input type="text" 
                                       name="site_tagline" 
                                       value="<?= htmlspecialchars($general_settings['site_tagline'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Açıklaması</label>
                                <textarea name="site_description" 
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($general_settings['site_description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Identity -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Görsel Kimlik</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Logosu URL</label>
                                <input type="text" 
                                       name="site_logo" 
                                       value="<?= htmlspecialchars($general_settings['site_logo'] ?? '') ?>"
                                       placeholder="assets/images/logo.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Favicon URL</label>
                                <input type="text" 
                                       name="site_favicon" 
                                       value="<?= htmlspecialchars($general_settings['site_favicon'] ?? '') ?>"
                                       placeholder="assets/images/favicon.ico"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ana Renk</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" 
                                           name="primary_color" 
                                           value="<?= htmlspecialchars($general_settings['primary_color'] ?? '#e91e63') ?>"
                                           class="w-12 h-10 border border-gray-300 rounded-lg">
                                    <input type="text" 
                                           name="primary_color_text" 
                                           value="<?= htmlspecialchars($general_settings['primary_color'] ?? '#e91e63') ?>"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">İkincil Renk</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" 
                                           name="secondary_color" 
                                           value="<?= htmlspecialchars($general_settings['secondary_color'] ?? '#2c2c54') ?>"
                                           class="w-12 h-10 border border-gray-300 rounded-lg">
                                    <input type="text" 
                                           name="secondary_color_text" 
                                           value="<?= htmlspecialchars($general_settings['secondary_color'] ?? '#2c2c54') ?>"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Footer</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telif Hakkı Metni</label>
                            <input type="text" 
                                   name="footer_copyright" 
                                   value="<?= htmlspecialchars($general_settings['footer_copyright'] ?? '') ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Site Bilgilerini Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Technical Settings Tab -->
        <div id="content-technical" class="tab-content p-6 hidden">
            <form method="POST">
                <input type="hidden" name="action" value="update_technical_settings">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="space-y-8">
                    
                    <!-- Pagination -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sayfalama Ayarları</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sayfa Başına Ürün Sayısı</label>
                                <input type="number" 
                                       name="products_per_page" 
                                       value="<?= htmlspecialchars($technical_settings['products_per_page'] ?? '12') ?>"
                                       min="1" max="100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sayfa Başına Blog Sayısı</label>
                                <input type="number" 
                                       name="blogs_per_page" 
                                       value="<?= htmlspecialchars($technical_settings['blogs_per_page'] ?? '10') ?>"
                                       min="1" max="50"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sistem Ayarları</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="font-medium text-gray-900">Bakım Modu</label>
                                    <p class="text-sm text-gray-600">Site ziyaretçilere kapalı olur</p>
                                </div>
                                <input type="checkbox" 
                                       name="maintenance_mode" 
                                       <?= ($technical_settings['maintenance_mode'] ?? 'false') === 'true' ? 'checked' : '' ?>
                                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="font-medium text-gray-900">Yorum Sistemi</label>
                                    <p class="text-sm text-gray-600">Blog yazılarında yorum yapılabilsin</p>
                                </div>
                                <input type="checkbox" 
                                       name="comments_enabled" 
                                       <?= ($technical_settings['comments_enabled'] ?? 'true') === 'true' ? 'checked' : '' ?>
                                       class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            </div>
                        </div>
                    </div>

                    <!-- Localization -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Yerelleştirme</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Dili</label>
                                <select name="site_language" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="tr" <?= ($technical_settings['site_language'] ?? 'tr') === 'tr' ? 'selected' : '' ?>>Türkçe</option>
                                    <option value="en" <?= ($technical_settings['site_language'] ?? 'tr') === 'en' ? 'selected' : '' ?>>English</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Zaman Dilimi</label>
                                <select name="timezone" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="Europe/Istanbul" <?= ($technical_settings['timezone'] ?? 'Europe/Istanbul') === 'Europe/Istanbul' ? 'selected' : '' ?>>Europe/Istanbul</option>
                                    <option value="America/New_York" <?= ($technical_settings['timezone'] ?? 'Europe/Istanbul') === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                                    <option value="Europe/London" <?= ($technical_settings['timezone'] ?? 'Europe/Istanbul') === 'Europe/London' ? 'selected' : '' ?>>Europe/London</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Teknik Ayarları Kaydet
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

// Color picker sync
document.addEventListener('DOMContentLoaded', function() {
    // Primary color sync
    const primaryColorPicker = document.querySelector('input[name="primary_color"]');
    const primaryColorText = document.querySelector('input[name="primary_color_text"]');
    
    if (primaryColorPicker && primaryColorText) {
        primaryColorPicker.addEventListener('change', function() {
            primaryColorText.value = this.value;
        });
        
        primaryColorText.addEventListener('change', function() {
            primaryColorPicker.value = this.value;
        });
    }
    
    // Secondary color sync
    const secondaryColorPicker = document.querySelector('input[name="secondary_color"]');
    const secondaryColorText = document.querySelector('input[name="secondary_color_text"]');
    
    if (secondaryColorPicker && secondaryColorText) {
        secondaryColorPicker.addEventListener('change', function() {
            secondaryColorText.value = this.value;
        });
        
        secondaryColorText.addEventListener('change', function() {
            secondaryColorPicker.value = this.value;
        });
    }
    
    // Check URL hash to show correct tab
    const hash = window.location.hash.substring(1);
    if (hash === 'technical') {
        showTab(hash);
    }
});
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
