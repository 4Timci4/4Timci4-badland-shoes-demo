<?php


require_once 'config/auth.php';
check_admin_auth();


require_once '../config/database.php';
require_once '../services/ContactService.php';

$contactService = new ContactService();


$page_title = 'İletişim Bilgileri';
$breadcrumb_items = [
    ['title' => 'İletişim', 'url' => '#', 'icon' => 'fas fa-envelope'],
    ['title' => 'İletişim Bilgileri', 'url' => 'contact-settings.php', 'icon' => 'fas fa-cog']
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {


        if (isset($_POST['action']) && $_POST['action'] === 'update_footer_info') {
            $footer_data = [
                'footer' => [
                    'site_title' => $_POST['site_title'] ?? '',
                    'site_description' => $_POST['site_description'] ?? ''
                ],
                'links' => [
                    'home_text' => $_POST['home_text'] ?? '',
                    'home_url' => $_POST['home_url'] ?? '',
                    'products_text' => $_POST['products_text'] ?? '',
                    'products_url' => $_POST['products_url'] ?? '',
                    'about_text' => $_POST['about_text'] ?? '',
                    'about_url' => $_POST['about_url'] ?? '',
                    'blog_text' => $_POST['blog_text'] ?? '',
                    'blog_url' => $_POST['blog_url'] ?? '',
                    'contact_text' => $_POST['contact_text'] ?? '',
                    'contact_url' => $_POST['contact_url'] ?? ''
                ]
            ];

            if ($contactService->updateFooterInfo($footer_data)) {
                set_flash_message('success', 'Footer bilgileri başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'Footer bilgileri güncellenirken bir hata oluştu.');
            }
            header('Location: contact-settings.php#footer');
            exit;
        }


        if (isset($_POST['action']) && $_POST['action'] === 'update_contact_info') {
            $contact_data = [
                'banner' => [
                    'title' => $_POST['banner_title'] ?? '',
                    'subtitle' => $_POST['banner_subtitle'] ?? ''
                ],
                'contact' => [
                    'title' => $_POST['contact_title'] ?? '',
                    'description' => $_POST['contact_description'] ?? '',
                    'address' => $_POST['address'] ?? '',
                    'phone1' => $_POST['phone1'] ?? '',
                    'phone2' => $_POST['phone2'] ?? '',
                    'email1' => $_POST['email1'] ?? '',
                    'email2' => $_POST['email2'] ?? '',
                    'working_hours1' => $_POST['working_hours1'] ?? '',
                    'working_hours2' => $_POST['working_hours2'] ?? ''
                ],
                'form' => [
                    'title' => $_POST['form_title'] ?? '',
                    'success_title' => $_POST['success_title'] ?? '',
                    'success_message' => $_POST['success_message'] ?? '',
                    'success_button' => $_POST['success_button'] ?? ''
                ],
                'map' => [
                    'title' => $_POST['map_title'] ?? '',
                    'embed_code' => $_POST['map_embed_code'] ?? ''
                ]
            ];

            if ($contactService->updateContactInfo($contact_data)) {
                set_flash_message('success', 'İletişim bilgileri başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'İletişim bilgileri güncellenirken bir hata oluştu.');
            }
            header('Location: contact-settings.php');
            exit;
        }


        if (isset($_POST['action']) && $_POST['action'] === 'add_social_link') {
            $link_data = [
                'platform' => $_POST['platform'] ?? '',
                'url' => $_POST['url'] ?? '',
                'icon_class' => $_POST['icon_class'] ?? '',
                'order_index' => intval($_POST['order_index'] ?? 0),
                'is_active' => isset($_POST['is_active'])
            ];

            if ($contactService->addSocialMediaLink($link_data)) {
                set_flash_message('success', 'Sosyal medya linki başarıyla eklendi.');
            } else {
                set_flash_message('error', 'Sosyal medya linki eklenirken bir hata oluştu.');
            }
            header('Location: contact-settings.php#social-media');
            exit;
        }


        if (isset($_POST['action']) && $_POST['action'] === 'update_social_link') {
            $link_id = intval($_POST['link_id']);
            $link_data = [
                'platform' => $_POST['platform'] ?? '',
                'url' => $_POST['url'] ?? '',
                'icon_class' => $_POST['icon_class'] ?? '',
                'order_index' => intval($_POST['order_index'] ?? 0),
                'is_active' => isset($_POST['is_active'])
            ];

            if ($contactService->updateSocialMediaLink($link_id, $link_data)) {
                set_flash_message('success', 'Sosyal medya linki başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'Sosyal medya linki güncellenirken bir hata oluştu.');
            }
            header('Location: contact-settings.php#social-media');
            exit;
        }


        if (isset($_POST['action']) && $_POST['action'] === 'delete_social_link') {
            $link_id = intval($_POST['link_id']);
            if ($contactService->deleteSocialMediaLink($link_id)) {
                set_flash_message('success', 'Sosyal medya linki başarıyla silindi.');
            } else {
                set_flash_message('error', 'Sosyal medya linki silinirken bir hata oluştu.');
            }
            header('Location: contact-settings.php#social-media');
            exit;
        }
    }
}


$contact_info = $contactService->getContactInfo();
$social_links = $contactService->getSocialMediaLinks(false);
$footer_info = $contactService->getFooterInfo();


$additional_css = [];
$additional_js = [];


include 'includes/header.php';
?>

<!-- Contact Settings Content -->
<div class="space-y-6">

    <!-- Page Header -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">İletişim Bilgileri</h1>
                <p class="text-gray-600">Web sitesindeki iletişim sayfası bilgilerini yönetin</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex flex-col sm:flex-row sm:space-x-8 px-4 sm:px-6">
                <button onclick="showTab('contact-info')" id="tab-contact-info"
                    class="tab-button py-3 sm:py-4 px-1 border-b-2 border-primary-500 font-medium text-sm text-primary-600 text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    İletişim Bilgileri
                </button>
                <button onclick="showTab('social-media')" id="tab-social-media"
                    class="tab-button py-3 sm:py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 text-center">
                    <i class="fas fa-share-alt mr-2"></i>
                    Sosyal Medya
                </button>
                <button onclick="showTab('footer')" id="tab-footer"
                    class="tab-button py-3 sm:py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 text-center">
                    <i class="fas fa-columns mr-2"></i>
                    Footer Ayarları
                </button>
            </nav>
        </div>

        <!-- Contact Info Tab -->
        <div id="content-contact-info" class="tab-content p-6">
            <form method="POST">
                <input type="hidden" name="action" value="update_contact_info">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="space-y-8">

                    <!-- Banner Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Sayfa Başlığı</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ana Başlık</label>
                                <input type="text" name="banner_title"
                                    value="<?= htmlspecialchars($contact_info['banner']['title'] ?? '') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alt Başlık</label>
                                <input type="text" name="banner_subtitle"
                                    value="<?= htmlspecialchars($contact_info['banner']['subtitle'] ?? '') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">İletişim Detayları</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bölüm Başlığı</label>
                                    <input type="text" name="contact_title"
                                        value="<?= htmlspecialchars($contact_info['contact']['title'] ?? '') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                                <textarea name="contact_description" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($contact_info['contact']['description'] ?? '') ?></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Adres</label>
                                    <textarea name="address" rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($contact_info['contact']['address'] ?? '') ?></textarea>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Telefon 1</label>
                                        <input type="text" name="phone1"
                                            value="<?= htmlspecialchars($contact_info['contact']['phone1'] ?? '') ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Telefon 2</label>
                                        <input type="text" name="phone2"
                                            value="<?= htmlspecialchars($contact_info['contact']['phone2'] ?? '') ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">E-posta 1</label>
                                    <input type="email" name="email1"
                                        value="<?= htmlspecialchars($contact_info['contact']['email1'] ?? '') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">E-posta 2</label>
                                    <input type="email" name="email2"
                                        value="<?= htmlspecialchars($contact_info['contact']['email2'] ?? '') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Çalışma Saatleri
                                        1</label>
                                    <input type="text" name="working_hours1"
                                        value="<?= htmlspecialchars($contact_info['contact']['working_hours1'] ?? '') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Çalışma Saatleri
                                        2</label>
                                    <input type="text" name="working_hours2"
                                        value="<?= htmlspecialchars($contact_info['contact']['working_hours2'] ?? '') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">İletişim Formu</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Form Başlığı</label>
                                <input type="text" name="form_title"
                                    value="<?= htmlspecialchars($contact_info['form']['title'] ?? '') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Başarı Başlığı</label>
                                <input type="text" name="success_title"
                                    value="<?= htmlspecialchars($contact_info['form']['success_title'] ?? '') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Başarı Mesajı</label>
                                <textarea name="success_message" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($contact_info['form']['success_message'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Başarı Buton Metni</label>
                                <input type="text" name="success_button"
                                    value="<?= htmlspecialchars($contact_info['form']['success_button'] ?? '') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Harita</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harita Başlığı</label>
                                <input type="text" name="map_title"
                                    value="<?= htmlspecialchars($contact_info['map']['title'] ?? '') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harita Embed Kodu (Google
                                    Maps)</label>
                                <textarea name="map_embed_code" rows="3"
                                          placeholder="https://www.google.com/maps/embed?..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($contact_info['map']['embed_code'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit"
                        class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Bilgileri Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Social Media Tab -->
        <div id="content-social-media" class="tab-content p-6 hidden">

            <!-- Add New Social Link -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Yeni Sosyal Medya Linki Ekle</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_social_link">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Platform Adı</label>
                            <input type="text" name="platform" placeholder="Facebook, Instagram, Twitter..." required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                            <input type="url" name="url" placeholder="https://..."
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icon Class</label>
                            <input type="text" name="icon_class" placeholder="fab fa-facebook-f" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sıra</label>
                            <input type="number" name="order_index" value="<?= count($social_links) + 1 ?>" min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" checked
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label class="ml-2 block text-sm text-gray-900">Aktif</label>
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Ekle
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Existing Social Links -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">Mevcut Sosyal Medya Linkleri</h3>

                <?php if (!empty($social_links)): ?>
                    <?php foreach ($social_links as $link): ?>
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <form method="POST" class="flex items-center justify-between">
                                <input type="hidden" name="action" value="update_social_link">
                                <input type="hidden" name="link_id" value="<?= $link['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 flex-1">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="<?= htmlspecialchars($link['icon_class']) ?> text-gray-600"></i>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 flex-1 w-full">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Platform</label>
                                            <input type="text" name="platform"
                                                value="<?= htmlspecialchars($link['platform']) ?>"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">URL</label>
                                            <input type="url" name="url" value="<?= htmlspecialchars($link['url']) ?>"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Icon Class</label>
                                            <input type="text" name="icon_class"
                                                value="<?= htmlspecialchars($link['icon_class']) ?>"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                        </div>
                                        <div class="flex items-end space-x-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Sıra</label>
                                                <input type="number" name="order_index" value="<?= $link['order_index'] ?>" min="1"
                                                    class="w-20 px-2 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                            </div>
                                            <div class="flex items-center pb-2">
                                                <input type="checkbox" name="is_active" <?= $link['is_active'] ? 'checked' : '' ?>
                                                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                                <label class="ml-2 text-sm">Aktif</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 ml-4">
                                    <button type="submit"
                                        class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm transition-colors">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button type="button"
                                        onclick="confirmDelete(<?= $link['id'] ?>, '<?= htmlspecialchars($link['platform']) ?>')"
                                        class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-share-alt text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz sosyal medya linki yok</h3>
                        <p class="text-gray-600">Yukarıdaki formu kullanarak yeni sosyal medya linki ekleyin.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Settings Tab -->
        <div id="content-footer" class="tab-content p-6 hidden">
            <form method="POST">
                <input type="hidden" name="action" value="update_footer_info">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                <div class="space-y-8">

                    <!-- Site Identity -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Site Kimliği</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Site Başlığı</label>
                                <input type="text" name="site_title"
                                    value="<?= htmlspecialchars($footer_info['footer']['site_title'] ?? '') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Site Açıklaması</label>
                            <textarea name="site_description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"><?= htmlspecialchars($footer_info['footer']['site_description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Hızlı Erişim Linkleri</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ana Sayfa Metni</label>
                                    <input type="text" name="home_text"
                                        value="<?= htmlspecialchars($footer_info['links']['home_text'] ?? 'Ana Sayfa') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ana Sayfa URL</label>
                                    <input type="text" name="home_url"
                                        value="<?= htmlspecialchars($footer_info['links']['home_url'] ?? '/index.php') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ürünler Metni</label>
                                    <input type="text" name="products_text"
                                        value="<?= htmlspecialchars($footer_info['links']['products_text'] ?? 'Ürünler') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ürünler URL</label>
                                    <input type="text" name="products_url"
                                        value="<?= htmlspecialchars($footer_info['links']['products_url'] ?? '/products.php') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Hakkımızda Metni</label>
                                    <input type="text" name="about_text"
                                        value="<?= htmlspecialchars($footer_info['links']['about_text'] ?? 'Hakkımızda') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Hakkımızda URL</label>
                                    <input type="text" name="about_url"
                                        value="<?= htmlspecialchars($footer_info['links']['about_url'] ?? '/about.php') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Blog Metni</label>
                                    <input type="text" name="blog_text"
                                        value="<?= htmlspecialchars($footer_info['links']['blog_text'] ?? 'Blog') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Blog URL</label>
                                    <input type="text" name="blog_url"
                                        value="<?= htmlspecialchars($footer_info['links']['blog_url'] ?? '/blog.php') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">İletişim Metni</label>
                                    <input type="text" name="contact_text"
                                        value="<?= htmlspecialchars($footer_info['links']['contact_text'] ?? 'İletişim') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">İletişim URL</label>
                                    <input type="text" name="contact_url"
                                        value="<?= htmlspecialchars($footer_info['links']['contact_url'] ?? '/contact.php') ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Note -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">İletişim Bilgileri</h3>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-medium text-blue-900 mb-2">Önemli Not</h4>
                                    <p class="text-sm text-blue-800">
                                        Footer'daki iletişim bilgileri (adres, telefon, e-posta) "İletişim Bilgileri"
                                        sekmesindeki verilerle otomatik olarak senkronize edilir.
                                        Bu bilgileri değiştirmek için "İletişim Bilgileri" sekmesini kullanın.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <button type="submit"
                        class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Footer Ayarlarını Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Social Link Form -->
<form id="deleteSocialForm" method="POST" class="hidden">
    <input type="hidden" name="action" value="delete_social_link">
    <input type="hidden" name="link_id" id="deleteLinkId">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
</form>

<script>
    function showTab(tabName) {

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });


        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('border-primary-500', 'text-primary-600');
            button.classList.add('border-transparent', 'text-gray-500');
        });


        document.getElementById('content-' + tabName).classList.remove('hidden');


        const activeButton = document.getElementById('tab-' + tabName);
        activeButton.classList.remove('border-transparent', 'text-gray-500');
        activeButton.classList.add('border-primary-500', 'text-primary-600');
    }

    function confirmDelete(linkId, platform) {
        if (confirm(`"${platform}" sosyal medya linkini silmek istediğinize emin misiniz?\n\nBu işlem geri alınamaz.`)) {
            const form = document.getElementById('deleteSocialForm');
            form.querySelector('#deleteLinkId').value = linkId;
            form.submit();
        }
    }


    document.addEventListener('DOMContentLoaded', function () {
        const hash = window.location.hash.substring(1);
        if (hash === 'social-media') {
            showTab('social-media');
        }
    });
</script>

<?php

include 'includes/footer.php';
?>