<?php
/**
 * Hakkımızda İçerik Düzenleme Sayfası
 * Değer veya Ekip Üyesi Düzenleme Formu
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';
require_once '../services/AboutService.php';

// Content ID kontrolü
$content_id = intval($_GET['id'] ?? 0);
if ($content_id <= 0) {
    set_flash_message('error', 'Geçersiz içerik ID.');
    header('Location: about.php');
    exit;
}

// İçerik verisini getir
try {
    $aboutService = new AboutService();
    $content = $aboutService->getContentBlockById($content_id);
    
    if (!$content) {
        set_flash_message('error', 'İçerik bulunamadı.');
        header('Location: about.php');
        exit;
    }
} catch (Exception $e) {
    set_flash_message('error', 'İçerik yüklenirken bir hata oluştu: ' . $e->getMessage());
    header('Location: about.php');
    exit;
}

$section = $content['section'];
$section_name = $section === 'values' ? 'Değerlerimiz' : 'Ekibimiz';

// Sayfa bilgileri
$page_title = $section_name . ' Düzenle: ' . htmlspecialchars($content['title']);
$breadcrumb_items = [
    ['title' => 'Hakkımızda Yönetimi', 'url' => 'about.php', 'icon' => 'fas fa-info-circle'],
    ['title' => $section_name, 'url' => 'about.php?tab=' . $section, 'icon' => $section === 'values' ? 'fas fa-star' : 'fas fa-users'],
    ['title' => 'Düzenle', 'url' => '#', 'icon' => 'fas fa-edit']
];

// POST işlemi
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        $title = trim($_POST['title'] ?? '');
        $content_text = trim($_POST['content'] ?? '');
        
        // Section'a göre farklı alanlar
        if ($section === 'values') {
            $icon = trim($_POST['icon'] ?? '');
            $subtitle = null;
            $image_url = null;
        } else { // team
            $subtitle = trim($_POST['subtitle'] ?? '');
            $image_url = trim($_POST['image_url'] ?? '');
            $icon = null;
        }
        
        // Validation
        $errors = [];
        
        if (empty($title)) {
            $errors[] = ($section === 'values' ? 'Değer' : 'İsim') . ' zorunludur.';
        }
        
        if (empty($content_text)) {
            $errors[] = 'Açıklama zorunludur.';
        }
        
        if ($section === 'values' && empty($icon)) {
            $errors[] = 'İkon seçimi zorunludur.';
        }
        
        if ($section === 'team' && empty($subtitle)) {
            $errors[] = 'Pozisyon bilgisi zorunludur.';
        }
        
        if (empty($errors)) {
            try {
                $content_data = [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'content' => $content_text,
                    'image_url' => $image_url,
                    'icon' => $icon
                ];
                
                if ($aboutService->updateContentBlock($content_id, $content_data)) {
                    set_flash_message('success', $section_name . ' başarıyla güncellendi.');
                    header('Location: about.php?tab=' . $section);
                    exit;
                } else {
                    set_flash_message('error', $section_name . ' güncellenirken bir hata oluştu.');
                }
            } catch (Exception $e) {
                set_flash_message('error', $section_name . ' güncellenirken bir hata oluştu: ' . $e->getMessage());
            }
        } else {
            set_flash_message('error', implode('<br>', $errors));
        }
    }
} else {
    // Form verilerini content verisinden doldur
    $_POST = [
        'title' => $content['title'],
        'subtitle' => $content['subtitle'],
        'content' => $content['content'],
        'image_url' => $content['image_url'] ?? '',
        'icon' => $content['icon'] ?? ''
    ];
}

// Header dahil et
include 'includes/header.php';
?>

<!-- Content Edit Page -->
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= $section_name ?> Düzenle</h1>
            <p class="text-gray-600"><?= $section_name ?> bilgilerini düzenleyin ve güncelleyin</p>
        </div>
        <div class="mt-4 lg:mt-0 flex space-x-3">
            <a href="../about.php" 
               target="_blank"
               class="inline-flex items-center px-6 py-3 bg-green-100 text-green-700 font-semibold rounded-xl hover:bg-green-200 transition-colors">
                <i class="fas fa-eye mr-2"></i>
                Hakkımızda Sayfasını Gör
            </a>
            <a href="about.php?tab=<?= $section ?>" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                <?= $section_name ?> Listesine Dön
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

    <!-- Content Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
            <div>
                <p class="text-blue-800 font-medium">Düzenleme Modu</p>
                <p class="text-blue-700 text-sm">
                    ID: #<?= $content['id'] ?> | 
                    Bölüm: <?= $section_name ?> | 
                    Sıra: <?= $content['sort_order'] ?> | 
                    Oluşturma: <?= date('d M Y H:i', strtotime($content['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Content Form -->
    <form method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Basic Information -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Temel Bilgiler</h3>
                        <p class="text-gray-600 text-sm mt-1"><?= $section === 'values' ? 'Değer' : 'Ekip üyesi' ?> bilgilerini güncelleyin</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas <?= $section === 'values' ? 'fa-star' : 'fa-user' ?> mr-2"></i>
                                <?= $section === 'values' ? 'Değer Adı' : 'İsim Soyisim' ?> *
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   required
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                   placeholder="<?= $section === 'values' ? 'Örn: Kalite' : 'Örn: Ahmet Yılmaz' ?>"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors text-lg">
                        </div>

                        <?php if ($section === 'team'): ?>
                        <!-- Subtitle (Position) -->
                        <div>
                            <label for="subtitle" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-briefcase mr-2"></i>Pozisyon *
                            </label>
                            <input type="text" 
                                   id="subtitle" 
                                   name="subtitle" 
                                   required
                                   value="<?= htmlspecialchars($_POST['subtitle'] ?? '') ?>"
                                   placeholder="Örn: Kurucu ve CEO"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        </div>
                        <?php endif; ?>

                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-2"></i>Açıklama *
                            </label>
                            <textarea id="content" 
                                      name="content" 
                                      required
                                      rows="4"
                                      placeholder="<?= $section === 'values' ? 'Değerinizin açıklaması...' : 'Ekip üyesinin kısa özgeçmişi...' ?>"
                                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <?php if ($section === 'values'): ?>
                <!-- Icon Selection for Values -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">İkon Seçimi</h3>
                        <p class="text-gray-600 text-sm mt-1">Değerinizi temsil eden ikonu güncelleyin</p>
                    </div>
                    <div class="p-6">
                        <div>
                            <label for="icon" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-icons mr-2"></i>İkon *
                            </label>
                            <input type="text" 
                                   id="icon" 
                                   name="icon" 
                                   required
                                   value="<?= htmlspecialchars($_POST['icon'] ?? '') ?>"
                                   placeholder="fas fa-star"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">FontAwesome icon class'ı girin (örn: fas fa-star)</p>
                        </div>
                        
                        <!-- Icon Preview -->
                        <div class="mt-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Önizleme:</p>
                            <div id="icon-preview" class="w-16 h-16 bg-primary-100 rounded-xl flex items-center justify-center">
                                <i id="preview-icon" class="<?= htmlspecialchars($_POST['icon'] ?? '') ?> text-primary-600 text-2xl"></i>
                            </div>
                        </div>
                        
                        <!-- Common Icons -->
                        <div class="mt-6">
                            <p class="text-sm font-semibold text-gray-700 mb-3">Popüler İkonlar:</p>
                            <div class="grid grid-cols-6 gap-3">
                                <?php 
                                $common_icons = [
                                    'fas fa-star', 'fas fa-heart', 'fas fa-shield-alt', 'fas fa-leaf',
                                    'fas fa-users', 'fas fa-thumbs-up', 'fas fa-award', 'fas fa-gem',
                                    'fas fa-lightbulb', 'fas fa-handshake', 'fas fa-rocket', 'fas fa-crown'
                                ];
                                foreach ($common_icons as $icon_class): 
                                ?>
                                    <button type="button" 
                                            class="icon-option w-12 h-12 bg-gray-100 hover:bg-primary-100 rounded-lg flex items-center justify-center transition-colors"
                                            data-icon="<?= $icon_class ?>">
                                        <i class="<?= $icon_class ?> text-gray-600 hover:text-primary-600"></i>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($section === 'team'): ?>
                <!-- Profile Image for Team -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Profil Resmi</h3>
                        <p class="text-gray-600 text-sm mt-1">Ekip üyesinin profil resmini güncelleyin</p>
                    </div>
                    <div class="p-6">
                        <div>
                            <label for="image_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-image mr-2"></i>Resim URL'si
                            </label>
                            <input type="url" 
                                   id="image_url" 
                                   name="image_url" 
                                   value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>"
                                   placeholder="https://example.com/profile.jpg"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Unsplash, gravatar gibi sitelerden profil resmi URL'si</p>
                        </div>
                        
                        <!-- Image Preview -->
                        <div id="image-preview" class="mt-4 <?= empty($_POST['image_url']) ? 'hidden' : '' ?>">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Önizleme:</p>
                            <div class="w-20 h-20 bg-gray-100 rounded-full overflow-hidden">
                                <img id="preview-img" src="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>" alt="Önizleme" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Save Options -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Güncelle</h3>
                        <p class="text-gray-600 text-sm mt-1">Değişiklikleri kaydedin</p>
                    </div>
                    <div class="p-6">
                        <button type="submit" 
                                class="w-full bg-primary-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-primary-700 transition-colors flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>
                            Değişiklikleri Kaydet
                        </button>
                    </div>
                </div>

                <!-- Delete Content -->
                <div class="bg-white rounded-2xl shadow-lg border border-red-200 overflow-hidden">
                    <div class="p-6 border-b border-red-100">
                        <h3 class="text-lg font-bold text-red-900">Tehlikeli İşlemler</h3>
                        <p class="text-red-600 text-sm mt-1">Bu işlemler geri alınamaz</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="about.php" onsubmit="return confirm('Bu içeriği silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="delete_content_block">
                            <input type="hidden" name="block_id" value="<?= $content['id'] ?>">
                            <input type="hidden" name="current_tab" value="<?= $section ?>">
                            <button type="submit" 
                                    class="w-full bg-red-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-red-700 transition-colors flex items-center justify-center">
                                <i class="fas fa-trash mr-2"></i>
                                <?= $section_name ?> Sil
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Help -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Yardım</h3>
                        <p class="text-gray-600 text-sm mt-1">İpuçları ve öneriler</p>
                    </div>
                    <div class="p-6 space-y-3">
                        <?php if ($section === 'values'): ?>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-lightbulb text-yellow-500 mt-1"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">İkon Seçimi</p>
                                    <p class="text-xs text-gray-600">Değerinizi en iyi temsil eden ikonu seçin</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-edit text-blue-500 mt-1"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Açıklama</p>
                                    <p class="text-xs text-gray-600">Kısa ve öz açıklama yazın</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-camera text-purple-500 mt-1"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Profil Resmi</p>
                                    <p class="text-xs text-gray-600">Profesyonel görünümlü resim kullanın</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-user text-green-500 mt-1"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Pozisyon</p>
                                    <p class="text-xs text-gray-600">Net ve anlaşılır pozisyon bilgisi</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript for enhanced UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    <?php if ($section === 'values'): ?>
    // Icon functionality
    const iconInput = document.getElementById('icon');
    const previewIcon = document.getElementById('preview-icon');
    
    function updateIconPreview() {
        const iconClass = iconInput.value.trim();
        if (iconClass) {
            previewIcon.className = iconClass + ' text-primary-600 text-2xl';
        } else {
            previewIcon.className = 'text-primary-600 text-2xl';
        }
    }
    
    iconInput.addEventListener('input', updateIconPreview);
    
    // Icon selection buttons
    document.querySelectorAll('.icon-option').forEach(button => {
        button.addEventListener('click', function() {
            const iconClass = this.getAttribute('data-icon');
            iconInput.value = iconClass;
            updateIconPreview();
        });
    });
    <?php endif; ?>
    
    <?php if ($section === 'team'): ?>
    // Image preview functionality
    const imageUrlInput = document.getElementById('image_url');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    imageUrlInput.addEventListener('input', function() {
        const url = this.value.trim();
        if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
            previewImg.src = url;
            previewImg.onload = function() {
                imagePreview.classList.remove('hidden');
            };
            previewImg.onerror = function() {
                imagePreview.classList.add('hidden');
            };
        } else {
            imagePreview.classList.add('hidden');
        }
    });
    <?php endif; ?>
    
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
    
    addCharacterCounter(document.getElementById('title'), 50);
    addCharacterCounter(document.getElementById('content'), 200);
    <?php if ($section === 'team'): ?>
    addCharacterCounter(document.getElementById('subtitle'), 50);
    <?php endif; ?>
});
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
