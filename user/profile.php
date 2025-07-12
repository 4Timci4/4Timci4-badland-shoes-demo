<?php

ob_start();

require_once '../services/AuthService.php';
$authService = new AuthService();

$authService->checkSessionSecurity();
if (!$authService->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentUser = $authService->getCurrentUser();
$user = $currentUser;
$error_message = '';
$success_message = '';

$user_profile = $authService->getUserProfile($currentUser['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $updateData = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'phone_number' => $_POST['phone_number'] ?? '',
        'gender' => $_POST['gender'] ?? ''
    ];

    $result = $authService->updateUserProfile($currentUser['id'], $updateData);

    if ($result['success']) {
        $success_message = $result['message'];
        SessionConfig::regenerateSession();
        $currentUser = $authService->getCurrentUser();
        $user_profile = $authService->getUserProfile($currentUser['id']);
    } else {
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta name="page-transitions" content="disabled">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Sayfası - Bandland Shoes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 loaded" data-disable-page-transitions="true">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-3">
            <?php
            $active_page = 'profile';
            include '../includes/sidebar.php';
            ?>

            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-20 w-20 rounded-full bg-primary flex items-center justify-center">
                                    <i class="fas fa-user text-white text-2xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 flex-1">
                                <h1 class="text-2xl font-bold text-gray-900">
                                    <?php echo htmlspecialchars($currentUser['full_name'] ?: 'Kullanıcı'); ?>
                                </h1>
                                <p class="text-sm font-medium text-gray-500">
                                    <?php echo htmlspecialchars($currentUser['email']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                        <p class="font-bold">Hata</p>
                        <p><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                        <p class="font-bold">Başarılı</p>
                        <p><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                <?php endif; ?>

                <form action="profile.php" method="POST">
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Kişisel Bilgiler</h3>
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">Ad</label>
                                    <input type="text" name="first_name" id="first_name"
                                        value="<?php echo htmlspecialchars($user_profile['first_name'] ?? ''); ?>"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Soyad</label>
                                    <input type="text" name="last_name" id="last_name"
                                        value="<?php echo htmlspecialchars($user_profile['last_name'] ?? ''); ?>"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="gender" class="block text-sm font-medium text-gray-700">Cinsiyet</label>
                                    <select id="gender" name="gender"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                        <option value="" <?php echo !isset($user_profile['gender']) ? 'selected' : ''; ?>>Seçiniz</option>
                                        <option value="Kadın" <?php echo ($user_profile['gender'] ?? '') === 'Kadın' ? 'selected' : ''; ?>>Kadın</option>
                                        <option value="Erkek" <?php echo ($user_profile['gender'] ?? '') === 'Erkek' ? 'selected' : ''; ?>>Erkek</option>
                                        <option value="Belirtmek İstemiyorum" <?php echo ($user_profile['gender'] ?? '') === 'Belirtmek İstemiyorum' ? 'selected' : ''; ?>>Belirtmek İstemiyorum
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow sm:rounded-lg mt-6">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">İletişim Bilgileri</h3>
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-4">
                                    <label for="email" class="block text-sm font-medium text-gray-700">E-posta
                                        Adresi</label>
                                    <input type="email" name="email" id="email"
                                        value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                    <p class="mt-2 text-xs text-gray-500">E-posta adresinizi değiştirirseniz, yeni
                                        adresinize gönderilen linki onaylamanız gerekecektir.</p>
                                </div>
                                <div class="sm:col-span-4">
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Telefon
                                        Numarası</label>
                                    <input type="tel" name="phone_number" id="phone_number"
                                        value="<?php echo htmlspecialchars($user_profile['phone_number'] ?? ''); ?>"
                                        class="mt-1 block w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" name="update_profile" value="1"
                            class="bg-primary border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Bilgileri Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <style>
        .tab-content {
            transition: opacity 0.3s ease;
        }
    </style>
</body>

</html>
<?php
ob_end_flush();
?>