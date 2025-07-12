<?php


require_once 'config/auth.php';
check_admin_auth();


require_once '../config/database.php';
require_once '../services/AboutService.php';


$page_title = 'Hakkımızda Yönetimi';
$breadcrumb_items = [
    ['title' => 'Hakkımızda Yönetimi', 'url' => '#', 'icon' => 'fas fa-info-circle']
];


if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        $action = $_POST['action'] ?? '';
        $aboutService = new AboutService();

        switch ($action) {
            case 'update_settings':

                $settings_to_update = [
                    'story_title' => trim($_POST['story_title'] ?? ''),
                    'story_subtitle' => trim($_POST['story_subtitle'] ?? ''),
                    'story_content_title' => trim($_POST['story_content_title'] ?? ''),
                    'story_content_p1' => trim($_POST['story_content_p1'] ?? ''),
                    'story_content_p2' => trim($_POST['story_content_p2'] ?? ''),
                    'story_content_p3' => trim($_POST['story_content_p3'] ?? ''),
                    'story_image_url' => trim($_POST['story_image_url'] ?? ''),
                    'story_content_homepage' => trim($_POST['story_content_homepage'] ?? ''),
                    'values_title' => trim($_POST['values_title'] ?? ''),
                    'values_subtitle' => trim($_POST['values_subtitle'] ?? ''),
                    'team_title' => trim($_POST['team_title'] ?? ''),
                    'team_subtitle' => trim($_POST['team_subtitle'] ?? '')
                ];

                if ($aboutService->updateMultipleSettings($settings_to_update)) {
                    set_flash_message('success', 'Ayarlar başarıyla güncellendi.');
                } else {
                    set_flash_message('error', 'Ayarlar güncellenirken bir hata oluştu.');
                }
                break;

            case 'delete_content_block':
                $block_id = intval($_POST['block_id'] ?? 0);

                if ($block_id > 0) {
                    if ($aboutService->deleteContentBlock($block_id)) {
                        set_flash_message('success', 'İçerik başarıyla silindi.');
                    } else {
                        set_flash_message('error', 'İçerik silinirken bir hata oluştu.');
                    }
                } else {
                    set_flash_message('error', 'Geçersiz içerik ID.');
                }
                break;

            case 'update_content_order':
                $section = $_POST['section'] ?? '';
                $order_data = json_decode($_POST['order_data'] ?? '[]', true);

                if (!empty($order_data) && !empty($section)) {
                    if ($aboutService->updateContentBlockOrder($section, $order_data)) {
                        set_flash_message('success', 'Sıralama güncellendi.');
                    } else {
                        set_flash_message('error', 'Sıralama güncellenirken bir hata oluştu.');
                    }
                }
                break;
        }


        header('Location: about.php?tab=' . ($_POST['current_tab'] ?? 'general'));
        exit;
    }
}


try {
    $aboutService = new AboutService();
    $aboutData = $aboutService->getAboutPageContent();
    $stats = $aboutService->getAboutStats();

    $settings = $aboutData['settings'];
    $values = $aboutData['values'];
    $team = $aboutData['team'];
} catch (Exception $e) {
    $settings = [];
    $values = [];
    $team = [];
    $stats = ['total_settings' => 0, 'total_values' => 0, 'total_team' => 0, 'last_updated' => null];
    set_flash_message('error', 'Hakkımızda verileri yüklenirken bir hata oluştu: ' . $e->getMessage());
}


$active_tab = $_GET['tab'] ?? 'general';


include 'includes/header.php';
?>

