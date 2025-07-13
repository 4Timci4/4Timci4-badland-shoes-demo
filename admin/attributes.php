<?php


require_once 'config/auth.php';
check_admin_auth();


require_once '../config/database.php';
require_once '../services/AttributeService.php';


$page_title = 'Renkler & Bedenler';
$breadcrumb_items = [
    ['title' => 'Renkler & Bedenler', 'url' => '#', 'icon' => 'fas fa-palette']
];


$active_tab = $_GET['tab'] ?? 'colors';
if (!in_array($active_tab, ['colors', 'sizes'])) {
    $active_tab = 'colors';
}


if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        $action = $_POST['action'] ?? '';
        $type = $_POST['type'] ?? '';

        switch ($action) {
            case 'add_color':
                $name = trim($_POST['name'] ?? '');
                $hex_code = trim($_POST['hex_code'] ?? '');

                if (empty($name) || empty($hex_code)) {
                    set_flash_message('error', 'Renk adı ve hex kodu zorunludur.');
                } else {
                    if (attribute_service()->createColor(['name' => $name, 'hex_code' => $hex_code])) {
                        set_flash_message('success', 'Renk başarıyla eklendi.');
                    } else {
                        set_flash_message('error', 'Renk eklenirken bir hata oluştu.');
                    }
                }
                break;

            case 'edit_color':
                $color_id = intval($_POST['color_id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $hex_code = trim($_POST['hex_code'] ?? '');

                if ($color_id > 0 && !empty($name) && !empty($hex_code)) {
                    if (attribute_service()->updateColor($color_id, ['name' => $name, 'hex_code' => $hex_code])) {
                        set_flash_message('success', 'Renk başarıyla güncellendi.');
                    } else {
                        set_flash_message('error', 'Renk güncellenirken bir hata oluştu.');
                    }
                }
                break;

            case 'delete_color':
                $color_id = intval($_POST['color_id'] ?? 0);

                if ($color_id > 0) {
                    if (attribute_service()->deleteColor($color_id)) {
                        set_flash_message('success', 'Renk başarıyla silindi.');
                    } else {
                        set_flash_message('error', 'Renk silinemedi. Bu rengi kullanan ürün varyantları mevcut olabilir.');
                    }
                }
                break;

            case 'add_size':
                $name = trim($_POST['name'] ?? '');

                if (empty($name)) {
                    set_flash_message('error', 'Beden adı zorunludur.');
                } else {
                    if (attribute_service()->createSize(['name' => $name])) {
                        set_flash_message('success', 'Beden başarıyla eklendi.');
                    } else {
                        set_flash_message('error', 'Beden eklenirken bir hata oluştu.');
                    }
                }
                break;

            case 'edit_size':
                $size_id = intval($_POST['size_id'] ?? 0);
                $name = trim($_POST['name'] ?? '');

                if ($size_id > 0 && !empty($name)) {
                    if (attribute_service()->updateSize($size_id, ['name' => $name])) {
                        set_flash_message('success', 'Beden başarıyla güncellendi.');
                    } else {
                        set_flash_message('error', 'Beden güncellenirken bir hata oluştu.');
                    }
                }
                break;

            case 'delete_size':
                $size_id = intval($_POST['size_id'] ?? 0);

                if ($size_id > 0) {
                    if (attribute_service()->deleteSize($size_id)) {
                        set_flash_message('success', 'Beden başarıyla silindi.');
                    } else {
                        set_flash_message('error', 'Beden silinemedi. Bu bedeni kullanan ürün varyantları mevcut olabilir.');
                    }
                }
                break;

            case 'update_size_order':
                $order_data = json_decode($_POST['order_data'] ?? '[]', true);

                if (!empty($order_data)) {
                    if (attribute_service()->updateSizeOrder($order_data)) {
                        set_flash_message('success', 'Beden sıralaması güncellendi.');
                    } else {
                        set_flash_message('error', 'Sıralama güncellenirken bir hata oluştu.');
                    }
                }
                break;
        }


        header('Location: attributes.php?tab=' . $active_tab);
        exit;
    }
}


$colors = attribute_service()->getColorsWithUsageCounts();
$sizes = attribute_service()->getSizesWithUsageCounts();


$edit_mode = isset($_GET['edit']) && !empty($_GET['edit']);
$edit_type = $_GET['type'] ?? '';
$edit_item = null;

if ($edit_mode) {
    $edit_id = intval($_GET['edit']);
    if ($edit_type === 'color') {
        $edit_item = attribute_service()->getColorById($edit_id);
    } elseif ($edit_type === 'size') {
        $edit_item = attribute_service()->getSizeById($edit_id);
    }

    if (empty($edit_item)) {
        $edit_mode = false;
    }
}


include 'includes/header.php';
?>

