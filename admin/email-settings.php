<?php
/**
 * Admin Panel - E-posta Ayarları
 * SMTP ayarlarını yönetme ve test etme sayfası
 */

require_once 'config/auth.php';
check_admin_auth();

// Gerekli servisleri dahil et
require_once '../config/database.php';
require_once '../services/EmailService.php';
require_once 'includes/product-edit-helpers.php'; // Helper dosyasını dahil et

$emailService = new EmailService();

// Sayfa bilgileri
$page_title = 'E-posta Ayarları';
$breadcrumb_items = [
    ['title' => 'Ayarlar', 'url' => '#', 'icon' => 'fas fa-cog'],
    ['title' => 'E-posta Ayarları', 'url' => 'email-settings.php', 'icon' => 'fas fa-at']
];

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        
        // Ayarları güncelleme
        if (isset($_POST['action']) && $_POST['action'] === 'update_email_settings') {
            $settings = [
                'mail_host' => $_POST['mail_host'] ?? '',
                'mail_port' => $_POST['mail_port'] ?? '',
                'mail_username' => $_POST['mail_username'] ?? '',
                'mail_password' => $_POST['mail_password'] ?? '',
                'mail_encryption' => $_POST['mail_encryption'] ?? 'tls',
                'mail_from_address' => $_POST['mail_from_address'] ?? '',
                'mail_from_name' => $_POST['mail_from_name'] ?? ''
            ];
            
            if ($emailService->updateEmailSettings($settings)) {
                set_flash_message('success', 'E-posta ayarları başarıyla güncellendi.');
            } else {
                set_flash_message('error', 'E-posta ayarları güncellenirken bir hata oluştu.');
            }
            header('Location: email-settings.php');
            exit;
        }
        
        // Test e-postası gönderme
        if (isset($_POST['action']) && $_POST['action'] === 'send_test_email') {
            $test_email_address = trim($_POST['test_email_address'] ?? '');
            if (filter_var($test_email_address, FILTER_VALIDATE_EMAIL)) {
                $result = $emailService->sendTestEmail($test_email_address);
                if ($result['success']) {
                    set_flash_message('success', 'Test e-postası başarıyla gönderildi: ' . $test_email_address);
                } else {
                    set_flash_message('error', 'Test e-postası gönderilemedi: ' . $result['message']);
                }
            } else {
                set_flash_message('error', 'Lütfen geçerli bir test e-posta adresi girin.');
            }
            header('Location: email-settings.php');
            exit;
        }
    }
}

// Mevcut ayarları getir
$email_settings = $emailService->getEmailSettings();

// Header dahil et
include 'includes/header.php';
?>

<!-- Email Settings Content -->
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">E-posta Ayarları</h1>
                <p class="text-gray-600">Sistemden gönderilecek e-postalar için SMTP ayarlarını yapılandırın.</p>
            </div>
        </div>
    </div>

    <?php render_flash_message(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- SMTP Settings Form -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <form method="POST">
                <input type="hidden" name="action" value="update_email_settings">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">SMTP Sunucu Ayarları</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mail Sunucusu (Host)</label>
                                <input type="text" name="mail_host" value="<?= htmlspecialchars($email_settings['mail_host'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Port</label>
                                <input type="number" name="mail_port" value="<?= htmlspecialchars($email_settings['mail_port'] ?? '587') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı Adı</label>
                            <input type="text" name="mail_username" value="<?= htmlspecialchars($email_settings['mail_username'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Şifre</label>
                            <input type="password" name="mail_password" value="<?= htmlspecialchars($email_settings['mail_password'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Şifreleme Türü</label>
                            <select name="mail_encryption" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="tls" <?= ($email_settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($email_settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 border-t border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Gönderen Bilgileri</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gönderen E-posta</label>
                            <input type="email" name="mail_from_address" value="<?= htmlspecialchars($email_settings['mail_from_address'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gönderen Adı</label>
                            <input type="text" name="mail_from_name" value="<?= htmlspecialchars($email_settings['mail_from_name'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50">
                    <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium">
                        <i class="fas fa-save mr-2"></i>Ayarları Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Test Email Form -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <form method="POST">
                <input type="hidden" name="action" value="send_test_email">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">SMTP Bağlantısını Test Et</h3>
                    <p class="text-sm text-gray-600 mb-4">Mevcut ayarlarla bir test e-postası göndererek yapılandırmanın doğru olduğundan emin olun.</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alıcı E-posta Adresi</label>
                        <input type="email" name="test_email_address" required class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="test@example.com">
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 bg-gray-50">
                    <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                        <i class="fas fa-paper-plane mr-2"></i>Test E-postası Gönder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Footer dahil et
include 'includes/footer.php';
?>