<?php
/**
 * Session System Test Page
 * 
 * Bu sayfa yeni session sistemini test etmek için kullanılır
 */

require_once 'services/AuthService.php';

$authService = new AuthService();
$testResults = [];

// Test 1: Session konfigürasyonu kontrolü
$testResults['config'] = [
    'name' => 'Session Konfigürasyonu',
    'status' => 'success',
    'details' => [
        'session_name' => session_name(),
        'session_id' => session_id(),
        'cookie_httponly' => ini_get('session.cookie_httponly'),
        'cookie_secure' => ini_get('session.cookie_secure'),
        'use_strict_mode' => ini_get('session.use_strict_mode'),
        'gc_maxlifetime' => ini_get('session.gc_maxlifetime')
    ]
];

// Test 2: Session debug bilgileri
if ($authService->isLoggedIn()) {
    $testResults['debug'] = [
        'name' => 'Session Debug Bilgileri',
        'status' => 'success',
        'details' => $authService->getSessionDebugInfo()
    ];
    
    // Test 3: Session sağlık kontrolü
    $testResults['health'] = [
        'name' => 'Session Sağlık Kontrolü',
        'status' => 'success',
        'details' => $authService->checkSessionHealth()
    ];
    
    // Test 4: Aktif session'lar
    $currentUser = $authService->getCurrentUser();
    $testResults['active_sessions'] = [
        'name' => 'Aktif Session\'lar',
        'status' => 'success',
        'details' => $authService->getActiveSessions($currentUser['id'])
    ];
} else {
    $testResults['debug'] = [
        'name' => 'Session Debug Bilgileri',
        'status' => 'warning',
        'details' => 'Kullanıcı giriş yapmamış'
    ];
}

// Test 5: Session güvenlik ayarları
$testResults['security'] = [
    'name' => 'Session Güvenlik Ayarları',
    'status' => 'success',
    'details' => [
        'ip_validation' => isset($_SESSION['user_ip']),
        'user_agent_validation' => isset($_SESSION['user_agent']),
        'last_regeneration' => $_SESSION['last_regeneration'] ?? null,
        'last_activity' => $_SESSION['user_last_activity'] ?? null
    ]
];

// Test işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'regenerate':
            if (class_exists('SessionConfig')) {
                SessionConfig::regenerateSession();
                $testResults['regenerate'] = [
                    'name' => 'Session Regeneration Test',
                    'status' => 'success',
                    'details' => 'Session ID başarıyla yenilendi'
                ];
            }
            break;
            
        case 'check_timeout':
            if (class_exists('SessionConfig')) {
                $timeout_result = SessionConfig::checkTimeout(1800);
                $testResults['timeout'] = [
                    'name' => 'Session Timeout Test',
                    'status' => $timeout_result ? 'success' : 'warning',
                    'details' => $timeout_result ? 'Session aktif' : 'Session timeout oldu'
                ];
            }
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session System Test - Bandland Shoes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">
                <i class="fas fa-shield-alt mr-2"></i>
                Session System Test
            </h1>
            
            <!-- Giriş Durumu -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-user-check mr-2"></i>
                    Giriş Durumu
                </h2>
                <?php if ($authService->isLoggedIn()): ?>
                    <?php $currentUser = $authService->getCurrentUser(); ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <p><strong>Giriş Yapılmış:</strong> <?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['email']); ?></p>
                        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
                        <p><strong>Giriş Zamanı:</strong> <?php echo date('Y-m-d H:i:s', $_SESSION['user_login_time'] ?? time()); ?></p>
                    </div>
                <?php else: ?>
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                        <p>Kullanıcı giriş yapmamış.</p>
                        <a href="login.php" class="text-blue-600 hover:text-blue-800 underline">Giriş Yap</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Test Butonları -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-flask mr-2"></i>
                    Test İşlemleri
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="regenerate">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                            <i class="fas fa-refresh mr-2"></i>
                            Session Regenerate
                        </button>
                    </form>
                    
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="check_timeout">
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">
                            <i class="fas fa-clock mr-2"></i>
                            Timeout Kontrol
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Test Sonuçları -->
            <div class="space-y-6">
                <?php foreach ($testResults as $testKey => $test): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <?php if ($test['status'] === 'success'): ?>
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <?php elseif ($test['status'] === 'warning'): ?>
                                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($test['name']); ?>
                        </h2>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <pre class="text-sm text-gray-700 overflow-x-auto"><?php echo htmlspecialchars(json_encode($test['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigasyon -->
            <div class="mt-8 text-center">
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded mr-4">
                    <i class="fas fa-home mr-2"></i>
                    Ana Sayfa
                </a>
                <?php if ($authService->isLoggedIn()): ?>
                    <a href="profile.php" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded">
                        <i class="fas fa-user mr-2"></i>
                        Profil
                    </a>
                <?php else: ?>
                    <a href="login.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Giriş Yap
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>