<!-- About Management Content -->
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Hakkımızda Yönetimi</h1>
            <p class="text-gray-600">Hakkımızda sayfasının tüm içeriğini yönetin</p>
        </div>
        <div class="mt-4 lg:mt-0 flex space-x-3">
            <a href="../about.php" target="_blank"
                class="inline-flex items-center px-6 py-3 bg-green-100 text-green-700 font-semibold rounded-xl hover:bg-green-200 transition-colors">
                <i class="fas fa-external-link-alt mr-2"></i>
                Hakkımızda Sayfasını Gör
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php
    $flash_message = get_flash_message();
    if ($flash_message):
        $bg_color = $flash_message['type'] === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
        $text_color = $flash_message['type'] === 'success' ? 'text-green-800' : 'text-red-800';
        $icon = $flash_message['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        $icon_color = $flash_message['type'] === 'success' ? 'text-green-500' : 'text-red-500';
        ?>
        <div class="<?= $bg_color ?> border rounded-xl p-4 flex items-center">
            <i class="fas <?= $icon ?> <?= $icon_color ?> mr-3"></i>
            <span class="<?= $text_color ?> font-medium"><?= htmlspecialchars($flash_message['message']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-xl">
                    <i class="fas fa-cog text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Genel Ayarlar</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total_settings'] ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-xl">
                    <i class="fas fa-star text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Değerlerimiz</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total_values'] ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-xl">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Ekip Üyeleri</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total_team'] ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-xl">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Son Güncelleme</p>
                    <p class="text-sm font-bold text-gray-900">
                        <?= $stats['last_updated'] ? date('d M Y', strtotime($stats['last_updated'])) : 'Henüz yok' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100">
            <nav class="flex space-x-0">
                <button
                    class="tab-button px-6 py-4 text-sm font-semibold border-b-2 transition-colors <?= $active_tab === 'general' ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' ?>"
                    data-tab="general">
                    <i class="fas fa-cog mr-2"></i>
                    Genel Ayarlar
                </button>
                <button
                    class="tab-button px-6 py-4 text-sm font-semibold border-b-2 transition-colors <?= $active_tab === 'values' ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' ?>"
                    data-tab="values">
                    <i class="fas fa-star mr-2"></i>
                    Değerlerimiz
                </button>
                <button
                    class="tab-button px-6 py-4 text-sm font-semibold border-b-2 transition-colors <?= $active_tab === 'team' ? 'border-primary-500 text-primary-600 bg-primary-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' ?>"
                    data-tab="team">
                    <i class="fas fa-users mr-2"></i>
                    Ekibimiz
                </button>
            </nav>
        </div>

        <!-- Tab Contents -->
        <div class="p-6">

            <!-- General Settings Tab -->
            <div id="general-tab" class="tab-content <?= $active_tab !== 'general' ? 'hidden' : '' ?>">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="update_settings">
                    <input type="hidden" name="current_tab" value="general">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- Story Section -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2">Hikaye Bölümü</h3>

                            <div>
                                <label for="story_title" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-heading mr-2"></i>Ana Başlık
                                </label>
                                <input type="text" id="story_title" name="story_title"
                                    value="<?= htmlspecialchars($settings['story_title'] ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <div>
                                <label for="story_subtitle" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-text-height mr-2"></i>Alt Başlık
                                </label>
                                <input type="text" id="story_subtitle" name="story_subtitle"
                                    value="<?= htmlspecialchars($settings['story_subtitle'] ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <div>
                                <label for="story_content_title" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-quote-left mr-2"></i>İçerik Başlığı
                                </label>
                                <input type="text" id="story_content_title" name="story_content_title"
                                    value="<?= htmlspecialchars($settings['story_content_title'] ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <div>
                                <label for="story_image_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-image mr-2"></i>Hikaye Resmi URL'si
                                </label>
                                <input type="url" id="story_image_url" name="story_image_url"
                                    value="<?= htmlspecialchars($settings['story_image_url'] ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>
                        </div>

                        <!-- Values and Team Sections -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2">Bölüm Başlıkları
                            </h3>

                            <div>
                                <label for="values_title" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-star mr-2"></i>Değerlerimiz Başlığı
                                </label>
                                <input type="text" id="values_title" name="values_title"
                                    value="<?= htmlspecialchars($settings['values_title'] ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <div>
                                <label for="values_subtitle" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-text-height mr-2"></i>Değerlerimiz Alt Başlığı
                                </label>
                                <input type="text" id="values_subtitle" name="values_subtitle"
                                    value="<?= htmlspecialchars($settings['values_subtitle'] ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <div>
                                <label for="team_title" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-users mr-2"></i>Ekibimiz Başlığı
                                </label>
                                <input type="text" id="team_title" name="team_title"
                                    value="<?= htmlspecialchars($settings['team_title'] ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <div>
                                <label for="team_subtitle" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-text-height mr-2"></i>Ekibimiz Alt Başlığı
                                </label>
                                <input type="text" id="team_subtitle" name="team_subtitle"
                                    value="<?= htmlspecialchars($settings['team_subtitle'] ?? '') ?>"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Story Content -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2">Hikaye İçeriği</h3>

                        <div>
                            <label for="story_content_p1" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-paragraph mr-2"></i>1. Paragraf
                            </label>
                            <textarea id="story_content_p1" name="story_content_p1" rows="3"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($settings['story_content_p1'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label for="story_content_p2" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-paragraph mr-2"></i>2. Paragraf
                            </label>
                            <textarea id="story_content_p2" name="story_content_p2" rows="3"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($settings['story_content_p2'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label for="story_content_p3" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-paragraph mr-2"></i>3. Paragraf
                            </label>
                            <textarea id="story_content_p3" name="story_content_p3" rows="3"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($settings['story_content_p3'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label for="story_content_homepage" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-home mr-2"></i>Ana Sayfa İçin Özel Metin
                            </label>
                            <textarea id="story_content_homepage" name="story_content_homepage" rows="3"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($settings['story_content_homepage'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Bu metin ana sayfada görüntülenir</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Ayarları Kaydet
                        </button>
                    </div>
                </form>
            </div>

            <!-- Values Tab -->
            <div id="values-tab" class="tab-content <?= $active_tab !== 'values' ? 'hidden' : '' ?>">
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Değerlerimiz</h3>
                        <a href="about-content-add.php?section=values"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Yeni Değer Ekle
                        </a>
                    </div>

                    <?php if (!empty($values)): ?>
                        <div id="values-list" class="space-y-4">
                            <?php foreach ($values as $value): ?>
                                <div class="content-block-item bg-gray-50 border border-gray-200 rounded-xl p-4 hover:bg-gray-100 transition-colors cursor-move"
                                    data-block-id="<?= $value['id'] ?>">
                                    <div class="flex items-center space-x-4">
                                        <!-- Drag Handle -->
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-grip-vertical text-gray-400 cursor-move"></i>
                                        </div>

                                        <!-- Icon -->
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                                <i class="<?= htmlspecialchars($value['icon']) ?> text-primary-600"></i>
                                            </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-900 mb-1"><?= htmlspecialchars($value['title']) ?>
                                            </h4>
                                            <p class="text-gray-600 text-sm line-clamp-2">
                                                <?= htmlspecialchars($value['content']) ?></p>
                                            <p class="text-xs text-gray-400 mt-1">Sıra: <?= $value['sort_order'] ?></p>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex-shrink-0 flex space-x-2">
                                            <a href="about-content-edit.php?id=<?= $value['id'] ?>"
                                                class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm">
                                                <i class="fas fa-edit mr-1"></i>
                                                Düzenle
                                            </a>

                                            <form method="POST" class="inline-block"
                                                onsubmit="return confirm('Bu değeri silmek istediğinizden emin misiniz?')">
                                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                <input type="hidden" name="action" value="delete_content_block">
                                                <input type="hidden" name="block_id" value="<?= $value['id'] ?>">
                                                <input type="hidden" name="current_tab" value="values">
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Sil
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="text-center py-16">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-star text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Henüz değer eklenmemiş</h3>
                            <p class="text-gray-600 mb-6">İlk değerinizi oluşturarak başlayın</p>
                            <a href="about-content-add.php?section=values"
                                class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                İlk Değeri Ekle
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Team Tab -->
            <div id="team-tab" class="tab-content <?= $active_tab !== 'team' ? 'hidden' : '' ?>">
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Ekip Üyeleri</h3>
                        <a href="about-content-add.php?section=team"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>
                            Yeni Ekip Üyesi Ekle
                        </a>
                    </div>

                    <?php if (!empty($team)): ?>
                        <div id="team-list" class="space-y-4">
                            <?php foreach ($team as $member): ?>
                                <div class="content-block-item bg-gray-50 border border-gray-200 rounded-xl p-4 hover:bg-gray-100 transition-colors cursor-move"
                                    data-block-id="<?= $member['id'] ?>">
                                    <div class="flex items-center space-x-4">
                                        <!-- Drag Handle -->
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-grip-vertical text-gray-400 cursor-move"></i>
                                        </div>

                                        <!-- Photo -->
                                        <div class="flex-shrink-0">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full overflow-hidden">
                                                <?php if (!empty($member['image_url'])): ?>
                                                    <img src="<?= htmlspecialchars($member['image_url']) ?>"
                                                        alt="<?= htmlspecialchars($member['title']) ?>"
                                                        class="w-full h-full object-cover" onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-400"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-900 mb-1"><?= htmlspecialchars($member['title']) ?>
                                            </h4>
                                            <p class="text-primary-600 font-semibold text-sm mb-1">
                                                <?= htmlspecialchars($member['subtitle']) ?></p>
                                            <p class="text-gray-600 text-sm line-clamp-2">
                                                <?= htmlspecialchars($member['content']) ?></p>
                                            <p class="text-xs text-gray-400 mt-1">Sıra: <?= $member['sort_order'] ?></p>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex-shrink-0 flex space-x-2">
                                            <a href="about-content-edit.php?id=<?= $member['id'] ?>"
                                                class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm">
                                                <i class="fas fa-edit mr-1"></i>
                                                Düzenle
                                            </a>

                                            <form method="POST" class="inline-block"
                                                onsubmit="return confirm('Bu ekip üyesini silmek istediğinizden emin misiniz?')">
                                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                <input type="hidden" name="action" value="delete_content_block">
                                                <input type="hidden" name="block_id" value="<?= $member['id'] ?>">
                                                <input type="hidden" name="current_tab" value="team">
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Sil
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="text-center py-16">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-users text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Henüz ekip üyesi eklenmemiş</h3>
                            <p class="text-gray-600 mb-6">İlk ekip üyenizi oluşturarak başlayın</p>
                            <a href="about-content-add.php?section=team"
                                class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                İlk Ekip Üyesini Ekle
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sortable.js CDN -->
<script src="https:

<!-- JavaScript for enhanced UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            
            tabButtons.forEach(btn => {
                btn.classList.remove('border-primary-500', 'text-primary-600', 'bg-primary-50');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.add('border-primary-500', 'text-primary-600', 'bg-primary-50');
            this.classList.remove('border-transparent', 'text-gray-500');
            
            
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(targetTab + '-tab').classList.remove('hidden');
            
            
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', url);
        });
    });
    
    
    ['values', 'team'].forEach(section => {
        const list = document.getElementById(section + '-list');
        if (list) {
            new Sortable(list, {
                handle: '.cursor-move',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: function(evt) {
                    
                    const orderData = {};
                    const items = list.querySelectorAll('[data-block-id]');
                    
                    items.forEach((item, index) => {
                        const blockId = item.getAttribute('data-block-id');
                        orderData[blockId] = index + 1;
                    });
                    
                    
                    const formData = new FormData();
                    formData.append('csrf_token', '<?= generate_csrf_token() ?>');
                    formData.append('action', 'update_content_order');
                    formData.append('section', section);
                    formData.append('order_data', JSON.stringify(orderData));
                    formData.append('current_tab', section);
                    
                    fetch('about.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(() => {
                        console.log('Order updated successfully');
                    })
                    .catch(error => {
                        console.error('Error updating order:', error);
                        window.location.reload();
                    });
                }
            });
        }
    });
    
    
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type=" submit"]'); if (submitBtn && !submitBtn.onclick) {
    const btnText=submitBtn.innerHTML; submitBtn.disabled=true;
    submitBtn.innerHTML='<i class="fas fa-spinner animate-spin mr-1"></i>İşleniyor...' ; setTimeout(()=> {
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        }, 5000);
            }
        });
    });
});
    </script>

<?php

include 'includes/footer.php';
?>