<?php
/**
 * User Profile Management Page
 * 
 * Session tabanlı kimlik doğrulama ile korumalı kullanıcı profil sayfası
 */

require_once 'services/AuthService.php';
$authService = new AuthService();

// Giriş kontrolü
if (!$authService->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Kullanıcı bilgilerini al
$currentUser = $authService->getCurrentUser();
$user = $currentUser; // favorites.php için backward compatibility
$error_message = '';
$success_message = '';

// Aktif tab kontrolü
$active_tab = $_GET['tab'] ?? 'profile';

// Profil güncelleme işlemi
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
        // Güncellenmiş kullanıcı bilgilerini al
        $currentUser = $authService->getCurrentUser();
    } else {
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Sayfası - Bandland Shoes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
            <!-- Sidebar -->
            <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
                <nav class="space-y-1">
                    <a href="#profile" class="bg-gray-50 text-gray-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-user text-gray-400 group-hover:text-gray-500 flex-shrink-0 -ml-1 mr-3 h-6 w-6"></i>
                        <span class="truncate">Profil Bilgileri</span>
                    </a>
                    <a href="#favorites" class="text-gray-600 hover:text-gray-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-heart text-gray-400 group-hover:text-gray-500 flex-shrink-0 -ml-1 mr-3 h-6 w-6"></i>
                        <span class="truncate">Favorilerim</span>
                    </a>
                    <a href="logout.php" class="text-red-600 hover:text-red-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-sign-out-alt text-red-400 group-hover:text-red-500 flex-shrink-0 -ml-1 mr-3 h-6 w-6"></i>
                        <span class="truncate">Çıkış Yap</span>
                    </a>
                </nav>
            </aside>

            <!-- Main content -->
            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <!-- Profile Header -->
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

                <!-- Alert Messages -->
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

                <!-- Profile Form -->
                <div id="profile" class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Profil Bilgileri</h2>
                        
                        <form method="POST" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">Ad</label>
                                    <input type="text" name="first_name" id="first_name" 
                                           value="<?php echo htmlspecialchars($currentUser['first_name'] ?? ''); ?>"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                </div>
                                
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Soyad</label>
                                    <input type="text" name="last_name" id="last_name" 
                                           value="<?php echo htmlspecialchars($currentUser['last_name'] ?? ''); ?>"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">E-posta</label>
                                <input type="email" name="email" id="email" 
                                       value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                                       disabled
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 sm:text-sm">
                                <p class="mt-2 text-sm text-gray-500">E-posta adresi değiştirilemez.</p>
                            </div>

                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700">Telefon Numarası</label>
                                <input type="tel" name="phone_number" id="phone_number" 
                                       value="<?php echo htmlspecialchars($currentUser['phone_number'] ?? ''); ?>"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                            </div>

                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700">Cinsiyet</label>
                                <select name="gender" id="gender" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">
                                    <option value="">Seçiniz</option>
                                    <option value="Kadın" <?php echo ($currentUser['gender'] ?? '') === 'Kadın' ? 'selected' : ''; ?>>Kadın</option>
                                    <option value="Erkek" <?php echo ($currentUser['gender'] ?? '') === 'Erkek' ? 'selected' : ''; ?>>Erkek</option>
                                    <option value="Belirtmek İstemiyorum" <?php echo ($currentUser['gender'] ?? '') === 'Belirtmek İstemiyorum' ? 'selected' : ''; ?>>Belirtmek İstemiyorum</option>
                                </select>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" name="update_profile" 
                                        class="bg-primary hover:bg-primary-dark text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                    Profili Güncelle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Favorites Section -->
                <div id="favorites" class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Favorilerim</h2>
                        
                        <?php
                        // Favorileri include et
                        if (file_exists('views/profile/favorites.php')) {
                            include 'views/profile/favorites.php';
                        } else {
                            echo '<p class="text-gray-500">Henüz favori ürününüz bulunmamaktadır.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
