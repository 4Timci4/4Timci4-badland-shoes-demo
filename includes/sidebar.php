<?php



$active_page = $active_page ?? 'profile';


$csrf_token = '';
if (isset($authService) && method_exists($authService, 'generateCsrfToken')) {
    $csrf_token = $authService->generateCsrfToken();
}
?>

<!-- Sidebar -->
<aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
    <nav class="space-y-1">
        <a href="/user/profile.php"
            class="<?php echo $active_page === 'profile' ? 'bg-gray-50 text-gray-900' : 'text-gray-600 hover:text-gray-900'; ?> group rounded-md px-3 py-2 flex items-center text-sm font-medium">
            <i class="fas fa-user text-gray-400 group-hover:text-gray-500 flex-shrink-0 -ml-1 mr-3 h-5 w-5"></i>
            <span class="truncate">Profil Bilgileri</span>
        </a>
        <a href="/user/favorites.php"
            class="<?php echo $active_page === 'favorites' ? 'bg-gray-50 text-gray-900' : 'text-gray-600 hover:text-gray-900'; ?> group rounded-md px-3 py-2 flex items-center text-sm font-medium">
            <i class="fas fa-heart text-gray-400 group-hover:text-gray-500 flex-shrink-0 -ml-1 mr-3 h-5 w-5"></i>
            <span class="truncate">Favorilerim</span>
        </a>
        <form action="/logout.php" method="POST" class="w-full">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit"
                class="w-full text-red-600 hover:text-red-900 group rounded-md px-3 py-2 flex items-center text-sm font-medium">
                <i
                    class="fas fa-sign-out-alt text-red-400 group-hover:text-red-500 flex-shrink-0 -ml-1 mr-3 h-5 w-5"></i>
                <span class="truncate">Çıkış Yap</span>
            </button>
        </form>
    </nav>
</aside>