<?php
/**
 * User Profile Management Page
 *
 * Session tabanlı kimlik doğrulama ile korumalı kullanıcı profil sayfası
 */

// Start output buffering to prevent session errors
ob_start();

require_once 'services/AuthService.php';
$authService = new AuthService();

// Session güvenlik kontrollerini yap ve giriş kontrolü
$authService->checkSessionSecurity();
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
$user_profile = $authService->getUserProfile($currentUser['id']);

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
                    <a href="profile.php?tab=profile" class="tab-link <?php echo $active_tab === 'profile' ? 'bg-gray-50 text-gray-900' : 'text-gray-600 hover:text-gray-900'; ?> group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-user text-gray-400 group-hover:text-gray-500 flex-shrink-0 -ml-1 mr-3 h-6 w-6"></i>
                        <span class="truncate">Profil Bilgileri</span>
                    </a>
                    <a href="profile.php?tab=favorites" class="tab-link <?php echo $active_tab === 'favorites' ? 'bg-gray-50 text-gray-900' : 'text-gray-600 hover:text-gray-900'; ?> group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-heart text-gray-400 group-hover:text-gray-500 flex-shrink-0 -ml-1 mr-3 h-6 w-6"></i>
                        <span class="truncate">Favorilerim</span>
                    </a>
                    <a href="logout.php" class="text-red-600 hover:text-red-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium" data-no-transition>
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

                <div id="profile-content">
                    <?php
                    define('IS_PROFILE_PAGE', true);

                    if ($active_tab === 'profile') {
                        if (file_exists('views/profile/profile-form.php')) {
                            include 'views/profile/profile-form.php';
                        } else {
                            echo '<p>Profil formu yüklenemedi.</p>';
                        }
                    } elseif ($active_tab === 'favorites') {
                        if (file_exists('views/profile/favorites.php')) {
                            include 'views/profile/favorites.php';
                        } else {
                            echo '<p>Favoriler yüklenemedi.</p>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const profileContent = document.getElementById('profile-content');

            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.href;
                    const tab = new URL(url).searchParams.get('tab');

                    // Update active class
                    tabLinks.forEach(l => l.classList.remove('bg-gray-50', 'text-gray-900'));
                    this.classList.add('bg-gray-50', 'text-gray-900');

                    // Fetch new content
                    fetch(`/api/profile-tabs.php?tab=${tab}`)
                        .then(response => response.text())
                        .then(html => {
                            profileContent.style.opacity = 0;
                            setTimeout(() => {
                                profileContent.innerHTML = html;
                                profileContent.style.opacity = 1;
                            }, 300);
                        });

                    // Update browser history
                    window.history.pushState({tab: tab}, '', url);
                });
            });

            // Handle back/forward browser buttons
            window.addEventListener('popstate', function(e) {
                if (e.state && e.state.tab) {
                    const tab = e.state.tab;
                    const link = document.querySelector(`.tab-link[href*="tab=${tab}"]`);
                    if (link) {
                        link.click();
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php
// End output buffering and flush buffer
ob_end_flush();
?>
