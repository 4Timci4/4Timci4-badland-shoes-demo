<?php
/**
 * Blog Yönetimi Sayfası
 * Modern, kullanıcı dostu blog yönetim paneli
 */

require_once 'config/auth.php';
check_admin_auth();

// Veritabanı bağlantısı
require_once '../config/database.php';
require_once '../services/BlogService.php';

// Sayfa bilgileri
$page_title = 'Blog Yönetimi';
$breadcrumb_items = [
    ['title' => 'Blog Yönetimi', 'url' => '#', 'icon' => 'fas fa-edit']
];

// POST işlemleri
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('error', 'Güvenlik hatası. Lütfen tekrar deneyin.');
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'delete':
                $blog_id = intval($_POST['blog_id'] ?? 0);
                
                if ($blog_id > 0) {
                    try {
                        $response = supabase()->request('blogs?id=eq.' . $blog_id, 'DELETE');
                        if (!empty($response)) {
                            set_flash_message('success', 'Blog yazısı başarıyla silindi.');
                        } else {
                            set_flash_message('error', 'Blog yazısı silinirken bir hata oluştu.');
                        }
                    } catch (Exception $e) {
                        set_flash_message('error', 'Blog yazısı silinirken bir hata oluştu: ' . $e->getMessage());
                    }
                } else {
                    set_flash_message('error', 'Geçersiz blog ID.');
                }
                break;
                
            case 'toggle_status':
                $blog_id = intval($_POST['blog_id'] ?? 0);
                $new_status = $_POST['status'] === 'published' ? 'draft' : 'published';
                
                if ($blog_id > 0) {
                    try {
                        $response = supabase()->request('blogs?id=eq.' . $blog_id, 'PATCH', [
                            'status' => $new_status
                        ]);
                        if (!empty($response)) {
                            $status_text = $new_status === 'published' ? 'yayınlandı' : 'taslağa alındı';
                            set_flash_message('success', 'Blog yazısı ' . $status_text . '.');
                        } else {
                            set_flash_message('error', 'Blog durumu değiştirilirken bir hata oluştu.');
                        }
                    } catch (Exception $e) {
                        set_flash_message('error', 'Blog durumu değiştirilirken bir hata oluştu: ' . $e->getMessage());
                    }
                }
                break;
        }
        
        // Redirect to prevent form resubmission
        header('Location: blogs.php');
        exit;
    }
}

// Blog yazılarını getir
try {
    $blogService = new BlogService();
    $blogs = $blogService->getAllBlogs(50);
} catch (Exception $e) {
    $blogs = [];
    set_flash_message('error', 'Blog yazıları yüklenirken bir hata oluştu: ' . $e->getMessage());
}

// Header dahil et
include 'includes/header.php';
?>

<!-- Blog Management Content -->
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Blog Yönetimi</h1>
            <p class="text-gray-600">Blog yazılarını yönetin ve düzenleyin</p>
        </div>
        <div class="mt-4 lg:mt-0 flex space-x-3">
            <a href="blog-add.php" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Yeni Blog Yazısı
            </a>
            <a href="../blog.php" target="_blank" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                <i class="fas fa-external-link-alt mr-2"></i>
                Blog Sayfasını Gör
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

    <!-- Blog Statistics -->
    <?php if (!empty($blogs)): ?>
        <?php
        $total_blogs = count($blogs);
        $published_blogs = count(array_filter($blogs, function($blog) { 
            return ($blog['status'] ?? 'published') === 'published'; 
        }));
        $draft_blogs = $total_blogs - $published_blogs;
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-xl">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Toplam Blog</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $total_blogs ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-xl">
                        <i class="fas fa-eye text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Yayınlanan</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $published_blogs ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-xl">
                        <i class="fas fa-edit text-orange-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Taslak</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $draft_blogs ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Blog List -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Blog Yazıları</h3>
                    <p class="text-gray-600 text-sm">Tüm blog yazılarınızın listesi</p>
                </div>
                <div class="text-sm text-gray-500">
                    Toplam: <span class="font-semibold"><?= count($blogs) ?></span> yazı
                </div>
            </div>
        </div>

        <?php if (!empty($blogs)): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Blog Yazısı</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Durum</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tarih</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($blogs as $blog): 
                            $blog = (array) $blog;
                            $status = $blog['status'] ?? 'published';
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                            <?php if (!empty($blog['image_url'])): ?>
                                                <img src="<?= htmlspecialchars($blog['image_url']) ?>" 
                                                     alt="<?= htmlspecialchars($blog['title']) ?>"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yMCAyNEgyNCIgc3Ryb2tlPSIjOEI5OEE1IiBzdHJva2Utd2lkdGg9IjIiLz4KPHA+'" >
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-semibold text-gray-900 truncate">
                                                <?= htmlspecialchars($blog['title']) ?>
                                            </h4>
                                            <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                                                <?= htmlspecialchars($blog['excerpt'] ?? '') ?>
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">ID: #<?= $blog['id'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-tag mr-1"></i>
                                        <?= htmlspecialchars($blog['category'] ?? 'Genel') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $status ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium transition-colors
                                            <?= $status === 'published' ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' ?>">
                                            <i class="fas <?= $status === 'published' ? 'fa-eye' : 'fa-eye-slash' ?> mr-1"></i>
                                            <?= $status === 'published' ? 'Yayında' : 'Taslak' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($blog['created_at'])) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('H:i', strtotime($blog['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="../blog-detail.php?id=<?= $blog['id'] ?>" 
                                           target="_blank"
                                           class="inline-flex items-center justify-center w-20 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                                            <i class="fas fa-eye mr-1"></i>
                                            Gör
                                        </a>
                                        
                                        <a href="blog-edit.php?id=<?= $blog['id'] ?>" 
                                           class="inline-flex items-center justify-center w-20 px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium">
                                            <i class="fas fa-edit mr-1"></i>
                                            Düzenle
                                        </a>
                                        
                                        <form method="POST" class="inline-block" onsubmit="return confirm('Bu blog yazısını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center w-20 px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                                                <i class="fas fa-trash mr-1"></i>
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Henüz blog yazısı yok</h3>
                <p class="text-gray-600 mb-6">İlk blog yazınızı oluşturarak başlayın</p>
                <a href="blog-add.php" 
                   class="inline-flex items-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    İlk Blog Yazısını Ekle
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for enhanced UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission loading states
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.onclick) {
                const btnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-1"></i>İşleniyor...';
                
                // Re-enable after 3 seconds as fallback
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = btnText;
                }, 3000);
            }
        });
    });
});
</script>

<?php
// Footer dahil et
include 'includes/footer.php';
?>
