<?php
/**
 * Slider Düzenleme Sayfası
 * Modern, kullanıcı dostu slider düzenleme formu
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';
require_once '../services/SliderService.php';

// Slider ID kontrolü
$slider_id = intval($_GET['id'] ?? 0);
if ($slider_id <= 0) {
    set_flash_message('error', 'Geçersiz slider ID.');
    header('Location: sliders.php');
    exit;
}

// Slider verisini getir
try {
    $sliderService = new SliderService();
    $slider = $sliderService->getSliderById($slider_id);
    
    if (!$slider) {
        set_flash_message('error', 'Slider bulunamadı.');
        header('Location: sliders.php');
        exit;
    }
} catch (Exception $e) {
    set_flash_message('error', 'Slider yüklenirken bir hata oluştu: ' . $e->getMessage());
    header('Location: sliders.php');
    exit;
}

// Sayfa bilgileri
$page_title = 'Slider Düzenle: ' . htmlspecialchars($slider['title']);
$breadcrumb_items = [
    ['title' => 'Slider Yönetimi', 'url' => 'sliders.php', 'icon' => 'fas fa-images'],
    ['title' => 'Slider Düzenle', 'url' => '#', 'icon' => 'fas fa-edit']
];

// POST işlemi
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
        
        // Validation
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
                
                if ($sliderService->updateSlider($slider_id, $slider_data)) {
                    set_flash_message('success', 'Slider başarıyla güncellendi.');
                    header('Location: sliders.php');
                    exit;
                } else {
                    set_flash_message('error', 'Slider güncellenirken bir hata oluştu.');
                }
            } catch (Exception $e) {
                set_flash_message('error', 'Slider güncellenirken bir hata oluştu: ' . $e->getMessage());
            }
        } else {
            set_flash_message('error', implode('<br>', $errors));
        }
    }
} else {
    // Form verilerini slider verisinden doldur
    $_POST = [
        'title' => $slider['title'],
        'description' => $slider['description'],
        'image_url' => $slider['image_url'] ?? '',
        'bg_color' => $slider['bg_color'],
        'button_text' => $slider['button_text'],
        'button_url' => $slider['button_url'],
        'is_active' => $slider['is_active'] ? '1' : '0'
    ];
}

// Header dahil et
include 'includes/header.php';
?>

<!-- Slider Edit Content -->
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Slider Düzenle</h1>
            <p class="text-gray-600">Slider bilgilerini düzenleyin ve güncelleyin</p>
        </div>
        <div class="mt-4 lg:mt-0 flex space-x-3">
            <a href="../index.php" 
               target="_blank"
               class="inline-flex items-center px-6 py-3 bg-green-100 text-green-700 font-semibold rounded-xl hover:bg-green-200 transition-colors">
                <i class="fas fa-eye mr-2"></i>
                Ana Sayfayı Gör
            </a>
            <a href="sliders.php" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
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

    <!-- Slider Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
            <div>
                <p class="text-blue-800 font-medium">Düzenleme Modu</p>
                <p class="text-blue-700 text-sm">
                    Slider ID: #<?= $slider['id'] ?> | 
                    Sıra: <?= $slider['sort_order'] ?> | 
                    Oluşturma: <?= date('d M Y H:i', strtotime($slider['created_at'])) ?> | 
                    Durum: <span class="font-semibold"><?= $slider['is_active'] ? 'Aktif' : 'Pasif' ?></span>
                </p>
            </div>
        </div>
    </div>

    <!-- Slider Form -->
    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                
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
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   required
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                   placeholder="Örn: Yeni Sezon Koleksiyonu"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors text-lg">
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-2"></i>Açıklama *
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      required
                                      rows="3"
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
                            <input type="text" 
                                   id="button_text" 
                                   name="button_text" 
                                   required
                                   value="<?= htmlspecialchars($_POST['button_text'] ?? '') ?>"
                                   placeholder="Örn: Şimdi Alışveriş Yap"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        </div>

                        <!-- Button URL -->
                        <div>
                            <label for="button_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-link mr-2"></i>Buton Linki *
                            </label>
                            <input type="text" 
                                   id="button_url" 
                                   name="button_url" 
                                   required
                                   value="<?= htmlspecialchars($_POST['button_url'] ?? '') ?>"
                                   placeholder="/products.php veya https://example.com"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Dahili sayfa için "/" ile başlayın, harici link için "https://" kullanın</p>
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
                        <div id="slider-preview" class="relative h-64 rounded-xl overflow-hidden" style="background-color: <?= htmlspecialchars($_POST['bg_color'] ?? '#f0f0f0') ?>;">
                            <div id="preview-bg-image" class="absolute inset-0 bg-cover bg-center <?= empty($_POST['image_url']) ? 'hidden' : '' ?>"
                                 style="background-image: url('<?= htmlspecialchars($_POST['image_url'] ?? '') ?>');"></div>
                            <div class="absolute inset-0 bg-black opacity-40 <?= empty($_POST['image_url']) ? 'hidden' : '' ?>" id="preview-overlay"></div>
                            <div class="relative z-10 h-full flex items-center justify-center text-center text-white">
                                <div class="max-w-md px-4">
                                    <h2 id="preview-title" class="text-2xl md:text-3xl font-bold mb-3"><?= htmlspecialchars($_POST['title'] ?? 'Slider Başlığı') ?></h2>
                                    <p id="preview-description" class="text-sm md:text-base mb-4"><?= htmlspecialchars($_POST['description'] ?? 'Slider açıklaması burada görünecek') ?></p>
                                    <div id="preview-button" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-full font-semibold text-sm">
                                        <?= htmlspecialchars($_POST['button_text'] ?? 'Buton Metni') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Publish Options -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Yayın Durumu</h3>
                        <p class="text-gray-600 text-sm mt-1">Slider durumunu belirleyin</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       <?= ($_POST['is_active'] ?? '0') === '1' ? 'checked' : '' ?>
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
                                Değişiklikleri Kaydet
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
                            <input type="url" 
                                   id="image_url" 
                                   name="image_url" 
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
                                <input type="color" 
                                       id="bg_color" 
                                       name="bg_color" 
                                       required
                                       value="<?= htmlspecialchars($_POST['bg_color'] ?? '#f0f0f0') ?>"
                                       class="w-16 h-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                <input type="text" 
                                       id="bg_color_text" 
                                       value="<?= htmlspecialchars($_POST['bg_color'] ?? '#f0f0f0') ?>"
                                       readonly
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
                                            style="background-color: <?= $color ?>"
                                            data-color="<?= $color ?>">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Slider -->
                <div class="bg-white rounded-2xl shadow-lg border border-red-200 overflow-hidden">
                    <div class="p-6 border-b border-red-100">
                        <h3 class="text-lg font-bold text-red-900">Tehlikeli İşlemler</h3>
                        <p class="text-red-600 text-sm mt-1">Bu işlemler geri alınamaz</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="sliders.php" onsubmit="return confirm('Bu slideri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="slider_id" value="<?= $slider['id'] ?>">
                            <button type="submit" 
                                    class="w-full bg-red-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-red-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-trash mr-2"></i>
                                Slideri Sil
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript for enhanced UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview elements
    const previewTitle = document.getElementById('preview-title');
    const previewDescription = document.getElementById('preview-description');
    const previewButton = document.getElementById('preview-button');
    const previewContainer = document.getElementById('slider-preview');
    const previewBgImage = document.getElementById('preview-bg-image');
    const previewOverlay = document.getElementById('preview-overlay');
    
    // Form elements
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const buttonTextInput = document.getElementById('button_text');
    const imageUrlInput = document.getElementById('image_url');
    const bgColorInput = document.getElementById('bg_color');
    const bgColorText = document.getElementById('bg_color_text');
    
    // Update preview function
    function updatePreview() {
        previewTitle.textContent = titleInput.value || 'Slider Başlığı';
        previewDescription.textContent = descriptionInput.value || 'Slider açıklaması burada görünecek';
        previewButton.textContent = buttonTextInput.value || 'Buton Metni';
        previewContainer.style.backgroundColor = bgColorInput.value;
        
        // Background image
        const imageUrl = imageUrlInput.value.trim();
        if (imageUrl && (imageUrl.startsWith('http://') || imageUrl.startsWith('https://'))) {
            previewBgImage.style.backgroundImage = `url(${imageUrl})`;
            previewBgImage.classList.remove('hidden');
            previewOverlay.classList.remove('hidden');
        } else {
            previewBgImage.classList.add('hidden');
            previewOverlay.classList.add('hidden');
        }
    }
    
    // Event listeners for live preview
    titleInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    buttonTextInput.addEventListener('input', updatePreview);
    imageUrlInput.addEventListener('input', updatePreview);
    bgColorInput.addEventListener('input', function() {
        bgColorText.value = this.value;
        updatePreview();
    });
    
    // Color presets
    document.querySelectorAll('.color-preset').forEach(button => {
        button.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            bgColorInput.value = color;
            bgColorText.value = color;
            updatePreview();
        });
    });
    
    // Form submission loading state
    const form = document.querySelector('form[method="POST"]:not([action])');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const btnText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Güncelleniyor...';
            
            // Re-enable after 10 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = btnText;
            }, 10000);
        });
    }
    
    // Character counters
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
// Footer dahil et
include 'includes/footer.php';
?>
