<?php
require_once 'services/AuthService.php';
$authService = new AuthService();


if ($authService->isLoggedIn()) {
    header('Location: /user/profile');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password !== $password_confirm) {
        $error_message = 'Şifreler uyuşmuyor.';
    } else {
        $result = $authService->register($email, $password, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $phone,
            'gender' => $gender
        ]);
        if ($result['success']) {
            $success_message = 'Hesabınız başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.';
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
                    <a href="/login" class="font-medium text-primary hover:text-primary-dark">
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
                    <p class="mt-2">
                        <a href="/login" class="text-green-800 underline">Giriş sayfasına gidin</a>
                    </p>
                </div>
            <?php else: ?>
                <form class="mt-8 space-y-6" action="/register" method="POST">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first-name" class="block text-sm font-medium text-gray-700 mb-1">Ad</label>
                            <input id="first-name" name="first_name" type="text" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                placeholder="Adınız">
                        </div>
                        <div>
                            <label for="last-name" class="block text-sm font-medium text-gray-700 mb-1">Soyad</label>
                            <input id="last-name" name="last_name" type="text" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                placeholder="Soyadınız">
                        </div>
                    </div>

                    <div>
                        <label for="email-address" class="block text-sm font-medium text-gray-700 mb-1">E-posta
                            Adresi</label>
                        <input id="email-address" name="email" type="email" autocomplete="email" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                            placeholder="ornek@mail.com">
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Cinsiyet</label>
                        <select id="gender" name="gender"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                            <option value="" disabled selected>Cinsiyet Seçiniz</option>
                            <option value="Kadın">Kadın</option>
                            <option value="Erkek">Erkek</option>
                            <option value="Belirtmek İstemiyorum">Belirtmek İstemiyorum</option>
                        </select>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon Numarası</label>
                        <input id="phone" name="phone" type="tel" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Şifre</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                placeholder="En az 6 karakter">
                        </div>
                        <div>
                            <label for="password-confirm" class="block text-sm font-medium text-gray-700 mb-1">Şifre
                                Tekrar</label>
                            <input id="password-confirm" name="password_confirm" type="password" autocomplete="new-password"
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                placeholder="Şifrenizi tekrar girin">
                        </div>
                    </div>

                    <div class="flex items-start">
                        <input id="terms" name="terms" type="checkbox" required
                            class="h-4 w-4 mt-1 text-primary focus:ring-primary border-gray-300 rounded flex-shrink-0">
                        <label for="terms" class="ml-3 block text-sm text-gray-900 leading-relaxed">
                            <a href="#" class="text-primary hover:underline">Hizmet Şartları</a>'nı ve <a href="#"
                                class="text-primary hover:underline">Gizlilik Politikası</a>'nı okudum ve kabul ediyorum.
                        </label>
                    </div>

                    <div>
                        <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark transition-colors">
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
        .iti {
            width: 100%;
        }

        .iti__flag-container {
            z-index: 10;
        }

        .iti__selected-dial-code {
            font-size: 1rem;
            line-height: 1.5rem;
        }

        @media (min-width: 640px) {
            .iti__selected-dial-code {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInputField = document.querySelector("#phone");


            if (phoneInputField) {
                const nameInputField = document.querySelector("#first-name");

                const phoneInput = window.intlTelInput(phoneInputField, {
                    initialCountry: "tr",
                    separateDialCode: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
                });

                const form = phoneInputField.closest("form");
                if (form) {
                    form.addEventListener("submit", () => {
                        phoneInputField.value = phoneInput.getNumber();
                    });
                }
            }
        });
    </script>
</body>

</html>