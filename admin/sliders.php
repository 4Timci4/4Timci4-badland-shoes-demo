<?php


require_once 'config/auth.php';
check_admin_auth();


require_once '../config/database.php';
require_once '../services/SliderService.php';


$page_title = 'Slider Yönetimi';
$breadcrumb_items = [
    ['title' => 'Slider Yönetimi', 'url' => '#', 'icon' => 'fas fa-images']
];


if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        $action = $_POST['action'] ?? '';
        $sliderService = new SliderService();

        switch ($action) {
            case 'delete':
                $slider_id = intval($_POST['slider_id'] ?? 0);

                if ($slider_id > 0) {
                    if ($sliderService->deleteSlider($slider_id)) {
                        set_flash_message('success', 'Slider başarıyla silindi.');
                    } else {
                        set_flash_message('error', 'Slider silinirken bir hata oluştu.');
                    }
                } else {
                    set_flash_message('error', 'Geçersiz slider ID.');
                }
                break;

            case 'toggle_status':
                $slider_id = intval($_POST['slider_id'] ?? 0);

                if ($slider_id > 0) {
                    if ($sliderService->toggleSliderStatus($slider_id)) {
                        set_flash_message('success', 'Slider durumu başarıyla değiştirildi.');
                    } else {
                        set_flash_message('error', 'Slider durumu değiştirilirken bir hata oluştu.');
                    }
                } else {
                    set_flash_message('error', 'Geçersiz slider ID.');
                }
                break;

            case 'update_order':
                $order_data = json_decode($_POST['order_data'] ?? '[]', true);

                if (!empty($order_data)) {
                    if ($sliderService->updateSliderOrder($order_data)) {
                        set_flash_message('success', 'Slider sıralaması güncellendi.');
                    } else {
                        set_flash_message('error', 'Sıralama güncellenirken bir hata oluştu.');
                    }
                }
                break;
        }


        header('Location: sliders.php');
        exit;
    }
}


try {
    $sliderService = new SliderService();
    $sliders = $sliderService->getAllSliders();
    $stats = $sliderService->getSliderStats();
} catch (Exception $e) {
    $sliders = [];
    $stats = ['total' => 0, 'active' => 0, 'inactive' => 0];
    set_flash_message('error', 'Slider verileri yüklenirken bir hata oluştu: ' . $e->getMessage());
}


include 'includes/header.php';
?>

<!-- Slider Management Content -->
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Slider Yönetimi</h1>
            <p class="text-gray-600">Ana sayfa sliderlarını yönetin ve düzenleyin</p>
        </div>
        <div class="mt-4 lg:mt-0 flex space-x-3">
            <a href="slider-add.php"
                class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Yeni Slider
            </a>
            <a href="../index.php" target="_blank"
                class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                <i class="fas fa-external-link-alt mr-2"></i>
                Ana Sayfayı Gör
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

    <!-- Slider Statistics -->
    <?php if ($stats['total'] > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-xl">
                        <i class="fas fa-images text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Toplam Slider</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-xl">
                        <i class="fas fa-eye text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Aktif Slider</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['active'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-xl">
                        <i class="fas fa-eye-slash text-orange-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Pasif Slider</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['inactive'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Slider List -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Sliderlar</h3>
                    <p class="text-gray-600 text-sm">Tüm sliderlarınızın listesi</p>
                </div>
                <div class="text-sm text-gray-500">
                    <?php if (!empty($sliders)): ?>
                        <i class="fas fa-arrows-alt mr-1"></i>
                        Sıralamak için sürükleyin
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($sliders)): ?>
            <div id="sliders-list" class="divide-y divide-gray-100">
                <?php foreach ($sliders as $slider): ?>
                    <div class="slider-item p-6 hover:bg-gray-50 transition-colors cursor-move"
                        data-slider-id="<?= $slider['id'] ?>">
                        <div class="flex items-center space-x-6">

                            <!-- Drag Handle -->
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center cursor-move">
                                    <i class="fas fa-grip-vertical text-gray-400"></i>
                                </div>
                            </div>

                            <!-- Slider Preview -->
                            <div class="w-32 h-20 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0"
                                style="background-color: <?= htmlspecialchars($slider['bg_color']) ?>">
                                <?php if (!empty($slider['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($slider['image_url']) ?>"
                                        alt="<?= htmlspecialchars($slider['title']) ?>" class="w-full h-full object-cover"
                                        onerror="this.style.display='none'">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-2xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Slider Info -->
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 text-lg mb-1 truncate">
                                    <?= htmlspecialchars($slider['title']) ?>
                                </h4>
                                <p class="text-gray-600 text-sm mb-2 line-clamp-2">
                                    <?= htmlspecialchars($slider['description']) ?>
                                </p>
                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                    <span><i class="fas fa-sort-numeric-up mr-1"></i>Sıra: <?= $slider['sort_order'] ?></span>
                                    <span><i class="fas fa-link mr-1"></i><?= htmlspecialchars($slider['button_text']) ?></span>
                                    <span><i
                                            class="fas fa-calendar mr-1"></i><?= date('d M Y', strtotime($slider['created_at'])) ?></span>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="flex-shrink-0">
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="slider_id" value="<?= $slider['id'] ?>">
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors
                                        <?= $slider['is_active'] ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' ?>">
                                        <i class="fas <?= $slider['is_active'] ? 'fa-eye' : 'fa-eye-slash' ?> mr-2"></i>
                                        <?= $slider['is_active'] ? 'Aktif' : 'Pasif' ?>
                                    </button>
                                </form>
                            </div>

                            <!-- Actions -->
                            <div class="flex-shrink-0">
                                <div class="flex items-center space-x-2">
                                    <a href="slider-edit.php?id=<?= $slider['id'] ?>"
                                        class="inline-flex items-center justify-center w-20 px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>
                                        Düzenle
                                    </a>

                                    <form method="POST" class="inline-block"
                                        onsubmit="return confirm('Bu slideri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="slider_id" value="<?= $slider['id'] ?>">
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-20 px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                                            <i class="fas fa-trash mr-1"></i>
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-images text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Henüz slider yok</h3>
                <p class="text-gray-600 mb-6">İlk sliderinizi oluşturarak başlayın</p>
                <a href="slider-add.php"
                    class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    İlk Slideri Ekle
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Sortable.js CDN -->
<script src="https:

<!-- JavaScript for enhanced UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const slidersList = document.getElementById('sliders-list');
    if (slidersList) {
        new Sortable(slidersList, {
            handle: '.cursor-move',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: function(evt) {
                
                const orderData = {};
                const items = slidersList.querySelectorAll('[data-slider-id]');
                
                items.forEach((item, index) => {
                    const sliderId = item.getAttribute('data-slider-id');
                    orderData[sliderId] = index + 1;
                });
                
                
                const formData = new FormData();
                formData.append('csrf_token', '<?= generate_csrf_token() ?>');
                formData.append('action', 'update_order');
                formData.append('order_data', JSON.stringify(orderData));
                
                fetch('sliders.php', {
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
    
    
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type=" submit"]'); if (submitBtn && !submitBtn.onclick) {
    const btnText=submitBtn.innerHTML; submitBtn.disabled=true;
    submitBtn.innerHTML='<i class="fas fa-spinner animate-spin mr-1"></i>İşleniyor...' ; setTimeout(()=> {
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        }, 3000);
            }
        });
    });
});
    </script>

<?php

include 'includes/footer.php';
?>