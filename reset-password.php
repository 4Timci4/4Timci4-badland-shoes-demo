<?php
require_once 'services/AuthService.php';

$auth_service = new AuthService();
$error_message = '';
$success_message = '';
$token = $_GET['token'] ?? '';

// Token kontrolü
if (empty($token)) {
    $error_message = 'Geçersiz şifre sıfırlama linki.';
} else {
    // Token geçerlilik kontrolü (basit kontrol)
    if (strlen($token) < 32) {
        $error_message = 'Geçersiz şifre sıfırlama token\'ı.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error_message)) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $token = $_POST['token'] ?? '';

    if (empty($password)) {
        $error_message = 'Şifre boş bırakılamaz.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Şifre en az 6 karakter olmalıdır.';
    } elseif ($password !== $password_confirm) {
        $error_message = 'Şifreler uyuşmuyor.';
    } else {
        try {
            $result = $auth_service->resetPassword($token, $password);
            if ($result) {
                $success_message = 'Şifreniz başarıyla güncellendi. Artık yeni şifrenizle giriş yapabilirsiniz.';
            } else {
                $error_message = 'Şifre sıfırlama token\'ı geçersiz veya süresi dolmuş.';
            }
        } catch (Exception $e) {
            $error_message = 'Şifre güncellemesi sırasında bir hata oluştu. Lütfen tekrar deneyin.';
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
                    <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
                    <div class="mt-3">
                        <a href="login.php" class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
                            Giriş Yap
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($success_message) && empty($error_message)): ?>
                <form method="POST" action="reset-password.php" class="mt-8 space-y-6">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Yeni Şifre</label>
                        <input type="password" id="password" name="password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                               placeholder="Yeni şifrenizi girin" minlength="6">
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">Yeni Şifre (Tekrar)</label>
                        <input type="password" id="password_confirm" name="password_confirm" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                               placeholder="Yeni şifrenizi tekrar girin" minlength="6">
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Şifremi Güncelle
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="text-sm text-center mt-4">
                <a href="login.php" class="font-medium text-primary hover:text-primary-dark">
                    Giriş ekranına geri dön
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>