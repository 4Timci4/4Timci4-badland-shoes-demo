<?php


require_once 'config/auth.php';
check_admin_auth();


require_once '../config/database.php';
require_once '../services/ContactService.php';

$contactService = new ContactService();


$page_title = 'Gelen Mesajlar';
$breadcrumb_items = [
    ['title' => 'İletişim', 'url' => '#', 'icon' => 'fas fa-envelope'],
    ['title' => 'Gelen Mesajlar', 'url' => 'messages.php', 'icon' => 'fas fa-inbox']
];


$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';


if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['message_id'])) {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $message_id = intval($_POST['message_id']);
        if ($contactService->deleteMessage($message_id)) {
            set_flash_message('success', 'Mesaj başarıyla silindi.');
        } else {
            set_flash_message('error', 'Mesaj silinirken bir hata oluştu.');
        }
        header('Location: messages.php' . ($search ? '?search=' . urlencode($search) : ''));
        exit;
    }
}


$result = $contactService->getAllMessages($limit, $offset, $search);
$messages = $result['messages'];
$total = $result['total'];
$total_pages = ceil($total / $limit);


$additional_css = [];
$additional_js = [];


include 'includes/header.php';
?>

<!-- Messages Content -->
<div class="space-y-6">

    <!-- Page Header -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Gelen Mesajlar</h1>
                <p class="text-gray-600">İletişim formundan gelen tüm mesajları görüntüleyin ve yönetin</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="bg-primary-100 text-primary-700 px-3 py-1 rounded-full text-sm font-medium">
                    <?= number_format($total) ?> Mesaj
                </span>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        placeholder="İsim, email veya konu ara..."
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                </div>
            </div>
            <button type="submit"
                class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-search mr-2"></i>
                Ara
            </button>
            <?php if ($search): ?>
                <a href="messages.php"
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Temizle
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Messages List -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <?php if (!empty($messages)): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($messages as $message): ?>
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-primary-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <?= htmlspecialchars($message['name']) ?>
                                        </h3>
                                        <p class="text-gray-600">
                                            <i class="fas fa-envelope mr-1"></i>
                                            <?= htmlspecialchars($message['email']) ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">
                                            <?php
                                            $date = new DateTime($message['created_at']);
                                            echo $date->format('d.m.Y H:i');
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h4 class="font-medium text-gray-900 mb-2">
                                        <i class="fas fa-comment-alt mr-2 text-gray-400"></i>
                                        <?= htmlspecialchars($message['subject']) ?>
                                    </h4>
                                    <p class="text-gray-600 line-clamp-2">
                                        <?= htmlspecialchars(substr($message['message'], 0, 200)) ?>        <?= strlen($message['message']) > 200 ? '...' : '' ?>
                                    </p>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <button onclick="showMessageModal(<?= htmlspecialchars(json_encode($message)) ?>)"
                                        class="text-primary-600 hover:text-primary-700 font-medium text-sm transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        Detayları Gör
                                    </button>
                                    <button
                                        onclick="deleteMessage(<?= $message['id'] ?>, '<?= htmlspecialchars($message['name']) ?>')"
                                        class="text-red-600 hover:text-red-700 font-medium text-sm transition-colors">
                                        <i class="fas fa-trash mr-1"></i>
                                        Sil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    <?= $search ? 'Arama sonucu bulunamadı' : 'Henüz mesaj yok' ?>
                </h3>
                <p class="text-gray-600">
                    <?= $search ? 'Farklı bir arama terimi deneyin.' : 'İletişim formundan gelen mesajlar burada görünecek.' ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Toplam <span class="font-medium"><?= number_format($total) ?></span> mesajdan
                    <span class="font-medium"><?= number_format($offset + 1) ?></span> -
                    <span class="font-medium"><?= number_format(min($offset + $limit, $total)) ?></span> arası gösteriliyor
                </div>
                <nav class="flex items-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                            class="px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                            class="px-3 py-2 <?= $i === $page ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' ?> rounded-lg transition-colors">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                            class="px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Message Detail Modal -->
<div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">Mesaj Detayları</h3>
                    <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gönderen</label>
                            <p id="modal-name" class="text-gray-900 font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-posta</label>
                            <p id="modal-email" class="text-gray-900"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konu</label>
                        <p id="modal-subject" class="text-gray-900 font-medium"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gönderim Tarihi</label>
                        <p id="modal-date" class="text-gray-600"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mesaj</label>
                        <div id="modal-message" class="bg-gray-50 rounded-lg p-4 text-gray-900 whitespace-pre-wrap">
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex justify-end space-x-3">
                    <button onclick="closeMessageModal()"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Kapat
                    </button>
                    <button onclick="deleteCurrentMessage()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>
                        Mesajı Sil
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" class="hidden">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="message_id" id="deleteMessageId">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
</form>

<script>
    let currentMessage = null;

    function showMessageModal(message) {
        currentMessage = message;

        document.getElementById('modal-name').textContent = message.name;
        document.getElementById('modal-email').textContent = message.email;
        document.getElementById('modal-subject').textContent = message.subject;
        document.getElementById('modal-message').textContent = message.message;

        const date = new Date(message.created_at);
        document.getElementById('modal-date').textContent = date.toLocaleString('tr-TR');

        document.getElementById('messageModal').classList.remove('hidden');
    }

    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        currentMessage = null;
    }

    function deleteMessage(messageId, messageName) {
        if (confirm(`"${messageName}" adlı kişiden gelen mesajı silmek istediğinize emin misiniz?\n\nBu işlem geri alınamaz.`)) {
            document.getElementById('deleteMessageId').value = messageId;
            document.getElementById('deleteForm').submit();
        }
    }

    function deleteCurrentMessage() {
        if (currentMessage) {
            deleteMessage(currentMessage.id, currentMessage.name);
        }
    }


    document.getElementById('messageModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeMessageModal();
        }
    });


    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !document.getElementById('messageModal').classList.contains('hidden')) {
            closeMessageModal();
        }
    });
</script>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php

include 'includes/footer.php';
?>