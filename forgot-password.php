<?php
session_start();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $result = $auth_service->sendPasswordResetEmail($email);
    
    // Güvenlik nedeniyle, e-posta mevcut olmasa bile her zaman başarı mesajı gösterilir.
    // Gerçek hata loglanabilir ama kullanıcıya yansıtılmaz.
    $success_message = 'E-posta adresiniz sistemimizde kayıtlıysa, şifre sıfırlama talimatlarını içeren bir e-posta gönderildi.';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - Bandland Shoes</title>
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
                    Şifrenizi sıfırlayın
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Şifre sıfırlama linki gönderebilmemiz için e-posta adresinizi girin.
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
                <form class="mt-8 space-y-6" action="forgot-password.php" method="POST">
                    <div class="rounded-md shadow-sm">
                        <div>
                            <label for="email-address" class="sr-only">E-posta adresi</label>
                            <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="E-posta adresi">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark">
                            Sıfırlama Linki Gönder
                        </button>
                    </div>
                </form>
            <?php endif; ?>
             <div class="text-sm text-center">
                <a href="login.php" class="font-medium text-primary hover:text-primary-dark">
                    Giriş ekranına geri dön
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>