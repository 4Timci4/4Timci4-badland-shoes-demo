<?php
/**
 * User Profile Management Page
 * Adobe Commerce/Magento session management patterns applied
 */

// Session konfigürasyonunu dahil et ve oturum başlat
require_once 'config/session.php';
start_session_safely();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$error_message = '';
$success_message = '';

// Simple session check like Express.js examples
if (!$auth_service->isLoggedIn()) {
    header('Location: login.php?reason=no_session');
    exit();
}

$user = $auth_service->getCurrentUser();
if (!$user || empty($user['id'])) {
    header('Location: login.php?reason=invalid_user');
    exit();
}

$user_profile = $auth_service->getUserProfile($user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $phoneNumber = $_POST['phone_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $gender = $_POST['gender'] ?? null;

    $email_changed = ($email !== $user['email']);

    $result = $auth_service->updateUserProfile($user['id'], [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'phone_number' => $phoneNumber,
        'email' => $email,
        'gender' => $gender
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
<button data-tab="profile" class="profile-tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'profile') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'; ?> group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full text-left">
                        <i class="fas fa-user-circle -ml-1 mr-3 flex-shrink-0 text-sm"></i>
                        <span class="truncate">Üyelik Bilgilerim</span>
                    </button>
<button data-tab="favorites" class="profile-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'favorites') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'; ?> group rounded-md px-3 py-2 flex items-center text-sm font-medium w-full text-left">
                        <i class="fas fa-heart -ml-1 mr-3 flex-shrink-0 text-sm <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'favorites') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500'; ?>"></i>
                        <span class="truncate">Favorilerim</span>
                    </button>
<a href="logout.php" class="text-gray-600 hover:bg-gray-100 hover:text-gray-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium" data-no-transition="true">
                        <i class="fas fa-sign-out-alt text-gray-400 group-hover:text-gray-500 -ml-1 mr-3 flex-shrink-0 text-sm"></i>
                        <span class="truncate">Çıkış Yap</span>
                    </a>
                </nav>
            </aside>

            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <!-- Mesaj alanı -->
                <div id="message-container">
                    <?php if (!empty($success_message)): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
                            <p><?php echo $success_message; ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                            <p><?php echo $error_message; ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Loading indicator -->
                <div id="loading-indicator" class="hidden text-center py-8">
                    <i class="fas fa-spinner fa-spin text-primary text-2xl"></i>
                    <p class="text-gray-600 mt-2">Yükleniyor...</p>
                </div>

                <!-- Tab içeriği -->
                <div id="tab-content">
                    <?php
                    // Aktif sekmeyi belirle
                    $active_tab = $_GET['tab'] ?? 'profile';
                    
                    // Aktif sekmeye göre içeriği göster
                    if ($active_tab === 'favorites') {
                        include 'views/profile/favorites.php';
                    } else {
                        include 'views/profile/profile-form.php';
                    }
                    ?>
                </div>
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
        // AJAX Tab Sistemi
        let currentTab = '<?php echo $active_tab; ?>';
        let phoneInput = null;

        document.addEventListener('DOMContentLoaded', function () {
            initTabSystem();
            initPhoneInput();
        });

        function initTabSystem() {
            const tabButtons = document.querySelectorAll('.profile-tab');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    if (targetTab !== currentTab) {
                        switchTab(targetTab);
                    }
                });
            });
        }

        function switchTab(tab) {
            // Loading göster
            showLoading();
            
            // Tab butonlarını güncelle
            updateTabButtons(tab);
            
            // URL'yi güncelle (sayfa yenilenmeden)
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', tab);
            history.pushState({tab: tab}, '', newUrl);
            
            // AJAX ile içeriği getir
            fetch(`/api/profile-tabs.php?tab=${tab}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    
                    if (data.success) {
                        document.getElementById('tab-content').innerHTML = data.content;
                        currentTab = tab;
                        
                        // Telefon input'unu yeniden başlat (sadece profile sekmesinde)
                        if (tab === 'profile') {
                            setTimeout(initPhoneInput, 100);
                        }
                        
                        // Mesajları temizle
                        document.getElementById('message-container').innerHTML = '';
                    } else {
                        showError('İçerik yüklenirken bir hata oluştu.');
                        
                        // Eğer session expired ise redirect
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    }
                })
                .catch(error => {
                    hideLoading();
                    showError('Bir hata oluştu. Lütfen tekrar deneyin.');
                    console.error('Tab switch error:', error);
                });
        }

        function updateTabButtons(activeTab) {
            const tabButtons = document.querySelectorAll('.profile-tab');
            
            tabButtons.forEach(button => {
                const tab = button.getAttribute('data-tab');
                const isActive = tab === activeTab;
                
                // Class'ları güncelle
                if (isActive) {
                    button.className = button.className
                        .replace('text-gray-600 hover:bg-gray-100 hover:text-gray-900', 'bg-primary text-white')
                        .replace('text-gray-400 group-hover:text-gray-500', 'text-white');
                } else {
                    button.className = button.className
                        .replace('bg-primary text-white', 'text-gray-600 hover:bg-gray-100 hover:text-gray-900')
                        .replace('text-white', 'text-gray-400 group-hover:text-gray-500');
                }
            });
        }

        function initPhoneInput() {
            const phoneInputField = document.querySelector("#phone_number");
            const nameInputField = document.querySelector("#first_name");
            
            // Elementlerin var olduğunu kontrol et
            if (!phoneInputField || !nameInputField) return;

            // Önceki phone input instance'ını temizle
            if (phoneInput) {
                phoneInput.destroy();
                phoneInput = null;
            }

            const inputClasses = nameInputField.className;
            phoneInputField.className = inputClasses;

            phoneInput = window.intlTelInput(phoneInputField, {
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
            if (form) {
                form.addEventListener("submit", () => {
                    phoneInputField.value = phoneInput.getNumber();
                });
            }
        }

        function showLoading() {
            document.getElementById('loading-indicator').classList.remove('hidden');
            document.getElementById('tab-content').style.opacity = '0.5';
        }

        function hideLoading() {
            document.getElementById('loading-indicator').classList.add('hidden');
            document.getElementById('tab-content').style.opacity = '1';
        }

        function showError(message) {
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = `
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                    <p>${message}</p>
                </div>
            `;
        }

        // Browser back/forward button desteği
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.tab) {
                currentTab = '';
                switchTab(event.state.tab);
            }
        });
    </script>
</body>
</html>