<!-- Attributes Management Content -->
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Renkler & Bedenler</h1>
            <p class="text-gray-600">Ürün özelliklerini yönetin ve düzenleyin</p>
        </div>
        <div class="mt-4 lg:mt-0">
            <a href="products.php"
                class="inline-flex items-center justify-center px-4 py-2 sm:px-6 sm:py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm sm:text-base">
                <i class="fas fa-box mr-2"></i>
                Ürünlere Dön
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

    <!-- Tab Navigation -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100">
            <nav class="flex flex-col sm:flex-row sm:space-x-8 px-4 sm:px-6" aria-label="Tabs">
                <a href="attributes.php?tab=colors"
                    class="border-b-2 py-3 sm:py-4 px-1 text-sm font-medium transition-colors <?= $active_tab === 'colors' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> text-center">
                    <i class="fas fa-palette mr-2"></i>
                    Renkler (<?= count($colors) ?>)
                </a>
                <a href="attributes.php?tab=sizes"
                    class="border-b-2 py-3 sm:py-4 px-1 text-sm font-medium transition-colors <?= $active_tab === 'sizes' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> text-center">
                    <i class="fas fa-ruler mr-2"></i>
                    Bedenler (<?= count($sizes) ?>)
                </a>
            </nav>
        </div>

        <!-- Colors Tab Content -->
        <?php if ($active_tab === 'colors'): ?>
            <div class="p-6">
                <!-- Quick Add Color Form -->
                <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <?= ($edit_mode && $edit_type === 'color') ? 'Renk Düzenle' : 'Yeni Renk Ekle' ?>
                    </h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action"
                            value="<?= ($edit_mode && $edit_type === 'color') ? 'edit_color' : 'add_color' ?>">
                        <?php if ($edit_mode && $edit_type === 'color'): ?>
                            <input type="hidden" name="color_id" value="<?= $edit_item['id'] ?>">
                        <?php endif; ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Color Name -->
                            <div class="lg:col-span-2">
                                <label for="color_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-tag mr-2"></i>Renk Adı *
                                </label>
                                <input type="text" id="color_name" name="name" required
                                    value="<?= htmlspecialchars($edit_item['name'] ?? '') ?>" placeholder="Örn: Kırmızı"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <!-- Color Picker -->
                            <div>
                                <label for="color_hex" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-palette mr-2"></i>Renk Kodu *
                                </label>
                                <input type="color" id="color_hex" name="hex_code" required
                                    value="<?= htmlspecialchars($edit_item['hex_code'] ?? '#000000') ?>"
                                    class="w-full h-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <!-- Submit Button -->
                            <div class="lg:self-end">
                                <div class="flex space-x-2">
                                    <button type="submit"
                                        class="flex-1 bg-primary-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-primary-700 transition-colors flex items-center justify-center">
                                        <i
                                            class="fas <?= ($edit_mode && $edit_type === 'color') ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                                        <?= ($edit_mode && $edit_type === 'color') ? 'Güncelle' : 'Ekle' ?>
                                    </button>
                                    <?php if ($edit_mode && $edit_type === 'color'): ?>
                                        <a href="attributes.php?tab=colors"
                                            class="bg-gray-100 text-gray-700 font-semibold py-3 px-4 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Colors List -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Mevcut Renkler</h3>
                    <?php if (!empty($colors)): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php foreach ($colors as $color): ?>
                                <div
                                    class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow <?= ($edit_mode && $edit_type === 'color' && $edit_item['id'] == $color['id']) ? 'ring-2 ring-primary-500' : '' ?>">
                                    <div class="flex items-center space-x-4 mb-3">
                                        <div class="w-8 h-8 rounded-full border-2 border-gray-200"
                                            style="background-color: <?= htmlspecialchars($color['hex_code']) ?>"></div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($color['name']) ?></h4>
                                            <p class="text-sm text-gray-500 font-mono"><?= htmlspecialchars($color['hex_code']) ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                            <?= $color['usage_count'] ?> kullanım
                                        </span>

                                        <div class="flex space-x-1">
                                            <a href="attributes.php?tab=colors&edit=<?= $color['id'] ?>&type=color"
                                                class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-xs">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <?php if ($color['usage_count'] == 0): ?>
                                                <form method="POST" class="inline-block"
                                                    onsubmit="return confirm('Bu rengi silmek istediğinizden emin misiniz?')">
                                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                    <input type="hidden" name="action" value="delete_color">
                                                    <input type="hidden" name="color_id" value="<?= $color['id'] ?>">
                                                    <button type="submit"
                                                        class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="text-center py-16">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-palette text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Henüz renk yok</h3>
                            <p class="text-gray-600">İlk renginizi oluşturarak başlayın</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sizes Tab Content -->
        <?php if ($active_tab === 'sizes'): ?>
            <div class="p-6">
                <!-- Quick Add Size Form -->
                <div class="mb-8 p-6 bg-gray-50 rounded-xl">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <?= ($edit_mode && $edit_type === 'size') ? 'Beden Düzenle' : 'Yeni Beden Ekle' ?>
                    </h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action"
                            value="<?= ($edit_mode && $edit_type === 'size') ? 'edit_size' : 'add_size' ?>">
                        <?php if ($edit_mode && $edit_type === 'size'): ?>
                            <input type="hidden" name="size_id" value="<?= $edit_item['id'] ?>">
                        <?php endif; ?>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Size Name -->
                            <div class="lg:col-span-2">
                                <label for="size_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-ruler mr-2"></i>Beden Adı *
                                </label>
                                <input type="text" id="size_name" name="name" required
                                    value="<?= htmlspecialchars($edit_item['size_value'] ?? '') ?>"
                                    placeholder="Örn: 42, Large, XL"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            </div>

                            <!-- Submit Button -->
                            <div class="lg:self-end">
                                <div class="flex space-x-2">
                                    <button type="submit"
                                        class="flex-1 bg-primary-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-primary-700 transition-colors flex items-center justify-center">
                                        <i
                                            class="fas <?= ($edit_mode && $edit_type === 'size') ? 'fa-save' : 'fa-plus' ?> mr-2"></i>
                                        <?= ($edit_mode && $edit_type === 'size') ? 'Güncelle' : 'Ekle' ?>
                                    </button>
                                    <?php if ($edit_mode && $edit_type === 'size'): ?>
                                        <a href="attributes.php?tab=sizes"
                                            class="bg-gray-100 text-gray-700 font-semibold py-3 px-4 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Sizes List -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Mevcut Bedenler</h3>
                        <?php if (!empty($sizes)): ?>
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-arrows-alt mr-1"></i>
                                Sıralamak için sürükleyin
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($sizes)): ?>
                        <div id="sizes-list" class="space-y-2">
                            <?php foreach ($sizes as $size): ?>
                                <div class="bg-white border border-gray-200 rounded-xl p-4 cursor-move hover:shadow-md transition-shadow <?= ($edit_mode && $edit_type === 'size' && $edit_item['id'] == $size['id']) ? 'ring-2 ring-primary-500' : '' ?>"
                                    data-size-id="<?= $size['id'] ?>">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-grip-vertical text-gray-400"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($size['size_value']) ?>
                                                </h4>
                                                <p class="text-sm text-gray-500">Type: <?= htmlspecialchars($size['size_type']) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                                <?= $size['usage_count'] ?> kullanım
                                            </span>

                                            <div class="flex space-x-1">
                                                <a href="attributes.php?tab=sizes&edit=<?= $size['id'] ?>&type=size"
                                                    class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-xs">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <?php if ($size['usage_count'] == 0): ?>
                                                    <form method="POST" class="inline-block"
                                                        onsubmit="return confirm('Bu bedeni silmek istediğinizden emin misiniz?')">
                                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                        <input type="hidden" name="action" value="delete_size">
                                                        <input type="hidden" name="size_id" value="<?= $size['id'] ?>">
                                                        <button type="submit"
                                                            class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
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
                                <i class="fas fa-ruler text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Henüz beden yok</h3>
                            <p class="text-gray-600">İlk bedeninizi oluşturarak başlayın</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Statistics Card -->
    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-6 text-white">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold mb-2"><?= count($colors) ?></div>
                <div class="text-purple-100">Toplam Renk</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold mb-2"><?= count($sizes) ?></div>
                <div class="text-purple-100">Toplam Beden</div>
            </div>
        </div>
    </div>
</div>

<!-- Sortable.js CDN -->
<script src="https:

<!-- JavaScript for enhanced UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const sizesList = document.getElementById('sizes-list');
    if (sizesList) {
        new Sortable(sizesList, {
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: function(evt) {
                
                const orderData = {};
                const items = sizesList.querySelectorAll('[data-size-id]');
                
                items.forEach((item, index) => {
                    const sizeId = item.getAttribute('data-size-id');
                    orderData[sizeId] = index + 1;
                });
                
                
                const formData = new FormData();
                formData.append('csrf_token', '<?= generate_csrf_token() ?>');
                formData.append('action', 'update_size_order');
                formData.append('order_data', JSON.stringify(orderData));
                
                fetch('attributes.php?tab=sizes', {
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
    
    
    const colorInput = document.getElementById('color_hex');
    if (colorInput) {
        colorInput.addEventListener('input', function() {
            
        });
    }
    
    
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type=" submit"]'); if (submitBtn) { const
    btnText=submitBtn.innerHTML; submitBtn.disabled=true;
    submitBtn.innerHTML='<i class="fas fa-spinner animate-spin mr-2"></i>İşleniyor...' ; setTimeout(()=> {
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