<?php

require_once 'services/AuthService.php';
require_once 'lib/helpers.php';

$authService = new AuthService();


$email = 'timcilol@gmail.com';
$password = 'password123'; // Test için basit bir şifre
$first_name = 'Test';
$last_name = 'User';
$phone_number = '05555555555';
$gender = 'Belirtmek İstemiyorum'; // Veritabanı ENUM değeri ile uyumlu hale getirildi

echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Otomatik Kullanıcı Oluşturma</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
</head>
<body class='bg-gray-100 flex items-center justify-center min-h-screen'>
    <div class='max-w-lg w-full bg-white rounded-lg shadow-lg p-8 text-center'>";


$db = database();
$existingUser = $db->select('users', ['email' => $email], 'id', ['limit' => 1]);

if (!empty($existingUser)) {

    echo "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4' role='alert'>
            <p class='font-bold'><i class='fas fa-info-circle mr-2'></i>Bilgi</p>
            <p><b>" . htmlspecialchars($email) . "</b> e-posta adresine sahip kullanıcı zaten mevcut.</p>
          </div>";
} else {

    $result = $authService->register($email, $password, [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'phone_number' => $phone_number,
        'gender' => $gender
    ]);

    if ($result['success']) {
        echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4' role='alert'>
                <p class='font-bold'><i class='fas fa-check-circle mr-2'></i>Başarılı!</p>
                <p>Test kullanıcısı başarıyla oluşturuldu.</p>
                <ul class='list-disc list-inside mt-2 text-left'>
                    <li><b>E-posta:</b> " . htmlspecialchars($email) . "</li>
                    <li><b>Şifre:</b> " . htmlspecialchars($password) . "</li>
                </ul>
              </div>";
    } else {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4' role='alert'>
                <p class='font-bold'><i class='fas fa-exclamation-triangle mr-2'></i>Hata!</p>
                <p>Kullanıcı oluşturulurken bir hata oluştu: " . htmlspecialchars($result['message']) . "</p>
              </div>";
    }
}

echo "<div class='mt-6'>
        <a href='login.php' class='text-blue-600 hover:text-blue-800 transition duration-200'>
            <i class='fas fa-sign-in-alt mr-1'></i>
            Giriş Sayfasına Git
        </a>
      </div>
    </div>
</body>
</html>";

?>