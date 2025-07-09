<?php
session_start();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password !== $password_confirm) {
        $error_message = 'Şifreler uyuşmuyor.';
    } else {
        $result = $auth_service->registerUser($email, $password, ['full_name' => $fullName]);
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Yeni hesap oluşturun
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Zaten bir hesabınız var mı?
                    <a href="login.php" class="font-medium text-primary hover:text-primary-dark">
                        Giriş yapın
                    </a>
                </p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php else: ?>
                <form class="mt-8 space-y-6" action="register.php" method="POST">
                    <div class="rounded-md shadow-sm -space-y-px">
                        <div>
                            <label for="full-name" class="sr-only">Ad Soyad</label>
                            <input id="full-name" name="full_name" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Ad Soyad">
                        </div>
                        <div>
                            <label for="email-address" class="sr-only">E-posta adresi</label>
                            <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="E-posta adresi">
                        </div>
                        <div>
                            <label for="password" class="sr-only">Şifre</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Şifre">
                        </div>
                        <div>
                            <label for="password-confirm" class="sr-only">Şifre Tekrar</label>
                            <input id="password-confirm" name="password_confirm" type="password" autocomplete="new-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Şifre Tekrar">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
                            Hesap Oluştur
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>