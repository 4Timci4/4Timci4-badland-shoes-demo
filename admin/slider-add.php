<?php


require_once 'config/auth.php';
check_admin_auth();


require_once '../config/database.php';
require_once '../services/SliderService.php';


$page_title = 'Yeni Slider';
$breadcrumb_items = [
    ['title' => 'Slider Yönetimi', 'url' => 'sliders.php', 'icon' => 'fas fa-images'],
    ['title' => 'Yeni Slider', 'url' => '#', 'icon' => 'fas fa-plus']
];


if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $bg_color = trim($_POST['bg_color'] ?? '#f0f0f0');
        $button_text = trim($_POST['button_text'] ?? '');
        $button_url = trim($_POST['button_url'] ?? '');
        $is_active = isset($_POST['is_active']) && $_POST['is_active'] === '1';


        $errors = [];

        if (empty($title)) {
            $errors[] = 'Slider başlığı zorunludur.';
        }

        if (empty($description)) {
            $errors[] = 'Slider açıklaması zorunludur.';
        }

        if (empty($button_text)) {
            $errors[] = 'Buton metni zorunludur.';
        }

        if (empty($button_url)) {
            $errors[] = 'Buton linki zorunludur.';
        }

        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $bg_color)) {
            $errors[] = 'Geçerli bir arka plan rengi seçiniz.';
        }

        if (empty($errors)) {
            try {
                $slider_data = [
                    'title' => $title,
                    'description' => $description,
                    'image_url' => $image_url,
                    'bg_color' => $bg_color,
                    'button_text' => $button_text,
                    'button_url' => $button_url,
                    'is_active' => $is_active
                ];

                $sliderService = new SliderService();

                if ($sliderService->createSlider($slider_data)) {
                    set_flash_message('success', 'Slider başarıyla eklendi.');
                    header('Location: sliders.php');
                    exit;
                } else {
                    set_flash_message('error', 'Slider eklenirken bir hata oluştu.');
                }
            } catch (Exception $e) {
                set_flash_message('error', 'Slider eklenirken bir hata oluştu: ' . $e->getMessage());
            }
        } else {
            set_flash_message('error', implode('<br>', $errors));
        }
    }
}


include 'includes/header.php';
?>

