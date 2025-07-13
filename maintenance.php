<?php
// Bu sayfanın bakım modu kontrolünden etkilenmemesi için bir bayrak ayarla
define('MAINTENANCE_PAGE', true);

require_once 'includes/header.php';
?>

<main class="flex items-center justify-center min-h-[calc(100vh-200px)] bg-gray-50">
    <div class="text-center p-8">
        <div class="mb-8">
            <i class="fas fa-tools text-7xl text-primary opacity-50"></i>
        </div>
        <h1 class="text-5xl font-bold text-secondary mb-4">Çok Yakında Geri Döneceğiz!</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Sizlere daha iyi bir alışveriş deneyimi sunabilmek için sitemizde kısa bir bakım çalışması yapıyoruz.
            En kısa sürede yeniden hizmetinizde olacağız. Anlayışınız için teşekkür ederiz.
        </p>
        <div class="mt-10">
            <a href="/index.php"
                class="inline-block px-8 py-3 bg-primary text-white rounded-full font-semibold hover:bg-opacity-90 transition-all duration-300">
                Ana Sayfaya Dön
            </a>
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>