<?php
session_start();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password !== $password_confirm) {
        $error_message = 'Şifreler uyuşmuyor.';
    } else {
        $result = $auth_service->updateUserPassword($password);
        if ($result['success']) {
            $success_message = 'Şifreniz başarıyla güncellendi. Şimdi giriş yapabilirsiniz.';
        } else {
            $error_message = 'Şifre güncellenirken bir hata oluştu: ' . $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Şifre Belirle - Bandland Shoes</title>
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
                    Yeni şifrenizi belirleyin
                </h2>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <p><?php echo $success_message; ?></p>
                    <a href="login.php" class="font-bold text-green-800 hover:underline">Giriş yapmak için buraya tıklayın.</a>
                </div>
            <?php else: ?>
                <form class="mt-8 space-y-6" action="reset-password.php" method="POST">
                    <div class="rounded-md shadow-sm -space-y-px">
                        <div>
                            <label for="password" class="sr-only">Yeni Şifre</label>
                            <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Yeni Şifre">
                        </div>
                        <div>
                            <label for="password-confirm" class="sr-only">Yeni Şifre Tekrar</label>
                            <input id="password-confirm" name="password_confirm" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Yeni Şifre Tekrar">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
                            Şifreyi Güncelle
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>