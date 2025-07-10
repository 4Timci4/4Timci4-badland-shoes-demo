<?php
session_start();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$error_message = '';
$success_message = '';

$user = $auth_service->getCurrentUser();

if (!$user) {
    header('Location: login.php');
    exit();
}

$user_profile = $auth_service->getUserProfile($user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $phoneNumber = $_POST['phone_number'] ?? '';
    $email = $_POST['email'] ?? '';

    $email_changed = ($email !== $user['email']);

    $result = $auth_service->updateUserProfile($user['id'], [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'phone_number' => $phoneNumber,
        'email' => $email
    ]);

    if ($result['success']) {
        if ($email_changed) {
            $success_message = 'Profiliniz başarıyla güncellendi. E-posta değişikliği için lütfen yeni e-posta adresinize gelen doğrulama linkine tıklayın.';
        } else {
            $success_message = 'Profiliniz başarıyla güncellendi.';
        }
        $user_profile = $auth_service->getUserProfile($user['id']);
    } else {
        $error_message = $result['message'];
    }
}

$display_name = trim(($user_profile['first_name'] ?? '') . ' ' . ($user_profile['last_name'] ?? '')) ?: $user['email'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Üyelik Bilgilerim - Bandland Shoes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
            <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
                <nav class="space-y-1">
                    <a href="#" class="bg-primary text-white group rounded-md px-3 py-2 flex items-center text-sm font-medium" aria-current="page">
                        <i class="fas fa-user-circle -ml-1 mr-3 flex-shrink-0 h-6 w-6"></i>
                        <span class="truncate">Üyelik Bilgilerim</span>
                    </a>
                    <a href="#" class="text-gray-600 hover:bg-gray-100 hover:text-gray-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-map-marker-alt text-gray-400 group-hover:text-gray-500 -ml-1 mr-3 flex-shrink-0 h-6 w-6"></i>
                        <span class="truncate">Adreslerim</span>
                    </a>
                    <a href="#" class="text-gray-600 hover:bg-gray-100 hover:text-gray-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-shopping-bag text-gray-400 group-hover:text-gray-500 -ml-1 mr-3 flex-shrink-0 h-6 w-6"></i>
                        <span class="truncate">Siparişlerim</span>
                    </a>
                    <a href="logout.php" class="text-gray-600 hover:bg-gray-100 hover:text-gray-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-sign-out-alt text-gray-400 group-hover:text-gray-500 -ml-1 mr-3 flex-shrink-0 h-6 w-6"></i>
                        <span class="truncate">Çıkış Yap</span>
                    </a>
                </nav>
            </aside>

            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <form action="profile.php" method="POST">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Üyelik Bilgileri</h3>
                                <p class="mt-1 text-sm text-gray-500">Bu bilgileri dilediğiniz zaman güncelleyebilirsiniz.</p>
                            </div>

                            <?php if (!empty($success_message)): ?>
                                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                                    <p><?php echo $success_message; ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($error_message)): ?>
                                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                                    <p><?php echo $error_message; ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">Ad</label>
                                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user_profile['first_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                </div>
                                
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Soyad</label>
                                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user_profile['last_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                </div>

                                <div class="col-span-6 sm:col-span-4">
                                    <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                    <p class="mt-2 text-xs text-gray-500">E-posta adresinizi değiştirirseniz, yeni adresinize gönderilen linki onaylamanız gerekecektir.</p>
                                </div>

                                <div class="col-span-6 sm:col-span-4">
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Telefon Numarası</label>
                                    <input type="tel" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($user_profile['phone_number'] ?? ''); ?>" class="mt-1 block w-full">
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <button type="submit" class="bg-primary border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Bilgileri Güncelle
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
    <style>
        .iti { width: 100%; }
        .iti__selected-dial-code { font-size: 0.9em; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInputField = document.querySelector("#phone_number");
            const nameInputField = document.querySelector("#first_name");

            const inputClasses = nameInputField.className;
            phoneInputField.className = inputClasses;

            const phoneInput = window.intlTelInput(phoneInputField, {
                initialCountry: "auto",
                geoIpLookup: (callback) => {
                    fetch("https://ipapi.co/json")
                        .then(res => res.json())
                        .then(data => callback(data.country_code))
                        .catch(() => callback("tr"));
                },
                separateDialCode: true,
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            });

            const form = phoneInputField.closest("form");
            form.addEventListener("submit", () => {
                phoneInputField.value = phoneInput.getNumber();
            });
        });
    </script>
</body>
</html>