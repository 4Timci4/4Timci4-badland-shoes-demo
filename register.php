<?php
session_start();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password !== $password_confirm) {
        $error_message = 'Şifreler uyuşmuyor.';
    } else {
        $result = $auth_service->registerUser($email, $password, [
            'full_name' => $fullName,
            'phone_number' => $phone
        ]);
        if ($result['success']) {
            $success_message = 'Hesabınız başarıyla oluşturuldu! Lütfen e-postanızı kontrol ederek hesabınızı doğrulayın.';
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Bandland Shoes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
            <div class="text-center">
                <h2 class="mt-4 text-3xl font-bold text-gray-900">
                    Hesabınızı Oluşturun
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Zaten bir hesabınız var mı?
                    <a href="login.php" class="font-medium text-primary hover:text-primary-dark">
                        Giriş yapın
                    </a>
                </p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p class="font-bold">Hata</p>
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">Başarılı</p>
                    <p><?php echo $success_message; ?></p>
                </div>
            <?php else: ?>
                <form class="mt-8 space-y-6" action="register.php" method="POST">
                    
                    <div>
                        <label for="full-name" class="block text-sm font-medium text-gray-700 mb-1">Ad Soyad</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-user text-gray-400"></i>
                            </span>
                            <input id="full-name" name="full_name" type="text" required class="appearance-none block w-full px-3 py-2 pl-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" placeholder="Adınız ve soyadınız">
                        </div>
                    </div>

                    <div>
                        <label for="email-address" class="block text-sm font-medium text-gray-700 mb-1">E-posta Adresi</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </span>
                            <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none block w-full px-3 py-2 pl-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" placeholder="ornek@mail.com">
                        </div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon Numarası</label>
                        <input id="phone" name="phone" type="tel" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Şifre</label>
                         <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-lock text-gray-400"></i>
                            </span>
                            <input id="password" name="password" type="password" autocomplete="new-password" required class="appearance-none block w-full px-3 py-2 pl-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" placeholder="En az 6 karakter">
                        </div>
                    </div>

                    <div>
                        <label for="password-confirm" class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrar</label>
                         <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-lock text-gray-400"></i>
                            </span>
                            <input id="password-confirm" name="password_confirm" type="password" autocomplete="new-password" required class="appearance-none block w-full px-3 py-2 pl-10 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" placeholder="Şifrenizi tekrar girin">
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            <a href="#" class="text-primary hover:underline">Hizmet Şartları</a>'nı ve <a href="#" class="text-primary hover:underline">Gizlilik Politikası</a>'nı okudum ve kabul ediyorum.
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark transition-colors">
                            Hesap Oluştur
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
    <style>
        .iti { width: 100%; }
        .iti__selected-dial-code { font-size: 0.9em; } /* Alan kodunu biraz küçült */
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInputField = document.querySelector("#phone");
            const nameInputField = document.querySelector("#full-name");

            // Kopyalanacak stil sınıflarını al
            const inputClasses = nameInputField.className;

            // Telefon input'una aynı sınıfları uygula
            phoneInputField.className += ' ' + inputClasses;
            // Kütüphanenin eklediği bazı stilleri sıfırlamak gerekebilir
            phoneInputField.style.paddingLeft = '52px'; // Bayrak için boşluk bırak

            const phoneInput = window.intlTelInput(phoneInputField, {
                initialCountry: "tr",
                separateDialCode: true,
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            });

            // Form gönderildiğinde uluslararası formatı al
            const form = phoneInputField.closest("form");
            form.addEventListener("submit", () => {
                phoneInputField.value = phoneInput.getNumber();
            });
        });
    </script>
</body>
</html>