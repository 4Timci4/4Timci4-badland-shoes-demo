<?php



require_once '../services/AdminAuthService.php';

$authService = new AdminAuthService();


if ($authService->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}


if ($authService->checkTimeout()) {
    $timeout_message = 'Oturumunuz zaman aşımına uğradı. Lütfen tekrar giriş yapın.';
}


$logout_message = '';
if (isset($_GET['logout'])) {
    $logout_message = 'Başarıyla çıkış yaptınız.';
}


if (isset($_GET['timeout'])) {
    $timeout_message = 'Oturumunuz zaman aşımına uğradı. Lütfen tekrar giriş yapın.';
}




if ($_POST) {

    $admin_data = $authService->getAdminById(1);

    if ($admin_data) {

        $authService->createSession($admin_data);


        header('Location: dashboard.php');
        exit;
    } else {

        $error = 'Varsayılan admin kullanıcısı bulunamadı!';
    }
}

?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Giriş</title>

    <!-- Tailwind CSS -->
    <script src="https:
    <!-- Font Awesome -->
    <link rel=" stylesheet" href="https:
    <!-- Google Fonts -->
    <link href=" https: <!-- Tailwind Config -->
        <script>
            tailwind.config = {
                theme: {
                extend: {
                colors: {
                primary: {
                50: '#f0f0ff',
            100: '#e0e1ff',
            200: '#c7c8ff',
            300: '#9e95f5',
            400: '#7367f0',
            500: '#5a52d6',
            600: '#4b46c7',
            700: '#3e3ba3',
            800: '#35327a',
            900: '#2e2b5e',
                        }
                    },
            fontFamily: {
                'inter': ['Inter', 'system-ui', 'sans-serif'],
                    },
            animation: {
                'float': 'float 6s ease-in-out infinite',
            'gradient': 'gradient 15s ease infinite',
                    },
            keyframes: {
                float: {
                '0%, 100%': {transform: 'translateY(0px) rotate(0deg)' },
            '50%': {transform: 'translateY(-20px) rotate(180deg)' }
                        },
            gradient: {
                '0%, 100%': {'background-position': '0% 50%' },
            '50%': {'background-position': '100% 50%' }
                        }
                    }
                }
            }
        }
    </script>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-primary-500 via-purple-600 to-pink-500 bg-[length:400%_400%] animate-gradient font-inter flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute w-96 h-96 bg-white/10 rounded-full blur-3xl -top-48 -left-48 animate-float"></div>
        <div class="absolute w-80 h-80 bg-white/5 rounded-full blur-3xl top-1/2 -right-40 animate-float"
            style="animation-delay: 2s;"></div>
        <div class="absolute w-64 h-64 bg-white/10 rounded-full blur-3xl -bottom-32 left-1/3 animate-float"
            style="animation-delay: 4s;"></div>
    </div>

    <!-- Main Login Container -->
    <div class="relative z-10 w-full max-w-md">

        <!-- Login Card -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">

            <!-- Header Section -->
            <div class="p-8 pb-6 text-center">
                <div class="mb-6">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl shadow-lg flex items-center justify-center mx-auto mb-4 transform hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-shoe-prints text-white text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Admin Panel</h1>
                    <p class="text-gray-600">Yönetim paneline hoş geldiniz</p>
                </div>
            </div>

            <!-- Error Alert -->
            <?php if (isset($error)): ?>
                <div class="mx-8 mb-6">
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                        <span class="text-red-800 font-medium"><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Logout Success Message -->
            <?php if (!empty($logout_message)): ?>
                <div class="mx-8 mb-6">
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <span class="text-green-800 font-medium"><?= htmlspecialchars($logout_message) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Timeout Message -->
            <?php if (!empty($timeout_message)): ?>
                <div class="mx-8 mb-6">
                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 flex items-center">
                        <i class="fas fa-clock text-orange-500 mr-3"></i>
                        <span class="text-orange-800 font-medium"><?= htmlspecialchars($timeout_message) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" id="loginForm" class="px-8 pb-8">
                <div class="space-y-6">

                    <!-- Username Field -->
                    <div class="space-y-2">
                        <label for="username" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-user mr-2 text-gray-400"></i>
                            Kullanıcı Adı
                        </label>
                        <input type="text" id="username" name="username" required
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-300 placeholder-gray-400"
                            placeholder="Kullanıcı adınızı girin">
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-lock mr-2 text-gray-400"></i>
                            Şifre
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-300 placeholder-gray-400 pr-12"
                                placeholder="Şifrenizi girin">
                            <button type="button" onclick="togglePassword()"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox"
                                class="w-4 h-4 text-primary-500 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2">
                            <span class="ml-2 text-sm text-gray-600">Beni hatırla</span>
                        </label>
                        <a href="#"
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium transition-colors">
                            Şifremi unuttum
                        </a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" id="loginBtn"
                        class="w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <span id="loginBtnText" class="flex items-center justify-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Giriş Yap
                        </span>
                        <span id="loadingSpinner" class="hidden flex items-center justify-center">
                            <i class="fas fa-spinner animate-spin mr-2"></i>
                            Giriş yapılıyor...
                        </span>
                    </button>
                </div>
            </form>

            <!-- Information -->
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-3 font-medium">
                        <i class="fas fa-info-circle mr-2"></i>
                        Giriş yapmak için veritabanından oluşturulmuş admin hesabınızı kullanın.
                    </p>
                    <p class="text-xs text-gray-500">
                        Hesabınız yoksa, mevcut bir admin kullanıcısından yeni hesap oluşturmasını isteyebilirsiniz.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white/80 text-sm">
                © <?= date('Y') ?> Admin Panel. Tüm hakları saklıdır.
            </p>
        </div>
    </div>

    <!-- JavaScript -->
    <script>

        function togglePassword() {
            const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
            } else {
            passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
            }
        }


        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
        const loginBtnText = document.getElementById('loginBtnText');
        const loadingSpinner = document.getElementById('loadingSpinner');


        loginBtn.disabled = true;
        loginBtn.classList.add('opacity-75', 'cursor-not-allowed');
        loginBtnText.classList.add('hidden');
        loadingSpinner.classList.remove('hidden');
        });

        
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function () {
                this.parentElement.classList.add('transform', 'scale-[1.02]');
            });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('transform', 'scale-[1.02]');
            });
        });


        document.addEventListener('keydown', function(e) {
            
            if (e.key === 'Enter' && !e.shiftKey) {
                const form = document.getElementById('loginForm');
        if (document.activeElement.tagName === 'INPUT') {
            form.submit();
                }
            }


        if (e.key === 'Escape') {
            document.getElementById('username').value = '';
        document.getElementById('password').value = '';
        document.getElementById('username').focus();
            }
        });


        window.addEventListener('load', function() {
            document.getElementById('username').focus();
        });

    </script>
</body>

</html>