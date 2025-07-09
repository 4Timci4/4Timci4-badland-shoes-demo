<?php
session_start();
require_once 'services/AuthService.php';
$auth_service = auth_service();

$user = $auth_service->getCurrentUser();

if (!$user) {
    header('Location: login.php');
    exit();
}

// Tam profil bilgilerini public.users tablosundan alalım
$user_profile = $auth_service->getUserProfile($user['id']);
$display_name = $user_profile['full_name'] ?? $user['email'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - Bandland Shoes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
            <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
                <nav class="space-y-1">
                    <a href="#" class="bg-gray-200 text-gray-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium" aria-current="page">
                        <i class="fas fa-user-circle text-gray-500 group-hover:text-gray-600 -ml-1 mr-3 flex-shrink-0 h-6 w-6"></i>
                        <span class="truncate">Profilim</span>
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
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Profil Bilgileri</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Kişisel bilgilerinizi buradan güncelleyebilirsiniz.</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Ad Soyad</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($display_name); ?></dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">E-posta adresi</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($user['email']); ?></dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Üyelik Tarihi</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo date('d F Y', strtotime($user['created_at'])); ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>