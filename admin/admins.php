<?php


require_once 'config/auth.php';
require_once '../services/AdminAuthService.php';


check_admin_auth();

$authService = new AdminAuthService();


$message = '';
$message_type = '';


if (isset($_POST['delete_admin']) && isset($_POST['admin_id'])) {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $result = $authService->deleteAdmin($_POST['admin_id']);
        if (isset($result['success'])) {
            $message = 'Admin başarıyla silindi!';
            $message_type = 'success';
        } else {
            $message = $result['error'] ?? 'Admin silinemedi!';
            $message_type = 'error';
        }
    } else {
        $message = 'Güvenlik hatası!';
        $message_type = 'error';
    }
}


$admins = $authService->getAllAdmins();
$current_admin = $authService->getCurrentAdmin();


$page_title = 'Admin Yönetimi';
$breadcrumb = get_breadcrumb([
    ['title' => 'Admin Yönetimi', 'url' => '', 'icon' => 'fas fa-users-cog']
]);

include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Yönetimi</h1>
                    <p class="mt-2 text-sm text-gray-600">Sistem yöneticilerini görüntüleyin ve yönetin</p>
                </div>
                <div class="flex space-x-4">
                    <a href="admin-add.php"
                        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-medium flex items-center transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Yeni Admin Ekle
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="mb-6">
                <div
                    class="<?= $message_type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800' ?> rounded-lg p-4 flex items-center">
                    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> mr-3"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Toplam Admin</h3>
                        <p class="text-2xl font-bold text-blue-600"><?= count($admins) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Aktif Admin</h3>
                        <p class="text-2xl font-bold text-green-600">
                            <?= count(array_filter($admins, fn($admin) => $admin['is_active'])) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-purple-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Online</h3>
                        <p class="text-2xl font-bold text-purple-600">1</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Admin Kullanıcıları</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Admin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Son Giriş</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kayıt Tarihi</th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($admins)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">Henüz admin kullanıcısı bulunmuyor</p>
                                    <p class="text-sm">İlk admin kullanıcısını oluşturmak için yukarıdaki butonu kullanın
                                    </p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gradient-to-r from-primary-500 to-primary-600 flex items-center justify-center">
                                                    <span class="text-white font-medium text-sm">
                                                        <?= strtoupper(substr($admin['full_name'] ?? $admin['username'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($admin['full_name'] ?? $admin['username']) ?>
                                                    <?php if ($admin['id'] == $current_admin['id']): ?>
                                                        <span
                                                            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            Siz
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-sm text-gray-500">@<?= htmlspecialchars($admin['username']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($admin['is_active']): ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                Aktif
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                Pasif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php if ($admin['last_login_at']): ?>
                                            <div class="flex flex-col">
                                                <span><?= date('d.m.Y', strtotime($admin['last_login_at'])) ?></span>
                                                <span
                                                    class="text-xs text-gray-500"><?= date('H:i', strtotime($admin['last_login_at'])) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">Hiç giriş yapmadı</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span><?= date('d.m.Y', strtotime($admin['created_at'])) ?></span>
                                            <span
                                                class="text-xs text-gray-500"><?= date('H:i', strtotime($admin['created_at'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="admin-edit.php?id=<?= $admin['id'] ?>"
                                                class="text-primary-600 hover:text-primary-900 transition-colors"
                                                title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <?php if ($admin['id'] != $current_admin['id']): ?>
                                                <button
                                                    onclick="confirmDelete(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['username']) ?>')"
                                                    class="text-red-600 hover:text-red-900 transition-colors" title="Sil">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 m-4 max-w-sm w-full">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-gray-900">Admin Sil</h3>
            </div>
        </div>
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                <span id="deleteAdminName"></span> kullanıcısını silmek istediğinizden emin misiniz?
                Bu işlem geri alınamaz.
            </p>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeDeleteModal()"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                İptal
            </button>
            <form id="deleteForm" method="POST" class="inline">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="admin_id" id="deleteAdminId">
                <button type="submit" name="delete_admin"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors">
                    Sil
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete(adminId, adminName) {
        document.getElementById('deleteAdminId').value = adminId;
        document.getElementById('deleteAdminName').textContent = adminName;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteModal').classList.remove('flex');
    }


    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });


    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>

<?php include 'includes/footer.php'; ?>