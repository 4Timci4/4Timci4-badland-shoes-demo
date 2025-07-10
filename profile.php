<?php
// Geçici hata ayıklama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Oturum başlat
session_start();
require_once 'services/AuthService.php';

$auth_service = auth_service();
$error_message = '';
$success_message = '';

// Session kontrolü
if (!isset($_SESSION['user_session']) || empty($_SESSION['user_session'])) {
    // Kullanıcı ID oturumda saklanmamışsa giriş sayfasına yönlendir
    if (isset($_GET['tab']) && $_GET['tab'] === 'favorites') {
        // Favoriler sayfasına erişmeye çalışıyorsa özel mesaj göster
        header('Location: login.php?reason=no_session&redirect=profile.php?tab=favorites');
    } else {
        header('Location: login.php?reason=no_session');
    }
    exit();
}

// Kullanıcı verisini doğrula
$user = $auth_service->getCurrentUser();
if (!$user || empty($user['id'])) {
    // Geçersiz kullanıcı - oturumu temizle ve giriş sayfasına yönlendir
    session_unset();
    session_destroy();
    header('Location: login.php?reason=invalid_user');
    exit();
}

// Son aktivite zamanını ayarla veya güncelle
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} else {
    // Oturum zaman aşımı kontrolü (30 dakika)
    if (time() - $_SESSION['last_activity'] > 1800) {
        session_unset();
        session_destroy();
        header('Location: login.php?reason=session_expired');
        exit();
    }
    // Son aktivite zamanını güncelle
    $_SESSION['last_activity'] = time();
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
<a href="?tab=profile" class="<?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'profile') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'; ?> group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-user-circle -ml-1 mr-3 flex-shrink-0 text-sm"></i>
                        <span class="truncate">Üyelik Bilgilerim</span>
                    </a>
<a href="?tab=favorites" class="<?php echo (isset($_GET['tab']) && $_GET['tab'] === 'favorites') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'; ?> group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-heart -ml-1 mr-3 flex-shrink-0 text-sm <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'favorites') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500'; ?>"></i>
                        <span class="truncate">Favorilerim</span>
                    </a>
<a href="logout.php" class="text-gray-600 hover:bg-gray-100 hover:text-gray-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                        <i class="fas fa-sign-out-alt text-gray-400 group-hover:text-gray-500 -ml-1 mr-3 flex-shrink-0 text-sm"></i>
                        <span class="truncate">Çıkış Yap</span>
                    </a>
                </nav>
            </aside>

            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
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

                <?php
                // Aktif sekmeyi belirle
                $active_tab = $_GET['tab'] ?? 'profile';
                
                // Aktif sekmeye göre içeriği göster
                if ($active_tab === 'favorites') {
                    // Favoriler sekmesi
                    include 'views/profile/favorites.php';
                } else {
                    // Varsayılan olarak profil sekmesi
                ?>
                <form action="profile.php" method="POST">
                    <!-- Kişisel Bilgiler Kartı -->
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Kişisel Bilgiler</h3>
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">Ad</label>
                                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user_profile['first_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Soyad</label>
                                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user_profile['last_name'] ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                </div>
                                <div class="sm:col-span-3">
                                    <label for="gender" class="block text-sm font-medium text-gray-700">Cinsiyet</label>
                                    <select id="gender" name="gender" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                        <option value="" <?php echo !isset($user_profile['gender']) ? 'selected' : ''; ?>>Seçiniz</option>
                                        <option value="Kadın" <?php echo ($user_profile['gender'] ?? '') === 'Kadın' ? 'selected' : ''; ?>>Kadın</option>
                                        <option value="Erkek" <?php echo ($user_profile['gender'] ?? '') === 'Erkek' ? 'selected' : ''; ?>>Erkek</option>
                                        <option value="Belirtmek İstemiyorum" <?php echo ($user_profile['gender'] ?? '') === 'Belirtmek İstemiyorum' ? 'selected' : ''; ?>>Belirtmek İstemiyorum</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- İletişim Bilgileri Kartı -->
                    <div class="bg-white shadow sm:rounded-lg mt-6">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">İletişim Bilgileri</h3>
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-4">
                                    <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi</label>
                                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                    <p class="mt-2 text-xs text-gray-500">E-posta adresinizi değiştirirseniz, yeni adresinize gönderilen linki onaylamanız gerekecektir.</p>
                                </div>
                                <div class="sm:col-span-4">
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Telefon Numarası</label>
                                    <input type="tel" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($user_profile['phone_number'] ?? ''); ?>" class="mt-1 block w-full">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="bg-primary border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Bilgileri Güncelle
                        </button>
                    </div>
                </form>
                <?php } ?>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Aktif sekmeyi kontrol et - sadece profil sekmesinde telefon giriş alanı var
            const activeTab = new URLSearchParams(window.location.search).get('tab');
            if (activeTab === 'favorites') return; // Favoriler sekmesinde bu kodu çalıştırma
            
            const phoneInputField = document.querySelector("#phone_number");
            const nameInputField = document.querySelector("#first_name");
            
            // Elementlerin var olduğunu kontrol et
            if (!phoneInputField || !nameInputField) return;

            const inputClasses = nameInputField.className;
            phoneInputField.className = inputClasses;

            const phoneInput = window.intlTelInput(phoneInputField, {
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
        });
    </script>
</body>
</html>
