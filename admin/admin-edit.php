<?php
/**
 * Admin Düzenleme Sayfası
 * 
 * Mevcut admin kullanıcısını düzenleme formu
 */

require_once 'config/auth.php';
require_once '../services/AdminAuthService.php';

// Giriş kontrolü
check_admin_auth();

$authService = new AdminAuthService();

// Admin ID kontrolü
$admin_id = intval($_GET['id'] ?? 0);
if (!$admin_id) {
    header('Location: admins.php');
    exit;
}

// Admin verisini getir
$admin = $authService->getAdminById($admin_id);
if (!$admin) {
    header('Location: admins.php');
    exit;
}

// Form işlemleri
$errors = [];
$success = false;
$form_data = $admin; // Başlangıçta mevcut verilerle doldur

if ($_POST) {
    // CSRF token kontrolü
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        // Form verilerini al
        $form_data = [
            'username' => trim($_POST['username'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'is_active' => isset($_POST['is_active'])
        ];
        
        // Validation
        if (empty($form_data['username'])) {
            $errors[] = 'Kullanıcı adı gereklidir.';
        } elseif (strlen($form_data['username']) < 3) {
            $errors[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $form_data['username'])) {
            $errors[] = 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.';
        }
        
        if (empty($form_data['full_name'])) {
            $errors[] = 'Tam ad gereklidir.';
        }
        
        if (empty($form_data['email'])) {
            $errors[] = 'E-posta adresi gereklidir.';
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta adresi girin.';
        }
        
        // Şifre kontrolü (sadece doldurulmuşsa)
        if (!empty($form_data['password'])) {
            if (strlen($form_data['password']) < 6) {
                $errors[] = 'Şifre en az 6 karakter olmalıdır.';
            }
            
            if ($form_data['password'] !== $form_data['password_confirm']) {
                $errors[] = 'Şifreler eşleşmiyor.';
            }
        }
        
        // Eğer hata yoksa admin güncelle
        if (empty($errors)) {
            $update_data = [
                'username' => $form_data['username'],
                'full_name' => $form_data['full_name'],
                'email' => $form_data['email'],
                'is_active' => $form_data['is_active']
            ];
            
            // Şifre varsa ekle
            if (!empty($form_data['password'])) {
                $update_data['password'] = $form_data['password'];
            }
            
            $result = $authService->updateAdmin($admin_id, $update_data);
            
            if (isset($result['success'])) {
                $success = true;
                // Güncel verileri tekrar getir
                $admin = $authService->getAdminById($admin_id);
                $form_data = $admin;
            } elseif (isset($result['error'])) {
                $errors[] = $result['error'];
            }
        }
    }
}

$current_admin = $authService->getCurrentAdmin();

// Sayfa başlığı ve breadcrumb
$page_title = 'Admin Düzenle';
$breadcrumb = get_breadcrumb([
    ['title' => 'Admin Yönetimi', 'url' => 'admins.php', 'icon' => 'fas fa-users-cog'],
    ['title' => 'Admin Düzenle', 'url' => '', 'icon' => 'fas fa-user-edit']
]);

include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Admin Düzenle</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        <strong><?= htmlspecialchars($admin['full_name'] ?? $admin['username']) ?></strong> adlı yöneticiyi düzenleyin
                    </p>
                </div>
                <a href="admins.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Geri Dön
                </a>
            </div>
        </div>

        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="mb-6">
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 flex items-center">
                    <i class="fas fa-check-circle mr-3"></i>
                    <div>
                        <p class="font-medium">Admin başarıyla güncellendi!</p>
                        <p class="text-sm mt-1">Admin bilgileri başarıyla kaydedildi.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6">
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle mr-3 mt-0.5"></i>
                        <div>
                            <h3 class="font-medium">Aşağıdaki hataları düzeltin:</h3>
                            <ul class="mt-2 text-sm list-disc list-inside space-y-1">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Admin Bilgileri</h2>
            </div>
            
            <form method="POST" class="p-6 space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kullanıcı Adı -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-gray-400"></i>
                            Kullanıcı Adı *
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?= htmlspecialchars($form_data['username'] ?? '') ?>"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                               placeholder="örnek: adminuser">
                        <p class="mt-1 text-xs text-gray-500">Sadece harf, rakam ve alt çizgi kullanın</p>
                    </div>
                    
                    <!-- Tam Ad -->
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-2 text-gray-400"></i>
                            Tam Ad *
                        </label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               value="<?= htmlspecialchars($form_data['full_name'] ?? '') ?>"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                               placeholder="örnek: Ahmet Yılmaz">
                    </div>
                </div>
                
                <!-- E-posta -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-gray-400"></i>
                        E-posta Adresi *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                           placeholder="örnek: admin@example.com">
                </div>
                
                <!-- Şifre Değiştirme Bölümü -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Şifre Değiştir (Opsiyonel)</h3>
                    <p class="text-sm text-gray-600 mb-4">Şifreyi değiştirmek istemiyorsanız bu alanları boş bırakın.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Yeni Şifre -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-400"></i>
                                Yeni Şifre
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-12"
                                       placeholder="En az 6 karakter">
                                <button type="button" 
                                        onclick="togglePassword('password')" 
                                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Şifre Tekrar -->
                        <div>
                            <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-400"></i>
                                Şifre Tekrar
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-12"
                                       placeholder="Şifreyi tekrar girin">
                                <button type="button" 
                                        onclick="togglePassword('password_confirm')" 
                                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-eye" id="password_confirmIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Durum -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-2 text-gray-400"></i>
                        Durum
                    </label>
                    <div class="flex items-center pt-3">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               <?= ($form_data['is_active'] ?? true) ? 'checked' : '' ?>
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Admin aktif olsun
                        </label>
                    </div>
                </div>
                
                <!-- Admin Info -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Admin Bilgileri</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                            <span class="font-medium">Kayıt Tarihi:</span>
                            <?= date('d.m.Y H:i', strtotime($admin['created_at'])) ?>
                        </div>
                        <div>
                            <span class="font-medium">Son Giriş:</span>
                            <?= $admin['last_login_at'] ? date('d.m.Y H:i', strtotime($admin['last_login_at'])) : 'Hiç giriş yapmadı' ?>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="admins.php" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        İptal
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Değişiklikleri Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + 'Icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Real-time password confirmation check
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    
    if (confirm && password !== confirm) {
        this.classList.add('border-red-300');
        this.classList.remove('border-gray-300');
    } else {
        this.classList.remove('border-red-300');
        this.classList.add('border-gray-300');
    }
});

// Username format validation
document.getElementById('username').addEventListener('input', function() {
    const value = this.value;
    const regex = /^[a-zA-Z0-9_]*$/;
    
    if (value && !regex.test(value)) {
        this.classList.add('border-red-300');
        this.classList.remove('border-gray-300');
    } else {
        this.classList.remove('border-red-300');
        this.classList.add('border-gray-300');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
