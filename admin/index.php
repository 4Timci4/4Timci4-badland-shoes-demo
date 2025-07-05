<?php
/**
 * Admin Panel Ana Giriş Sayfası
 * 
 * Modern, profesyonel admin panel giriş ekranı - Sadece Tailwind CSS
 */

session_start();

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Basit giriş kontrolü
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Basit admin kontrolü (gerçek uygulamada database'den kontrol edilmeli)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Geçersiz kullanıcı adı veya şifre!';
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
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind Config -->
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
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '50%': { transform: 'translateY(-20px) rotate(180deg)' }
                        },
                        gradient: {
                            '0%, 100%': { 'background-position': '0% 50%' },
                            '50%': { 'background-position': '100% 50%' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-500 via-purple-600 to-pink-500 bg-[length:400%_400%] animate-gradient font-inter flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute w-96 h-96 bg-white/10 rounded-full blur-3xl -top-48 -left-48 animate-float"></div>
        <div class="absolute w-80 h-80 bg-white/5 rounded-full blur-3xl top-1/2 -right-40 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute w-64 h-64 bg-white/10 rounded-full blur-3xl -bottom-32 left-1/3 animate-float" style="animation-delay: 4s;"></div>
    </div>
    
    <!-- Main Login Container -->
    <div class="relative z-10 w-full max-w-md">
        
        <!-- Login Card -->
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
            
            <!-- Header Section -->
            <div class="p-8 pb-6 text-center">
                <div class="mb-6">
                    <div class="w-16 h-16 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl shadow-lg flex items-center justify-center mx-auto mb-4 transform hover:scale-110 transition-transform duration-300">
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
            
            <!-- Login Form -->
            <form method="POST" id="loginForm" class="px-8 pb-8">
                <div class="space-y-6">
                    
                    <!-- Username Field -->
                    <div class="space-y-2">
                        <label for="username" class="block text-sm font-semibold text-gray-700">
                            <i class="fas fa-user mr-2 text-gray-400"></i>
                            Kullanıcı Adı
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required
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
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-300 placeholder-gray-400 pr-12"
                                   placeholder="Şifrenizi girin">
                            <button type="button" 
                                    onclick="togglePassword()" 
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="w-4 h-4 text-primary-500 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 focus:ring-2">
                            <span class="ml-2 text-sm text-gray-600">Beni hatırla</span>
                        </label>
                        <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-medium transition-colors">
                            Şifremi unuttum
                        </a>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" 
                            id="loginBtn"
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
            
            <!-- Demo Information -->
            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-3 font-medium">Demo Giriş Bilgileri:</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-white rounded-lg p-3 border border-gray-200">
                            <span class="font-semibold text-gray-700">Kullanıcı:</span>
                            <span class="block text-primary-600 font-mono">admin</span>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-gray-200">
                            <span class="font-semibold text-gray-700">Şifre:</span>
                            <span class="block text-primary-600 font-mono">admin123</span>
                        </div>
                    </div>
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
        // Password toggle functionality
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

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            // Show loading state
            loginBtn.disabled = true;
            loginBtn.classList.add('opacity-75', 'cursor-not-allowed');
            loginBtnText.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
        });

        // Input focus animations
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('transform', 'scale-[1.02]');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('transform', 'scale-[1.02]');
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Enter to submit
            if (e.key === 'Enter' && !e.shiftKey) {
                const form = document.getElementById('loginForm');
                if (document.activeElement.tagName === 'INPUT') {
                    form.submit();
                }
            }
            
            // Escape to clear form
            if (e.key === 'Escape') {
                document.getElementById('username').value = '';
                document.getElementById('password').value = '';
                document.getElementById('username').focus();
            }
        });

        // Auto-focus on username field
        window.addEventListener('load', function() {
            document.getElementById('username').focus();
        });

        // Demo data auto-fill (for development)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'admin123';
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>
