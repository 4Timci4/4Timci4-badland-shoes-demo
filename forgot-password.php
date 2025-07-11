<?php
require_once 'services/AuthService.php';

$auth_service = new AuthService();
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    // Session kaldırıldı, şifre sıfırlama fonksiyonu çalışmayacak
    $error_message = 'Şifre sıfırlama özelliği session yönetimi kaldırıldığı için çalışmamaktadır.';
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

            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">Session yönetimi kaldırıldığı için şifre sıfırlama özelliği çalışmamaktadır.</span>
                <p class="mt-2">
                    <a href="login.php" class="text-red-800 underline">Giriş sayfasına dönün</a>
                </p>
            </div>
            
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