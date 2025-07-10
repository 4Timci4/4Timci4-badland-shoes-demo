<?php
/**
 * Blog Yazısı Düzenleme Sayfası
 * Modern, kullanıcı dostu blog düzenleme formu
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';

// ID parametresi zorunlu kontrolü
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error', 'Düzenlenecek blog ID\'si belirtilmedi.');
    header('Location: blogs.php');
    exit;
}

$blog_id = intval($_GET['id']);

// Blog bilgilerini getir
try {
    $blog_data = database()->select('blogs', ['id' => $blog_id]);
    
    if (empty($blog_data)) {
        set_flash_message('error', 'Blog yazısı bulunamadı.');
        header('Location: blogs.php');
        exit;
    }
    
    $blog = $blog_data[0];
} catch (Exception $e) {
    set_flash_message('error', 'Blog yüklenirken bir hata oluştu.');
    header('Location: blogs.php');
    exit;
}

// Sayfa bilgileri
$page_title = 'Blog Düzenle: ' . htmlspecialchars($blog['title']);
$breadcrumb_items = [
    ['title' => 'Blog Yazıları', 'url' => 'blogs.php', 'icon' => 'fas fa-edit'],
    ['title' => htmlspecialchars($blog['title']), 'url' => '#', 'icon' => 'fas fa-file-alt'],
    ['title' => 'Düzenle', 'url' => '#', 'icon' => 'fas fa-edit']
];

// Orijinal verileri sakla (değişiklik tespiti için)
$original_data = [
    'title' => $blog['title'],
    'excerpt' => $blog['excerpt'] ?? '',
    'content' => $blog['content'] ?? '',
    'category' => $blog['category'] ?? '',
    'tags' => is_array($blog['tags']) ? implode(', ', $blog['tags']) : '',
    'image_url' => $blog['image_url'] ?? ''
];

// Form işleme
$changes_detected = false;
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        // Form verilerini al
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? '';
        $category = trim($_POST['category'] ?? '');
        $tags_string = trim($_POST['tags'] ?? '');
        $tags = array_filter(array_map('trim', explode(',', $tags_string)));
        $image_url = trim($_POST['image_url'] ?? '');
        
        // Değişiklik tespiti
        $new_data = [
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'category' => $category,
            'tags' => $tags_string,
            'image_url' => $image_url
        ];
        
        $changes_detected = ($original_data !== $new_data);
        
        // Validation
        $errors = [];
        
        if (empty($title)) {
            $errors[] = 'Blog başlığı zorunludur.';
        }
        
        if (empty($excerpt)) {
            $errors[] = 'Blog özeti zorunludur.';
        }
        
        if (empty($content)) {
            $errors[] = 'Blog içeriği zorunludur.';
        }
        
        if (empty($category)) {
            $errors[] = 'Kategori seçimi zorunludur.';
        }
        
        if (empty($errors)) {
            if (!$changes_detected) {
                set_flash_message('info', 'Herhangi bir değişiklik yapılmadı.');
            } else {
                try {
                    $update_data = [
                        'title' => $title,
                        'excerpt' => $excerpt,
                        'content' => $content,
                        'category' => $category,
                        'tags' => $tags,
                        'image_url' => $image_url
                    ];
                    
                    // Veritabanı UPDATE işlemi
                    $update_response = database()->update('blogs', $update_data, ['id' => $blog_id]);
                    
                    if ($update_response) {
                        set_flash_message('success', 'Blog yazısı başarıyla güncellendi.');
                        
                        // Devam et mi yoksa listeye dön mü?
                        if (isset($_POST['save_and_continue'])) {
                            header('Location: blog-edit.php?id=' . $blog_id);
                        } else {
                            header('Location: blogs.php');
                        }
                        exit;
                    } else {
                        throw new Exception('Blog güncelleme işlemi başarısız veya veri değişmedi.');
                    }
                    
                } catch (Exception $e) {
                    error_log("Blog update error: " . $e->getMessage());
                    $errors[] = 'Blog yazısı güncellenirken bir hata oluştu: ' . $e->getMessage();
                }
            }
        }
        
        // Hataları flash message olarak sakla
        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
        }
    }
}

// Header dahil et
include 'includes/header.php';
?>

<!-- Blog Edit Content -->
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Blog Yazısı Düzenle
            </h1>
            <p class="text-gray-600">
                <span class="font-semibold"><?= htmlspecialchars($blog['title']) ?></span> yazısının bilgilerini güncelleyin
            </p>
        </div>
        <div class="mt-4 lg:mt-0 flex space-x-3">
            <a href="blogs.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Blog Listesi
            </a>
            <a href="../blog-detail.php?id=<?= $blog_id ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 font-medium rounded-lg hover:bg-blue-200 transition-colors">
                <i class="fas fa-external-link-alt mr-2"></i>
                Önizle
            </a>
        </div>
    </div>

    <!-- Blog Info Card -->
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-purple-500 rounded-xl flex items-center justify-center">
                <i class="fas fa-file-alt text-white text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($blog['title']) ?></h3>
                <p class="text-gray-600">Blog ID: #<?= $blog_id ?></p>
                <p class="text-sm text-gray-500">
                    Son güncelleme: <?= date('d.m.Y H:i', strtotime($blog['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php
    $flash_message = get_flash_message();
    if ($flash_message):
        $bg_colors = [
            'success' => 'bg-green-50 border-green-200',
            'error' => 'bg-red-50 border-red-200',
            'info' => 'bg-blue-50 border-blue-200'
        ];
        $text_colors = [
            'success' => 'text-green-800',
            'error' => 'text-red-800',
            'info' => 'text-blue-800'
        ];
        $icons = [
            'success' => 'fa-check-circle',
            'error' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle'
        ];
        $icon_colors = [
            'success' => 'text-green-500',
            'error' => 'text-red-500',
            'info' => 'text-blue-500'
        ];
        
        $type = $flash_message['type'];
        $bg_color = $bg_colors[$type] ?? 'bg-gray-50 border-gray-200';
        $text_color = $text_colors[$type] ?? 'text-gray-800';
        $icon = $icons[$type] ?? 'fa-info';
        $icon_color = $icon_colors[$type] ?? 'text-gray-500';
    ?>
        <div class="<?= $bg_color ?> border rounded-xl p-4 flex items-start">
            <i class="fas <?= $icon ?> <?= $icon_color ?> mr-3 mt-0.5"></i>
            <div class="<?= $text_color ?> font-medium"><?= nl2br(htmlspecialchars($flash_message['message'])) ?></div>
        </div>
    <?php endif; ?>

    <!-- Blog Edit Form -->
    <form method="POST" class="space-y-6" id="blogEditForm">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Title and Excerpt -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Temel Bilgiler</h3>
                        <p class="text-gray-600 text-sm mt-1">Blog yazısının başlığı ve kısa açıklaması</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-heading mr-2"></i>Blog Başlığı *
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   required
                                   value="<?= htmlspecialchars($blog['title']) ?>"
                                   placeholder="Örn: 2025 Yaz Ayakkabı Trendleri"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors text-lg">
                            <div class="text-xs text-gray-500 mt-1">Orijinal: <?= htmlspecialchars($original_data['title']) ?></div>
                        </div>

                        <!-- Excerpt -->
                        <div>
                            <label for="excerpt" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-2"></i>Kısa Açıklama (Özet) *
                            </label>
                            <textarea id="excerpt" 
                                      name="excerpt" 
                                      required
                                      rows="3"
                                      placeholder="Blog yazısının kısa özetini yazın (SEO için önemli)..."
                                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($blog['excerpt'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Bu metin blog listesinde ve arama motorlarında görünecek</p>
                        </div>
                    </div>
                </div>

                <!-- Content Editor -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Blog İçeriği</h3>
                        <p class="text-gray-600 text-sm mt-1">Blog yazısının ana içeriğini buraya yazın</p>
                    </div>
                    <div class="p-6">
                        <div>
                            <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-edit mr-2"></i>İçerik *
                            </label>
                            <textarea id="content" 
                                      name="content" 
                                      required
                                      rows="12"
                                      placeholder="Blog yazısının ana içeriğini buraya yazın. HTML etiketleri kullanabilirsiniz..."
                                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors font-mono text-sm"><?= htmlspecialchars($blog['content'] ?? '') ?></textarea>
                            <div class="mt-2 text-xs text-gray-500 space-y-1">
                                <p><strong>HTML Etiketleri:</strong> &lt;p&gt;, &lt;h3&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;br&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;</p>
                                <p><strong>Örnek:</strong> &lt;h3&gt;Alt Başlık&lt;/h3&gt;&lt;p&gt;Paragraf metni...&lt;/p&gt;</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Category and Tags -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Kategori & Etiketler</h3>
                        <p class="text-gray-600 text-sm mt-1">Blog yazısını kategorilendirin</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tag mr-2"></i>Kategori *
                            </label>
                            <select id="category" 
                                    name="category" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                <option value="">Kategori Seçin</option>
                                <option value="Trendler" <?= ($blog['category'] ?? '') === 'Trendler' ? 'selected' : '' ?>>Trendler</option>
                                <option value="Sağlık" <?= ($blog['category'] ?? '') === 'Sağlık' ? 'selected' : '' ?>>Sağlık</option>
                                <option value="Moda" <?= ($blog['category'] ?? '') === 'Moda' ? 'selected' : '' ?>>Moda</option>
                                <option value="Bakım" <?= ($blog['category'] ?? '') === 'Bakım' ? 'selected' : '' ?>>Bakım</option>
                                <option value="Teknoloji" <?= ($blog['category'] ?? '') === 'Teknoloji' ? 'selected' : '' ?>>Teknoloji</option>
                                <option value="Yaşam" <?= ($blog['category'] ?? '') === 'Yaşam' ? 'selected' : '' ?>>Yaşam</option>
                            </select>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label for="tags" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tags mr-2"></i>Etiketler
                            </label>
                            <input type="text" 
                                   id="tags" 
                                   name="tags" 
                                   value="<?= htmlspecialchars($original_data['tags']) ?>"
                                   placeholder="ayakkabı, moda, trend"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Etiketleri virgülle ayırın</p>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Kapak Resmi</h3>
                        <p class="text-gray-600 text-sm mt-1">Blog yazısı için kapak resmi</p>
                    </div>
                    <div class="p-6">
                        <div>
                            <label for="image_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-image mr-2"></i>Resim URL'si
                            </label>
                            <input type="url" 
                                   id="image_url" 
                                   name="image_url" 
                                   value="<?= htmlspecialchars($blog['image_url'] ?? '') ?>"
                                   placeholder="https://example.com/image.jpg"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Unsplash, Pexels gibi sitelerden resim URL'si kullanabilirsiniz</p>
                        </div>
                        
                        <!-- Image Preview -->
                        <div id="image-preview" class="mt-4 <?= empty($blog['image_url']) ? 'hidden' : '' ?>">
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <img id="preview-img" src="<?= htmlspecialchars($blog['image_url'] ?? '') ?>" alt="Önizleme" class="w-full h-32 object-cover">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <button type="submit" 
                    name="save_and_return"
                    class="flex-1 sm:flex-none sm:min-w-[200px] bg-primary-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-primary-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Kaydet ve Listeye Dön
            </button>
            
            <button type="submit" 
                    name="save_and_continue"
                    class="flex-1 sm:flex-none sm:min-w-[200px] bg-green-600 text-white font-semibold py-3 px-8 rounded-xl hover:bg-green-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center">
                <i class="fas fa-check mr-2"></i>
                Kaydet ve Düzenlemeye Devam Et
            </button>
            
            <a href="blogs.php" 
               class="flex-1 sm:flex-none sm:min-w-[150px] bg-gray-100 text-gray-700 font-semibold py-3 px-8 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center">
                <i class="fas fa-times mr-2"></i>
                İptal
            </a>
        </div>
    </form>
</div>

<!-- Enhanced JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#blogEditForm');
    const titleInput = document.querySelector('#title');
    const excerptInput = document.querySelector('#excerpt');
    const contentInput = document.querySelector('#content');
    const categorySelect = document.querySelector('#category');
    
    // Orijinal değerler
    const originalValues = {
        title: <?= json_encode($original_data['title']) ?>,
        excerpt: <?= json_encode($original_data['excerpt']) ?>,
        content: <?= json_encode($original_data['content']) ?>,
        category: <?= json_encode($original_data['category']) ?>,
        tags: <?= json_encode($original_data['tags']) ?>,
        image_url: <?= json_encode($original_data['image_url']) ?>
    };
    
    // Değişiklik tespiti
    function detectChanges() {
        const currentValues = {
            title: titleInput.value.trim(),
            excerpt: excerptInput.value.trim(),
            content: contentInput.value.trim(),
            category: categorySelect.value,
            tags: document.querySelector('#tags').value.trim(),
            image_url: document.querySelector('#image_url').value.trim()
        };
        
        const hasChanges = JSON.stringify(currentValues) !== JSON.stringify(originalValues);
        
        // Submit butonlarını güncelle
        const saveButtons = document.querySelectorAll('button[type="submit"]');
        saveButtons.forEach(btn => {
            if (hasChanges) {
                btn.classList.remove('opacity-50');
                btn.disabled = false;
            } else {
                btn.classList.add('opacity-50');
                btn.disabled = true;
            }
        });
        
        return hasChanges;
    }
    
    // Form alanlarını izle
    [titleInput, excerptInput, contentInput, categorySelect, 
     document.querySelector('#tags'), document.querySelector('#image_url')].forEach(element => {
        element.addEventListener('input', detectChanges);
        element.addEventListener('change', detectChanges);
    });
    
    
    // Sayfa yüklendiğinde kontrol et
    detectChanges();
    
    // Image preview
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
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            if (detectChanges()) {
                form.querySelector('button[name="save_and_continue"]').click();
            }
        }
    });
    
    // Auto-resize textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Initial resize
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    });
    
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
    
    addCharacterCounter(titleInput, 100);
    addCharacterCounter(excerptInput, 200);
    
    // Form submission loading
    form.addEventListener('submit', function(e) {
        const submitBtn = e.submitter;
        const btnText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i>Kaydediliyor...';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = btnText;
        }, 5000);
    });
    
    // Sayfa terk etme uyarısı
    let formSubmitted = false;
    form.addEventListener('submit', function() {
        formSubmitted = true;
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (detectChanges() && !formSubmitted) {
            e.preventDefault();
            e.returnValue = 'Kaydedilmemiş değişiklikler var. Sayfadan ayrılmak istediğinizden emin misiniz?';
        }
    });
});
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