<!-- Slider Add Content -->
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Yeni Slider</h1>
            <p class="text-gray-600">Ana sayfa için yeni bir slider oluşturun</p>
        </div>
        <div class="mt-4 lg:mt-0">
            <a href="sliders.php"
                class="inline-flex items-center justify-center px-4 py-2 sm:px-6 sm:py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm sm:text-base">
                <i class="fas fa-arrow-left mr-2"></i>
                Slider Listesine Dön
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
            <span class="<?= $text_color ?> font-medium"><?= nl2br(htmlspecialchars($flash_message['message'])) ?></span>
        </div>
    <?php endif; ?>

    <!-- Slider Form -->
    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6 order-2 lg:order-1">

                <!-- Title and Description -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Slider İçeriği</h3>
                        <p class="text-gray-600 text-sm mt-1">Slider başlığı ve açıklaması</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-heading mr-2"></i>Slider Başlığı *
                            </label>
                            <input type="text" id="title" name="title" required
                                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                placeholder="Örn: Yeni Sezon Koleksiyonu"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors text-lg">
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-2"></i>Açıklama *
                            </label>
                            <textarea id="description" name="description" required rows="3"
                                placeholder="Slider açıklamasını yazın..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Button Settings -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Buton Ayarları</h3>
                        <p class="text-gray-600 text-sm mt-1">Slider butonunun metni ve linki</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Button Text -->
                        <div>
                            <label for="button_text" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-mouse-pointer mr-2"></i>Buton Metni *
                            </label>
                            <input type="text" id="button_text" name="button_text" required
                                value="<?= htmlspecialchars($_POST['button_text'] ?? '') ?>"
                                placeholder="Örn: Şimdi Alışveriş Yap"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        </div>

                        <!-- Button URL -->
                        <div>
                            <label for="button_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-link mr-2"></i>Buton Linki *
                            </label>
                            <input type="text" id="button_url" name="button_url" required
                                value="<?= htmlspecialchars($_POST['button_url'] ?? '') ?>"
                                placeholder="/products veya https://"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Dahili sayfa için "/" ile başlayın, harici link için
                                "https:
                        </div>
                    </div>
                </div>

                <!-- Live Preview -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Canlı Önizleme</h3>
                        <p class="text-gray-600 text-sm mt-1">Sliderinizin nasıl görüneceğini önizleyin</p>
                    </div>
                    <div class="p-6">
                        <div id="slider-preview" class="relative h-64 rounded-xl overflow-hidden"
                            style="background-color: #f0f0f0;">
                            <div id="preview-bg-image" class="absolute inset-0 bg-cover bg-center hidden"></div>
                            <div class="absolute inset-0 bg-black opacity-40 hidden" id="preview-overlay"></div>
                            <div class="relative z-10 h-full flex items-center justify-center text-center text-white">
                                <div class="max-w-md px-4">
                                    <h2 id="preview-title" class="text-2xl md:text-3xl font-bold mb-3">Slider Başlığı
                                    </h2>
                                    <p id="preview-description" class="text-sm md:text-base mb-4">Slider açıklaması
                                        burada görünecek</p>
                                    <div id="preview-button"
                                        class="inline-block px-6 py-2 bg-blue-600 text-white rounded-full font-semibold text-sm">
                                        Buton Metni
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 order-1 lg:order-2">

                <!-- Publish Options -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Yayın Durumu</h3>
                        <p class="text-gray-600 text-sm mt-1">Slider durumunu belirleyin</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" <?= ($_POST['is_active'] ?? '1') === '1' ? 'checked' : '' ?>
                                    class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="ml-3 text-sm font-medium text-gray-900">
                                    <i class="fas fa-eye text-green-500 mr-2"></i>Slider Aktif
                                </span>
                            </label>
                            <p class="text-xs text-gray-500 ml-7">Aktif sliderlar ana sayfada görüntülenir</p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <button type="submit"
                                class="w-full bg-primary-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-primary-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-save mr-2"></i>
                                Slideri Kaydet
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Visual Settings -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Görsel Ayarları</h3>
                        <p class="text-gray-600 text-sm mt-1">Slider görselini ayarlayın</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Background Image -->
                        <div>
                            <label for="image_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-image mr-2"></i>Arka Plan Resmi (Opsiyonel)
                            </label>
                            <input type="url" id="image_url" name="image_url"
                                value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>"
                                placeholder="https://example.com/image.jpg"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Unsplash, Pexels gibi sitelerden resim URL'si</p>
                        </div>

                        <!-- Background Color -->
                        <div>
                            <label for="bg_color" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-palette mr-2"></i>Arka Plan Rengi *
                            </label>
                            <div class="flex space-x-2">
                                <input type="color" id="bg_color" name="bg_color" required
                                    value="<?= htmlspecialchars($_POST['bg_color'] ?? '#f0f0f0') ?>"
                                    class="w-16 h-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                <input type="text" id="bg_color_text"
                                    value="<?= htmlspecialchars($_POST['bg_color'] ?? '#f0f0f0') ?>" readonly
                                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 font-mono text-sm">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Resim yoksa bu renk kullanılır</p>
                        </div>

                        <!-- Quick Color Presets -->
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Hazır Renkler</p>
                            <div class="grid grid-cols-4 gap-2">
                                <?php
                                $presets = ['#f0f0f0', '#e3f2fd', '#f3e5f5', '#e8f5e8', '#fff3e0', '#ffebee', '#f1f8e9', '#fafafa'];
                                foreach ($presets as $color):
                                    ?>
                                    <button type="button"
                                        class="w-12 h-12 rounded-lg border-2 border-gray-200 hover:border-gray-400 transition-colors color-preset"
                                        style="background-color: <?= $color ?>" data-color="<?= $color ?>">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript for enhanced UX -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const previewTitle = document.getElementById('preview-title');
        const previewDescription = document.getElementById('preview-description');
        const previewButton = document.getElementById('preview-button');
        const previewContainer = document.getElementById('slider-preview');
        const previewBgImage = document.getElementById('preview-bg-image');
        const previewOverlay = document.getElementById('preview-overlay');


        const titleInput = document.getElementById('title');
        const descriptionInput = document.getElementById('description');
        const buttonTextInput = document.getElementById('button_text');
        const imageUrlInput = document.getElementById('image_url');
        const bgColorInput = document.getElementById('bg_color');
        const bgColorText = document.getElementById('bg_color_text');


        function updatePreview() {
            previewTitle.textContent = titleInput.value || 'Slider Başlığı';
            previewDescription.textContent = descriptionInput.value || 'Slider açıklaması burada görünecek';
            previewButton.textContent = buttonTextInput.value || 'Buton Metni';
            previewContainer.style.backgroundColor = bgColorInput.value;


            const imageUrl = imageUrlInput.value.trim();
            if (imageUrl && (imageUrl.startsWith('http:
            previewBgImage.style.backgroundImage = `url(${imageUrl})`;
            previewBgImage.classList.remove('hidden');
            previewOverlay.classList.remove('hidden');
        } else {
            previewBgImage.classList.add('hidden');
            previewOverlay.classList.add('hidden');
        }
    }
    
    
    titleInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    buttonTextInput.addEventListener('input', updatePreview);
    imageUrlInput.addEventListener('input', updatePreview);
    bgColorInput.addEventListener('input', function () {
        bgColorText.value = this.value;
        updatePreview();
    });


    document.querySelectorAll('.color-preset').forEach(button => {
        button.addEventListener('click', function () {
            const color = this.getAttribute('data-color');
            bgColorInput.value = color;
            bgColorText.value = color;
            updatePreview();
        });
    });


    updatePreview();


    const form = document.querySelector('form');
    form.addEventListener('submit', function (e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const btnText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Kaydediliyor...';


        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        }, 10000);
    });


    function addCharacterCounter(input, maxLength = null) {
        const counter = document.createElement('div');
        counter.className = 'text-xs text-gray-500 mt-1';
        input.parentNode.appendChild(counter);

        function updateCounter() {
            const length = input.value.length;
            if (maxLength) {
                counter.textContent = `${length}/${maxLength} karakter`;
                if (length > maxLength) {
                    counter.className = 'text-xs text-red-500 mt-1';
                } else {
                    counter.className = 'text-xs text-gray-500 mt-1';
                }
            } else {
                counter.textContent = `${length} karakter`;
            }
        }

        input.addEventListener('input', updateCounter);
        updateCounter();
    }

    addCharacterCounter(titleInput, 80);
    addCharacterCounter(descriptionInput, 200);
    addCharacterCounter(buttonTextInput, 30);
});
</script>

<?php

include 'includes/footer.php';
?>