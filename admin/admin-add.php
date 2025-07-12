<?php


require_once 'config/auth.php';
require_once '../services/AdminAuthService.php';


check_admin_auth();

$authService = new AdminAuthService();


$errors = [];
$success = false;
$form_data = [];

if ($_POST) {

    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Güvenlik hatası! Lütfen sayfayı yenileyip tekrar deneyin.';
    } else {

        $form_data = [
            'username' => trim($_POST['username'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'is_active' => isset($_POST['is_active'])
        ];


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

        if (empty($form_data['password'])) {
            $errors[] = 'Şifre gereklidir.';
        } elseif (strlen($form_data['password']) < 6) {
            $errors[] = 'Şifre en az 6 karakter olmalıdır.';
        }

        if ($form_data['password'] !== $form_data['password_confirm']) {
            $errors[] = 'Şifreler eşleşmiyor.';
        }


        if (empty($errors)) {
            $result = $authService->createAdmin($form_data);

            if (isset($result['success'])) {
                $success = true;
                $form_data = [];
            } elseif (isset($result['error'])) {
                $errors[] = $result['error'];
            }
        }
    }
}


$page_title = 'Yeni Admin Ekle';
$breadcrumb = get_breadcrumb([
    ['title' => 'Admin Yönetimi', 'url' => 'admins.php', 'icon' => 'fas fa-users-cog'],
    ['title' => 'Yeni Admin Ekle', 'url' => '', 'icon' => 'fas fa-user-plus']
]);

include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Yeni Admin Ekle</h1>
                    <p class="mt-2 text-sm text-gray-600">Sisteme yeni yönetici kullanıcısı ekleyin</p>
                </div>
                <a href="admins.php"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium flex items-center transition-colors">
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
                        <p class="font-medium">Admin başarıyla oluşturuldu!</p>
                        <p class="text-sm mt-1">Yeni admin kullanıcısı sisteme eklendi ve giriş yapabilir.</p>
                    </div>
                </div>
                <div class="mt-4 flex space-x-3">
                    <a href="admins.php"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Admin Listesine Dön
                    </a>
                    <a href="admin-add.php"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                        Başka Admin Ekle
                    </a>
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
        <?php if (!$success): ?>
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
                            <input type="text" id="username" name="username"
                                value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" required
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
                            <input type="text" id="full_name" name="full_name"
                                value="<?= htmlspecialchars($form_data['full_name'] ?? '') ?>" required
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
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                            placeholder="örnek: admin@example.com">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Şifre -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-400"></i>
                                Şifre *
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-12"
                                    placeholder="En az 6 karakter">
                                <button type="button" onclick="togglePassword('password')"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Şifre Tekrar -->
                        <div>
                            <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-400"></i>
                                Şifre Tekrar *
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirm" name="password_confirm" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-12"
                                    placeholder="Şifreyi tekrar girin">
                                <button type="button" onclick="togglePassword('password_confirm')"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-eye" id="password_confirmIcon"></i>
                                </button>
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
                            <input type="checkbox" id="is_active" name="is_active" <?= ($form_data['is_active'] ?? true) ? 'checked' : '' ?>
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Admin aktif olsun
                            </label>
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
                            Admin Oluştur
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
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


    document.getElementById('password_confirm').addEventListener('input', function () {
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


    document.getElementById('username').addEventListener('input', function () {
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