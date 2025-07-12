<?php


require_once 'config/auth.php';
check_admin_auth();


require_once '../config/database.php';


$page_title = 'Yeni Blog Yazısı';
$breadcrumb_items = [
    ['title' => 'Blog Yazıları', 'url' => 'blogs.php', 'icon' => 'fas fa-edit'],
    ['title' => 'Yeni Blog Yazısı', 'url' => '#', 'icon' => 'fas fa-plus']
];


if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? '';
        $category = trim($_POST['category'] ?? '');
        $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
        $image_url = trim($_POST['image_url'] ?? '');


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
            try {
                $blog_data = [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'category' => $category,
                    'tags' => $tags,
                    'image_url' => $image_url
                ];

                try {
                    $insert_response = database()->insert('blogs', $blog_data);

                    if ($insert_response) {
                        set_flash_message('success', 'Blog yazısı başarıyla eklendi.');
                        header('Location: blogs.php');
                        exit;
                    } else {
                        throw new Exception('Blog ekleme işlemi başarısız oldu.');
                    }
                } catch (Exception $e) {
                    error_log("Blog add error: " . $e->getMessage());
                    $errors[] = 'Blog yazısı eklenirken bir hata oluştu: ' . $e->getMessage();
                }
            } catch (Exception $e) {
                error_log("Blog save error: " . $e->getMessage());
                $errors[] = 'Sistem hatası oluştu. Lütfen tekrar deneyin.';
            }
        } else {
            set_flash_message('error', implode('<br>', $errors));
        }
    }
}


include 'includes/header.php';
?>

<!-- Blog Add Content -->
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Yeni Blog Yazısı</h1>
            <p class="text-gray-600">Yeni bir blog yazısı oluşturun ve yayınlayın</p>
        </div>
        <div class="mt-4 lg:mt-0">
            <a href="blogs.php"
                class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Blog Listesine Dön
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

    <!-- Blog Form -->
    <form method="POST" class="space-y-6">
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
                            <input type="text" id="title" name="title" required
                                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                placeholder="Örn: 2025 Yaz Ayakkabı Trendleri"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors text-lg">
                        </div>

                        <!-- Excerpt -->
                        <div>
                            <label for="excerpt" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-2"></i>Kısa Açıklama (Özet) *
                            </label>
                            <textarea id="excerpt" name="excerpt" required rows="3"
                                placeholder="Blog yazısının kısa özetini yazın (SEO için önemli)..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors resize-none"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Bu metin blog listesinde ve arama motorlarında
                                görünecek</p>
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
                            <textarea id="content" name="content" required rows="12"
                                placeholder="Blog yazısının ana içeriğini buraya yazın. HTML etiketleri kullanabilirsiniz..."
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors font-mono text-sm"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                            <div class="mt-2 text-xs text-gray-500 space-y-1">
                                <p><strong>HTML Etiketleri:</strong> &lt;p&gt;, &lt;h3&gt;, &lt;strong&gt;, &lt;em&gt;,
                                    &lt;br&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;</p>
                                <p><strong>Örnek:</strong> &lt;h3&gt;Alt Başlık&lt;/h3&gt;&lt;p&gt;Paragraf
                                    metni...&lt;/p&gt;</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <button type="submit"
                            class="w-full bg-primary-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-primary-700 transition-colors flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>
                            Blog Yazısını Kaydet
                        </button>
                    </div>
                </div>

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
                            <select id="category" name="category" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                                <option value="">Kategori Seçin</option>
                                <option value="Trendler" <?= ($_POST['category'] ?? '') === 'Trendler' ? 'selected' : '' ?>>Trendler</option>
                                <option value="Sağlık" <?= ($_POST['category'] ?? '') === 'Sağlık' ? 'selected' : '' ?>>
                                    Sağlık</option>
                                <option value="Moda" <?= ($_POST['category'] ?? '') === 'Moda' ? 'selected' : '' ?>>Moda
                                </option>
                                <option value="Bakım" <?= ($_POST['category'] ?? '') === 'Bakım' ? 'selected' : '' ?>>Bakım
                                </option>
                                <option value="Teknoloji" <?= ($_POST['category'] ?? '') === 'Teknoloji' ? 'selected' : '' ?>>Teknoloji</option>
                                <option value="Yaşam" <?= ($_POST['category'] ?? '') === 'Yaşam' ? 'selected' : '' ?>>Yaşam
                                </option>
                            </select>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label for="tags" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tags mr-2"></i>Etiketler
                            </label>
                            <input type="text" id="tags" name="tags"
                                value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>"
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
                            <input type="url" id="image_url" name="image_url"
                                value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>" placeholder="https:
                                   class=" w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2
                                focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Unsplash, Pexels gibi sitelerden resim URL'si
                                kullanabilirsiniz</p>
                        </div>

                        <!-- Image Preview -->
                        <div id="image-preview" class="mt-4 hidden">
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <img id="preview-img" src="" alt="Önizleme" class="w-full h-32 object-cover">
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

        const imageUrlInput = document.getElementById('image_url');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');

        imageUrlInput.addEventListener('input', function () {
            const url = this.value.trim();
            if (url && (url.startsWith('http:
            previewImg.src = url;
            previewImg.onload = function () {
                imagePreview.classList.remove('hidden');
            };
            previewImg.onerror = function () {
                imagePreview.classList.add('hidden');
            };
        } else {
            imagePreview.classList.add('hidden');
        }
    });


    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });


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

    addCharacterCounter(document.getElementById('title'), 100);
    addCharacterCounter(document.getElementById('excerpt'), 200);
});
</script>

<?php

include 'includes/footer.php';
?>