<?php

require_once 'services/AdminAuthService.php';


$adminAuth = new AdminAuthService();


$admin_data = [
    'username' => 'admin',
    'password' => 'admin',
    'full_name' => 'Sistem Yöneticisi',
    'email' => 'admin@example.com',
    'is_active' => 1
];

echo "Admin kullanıcısı oluşturuluyor...\n";


$result = $adminAuth->createAdmin($admin_data);

if (isset($result['success']) && $result['success']) {
    echo "✅ Admin kullanıcısı başarıyla oluşturuldu!\n";
    echo "📋 Kullanıcı adı: admin\n";
    echo "🔑 Şifre: admin\n";
    echo "🆔 Admin ID: " . $result['id'] . "\n";
} else {
    echo "❌ Hata: " . ($result['error'] ?? 'Bilinmeyen hata') . "\n";
}


$admin_count = $adminAuth->getAdminCount();
echo "\n📊 Toplam admin sayısı: " . $admin_count . "\n";


echo "\n👥 Mevcut adminler:\n";
$all_admins = $adminAuth->getAllAdmins();
foreach ($all_admins as $admin) {
    $status = $admin['is_active'] ? '✅ Aktif' : '❌ Pasif';
    echo "- {$admin['username']} ({$admin['full_name']}) - {$status}\n";
}

?>