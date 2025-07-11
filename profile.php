<?php
/**
 * User Profile Management Page
 * SESSION KALDIRILD - Bu sayfa artık çalışmaz
 */
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
        <div class="text-center">
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md max-w-md mx-auto" role="alert">
                <h2 class="text-lg font-bold mb-2">Profil Sayfası Erişilemez</h2>
                <p>Session yönetimi kaldırıldığı için bu sayfa artık çalışmamaktadır.</p>
                <p class="mt-2">
                    <a href="login.php" class="text-red-800 underline">Giriş sayfasına dönün</a>
                </p>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
