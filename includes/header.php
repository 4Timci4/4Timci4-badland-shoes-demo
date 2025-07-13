<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../lib/SEOManager.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/GenderService.php';
require_once __DIR__ . '/../services/CategoryService.php';

$current_page = basename($_SERVER['PHP_SELF']);
$seo = seo();
$authService = new AuthService();

$genderService = gender_service();
$categoryService = category_service();

$mega_menu_genders = $genderService->getGendersWithProductCounts();
$mega_menu_categories = $categoryService->getCategoriesWithProductCountsOptimized(true);

$gender_categories = [];
foreach ($mega_menu_genders as $gender) {
    $gender_categories[$gender['slug']] = [
        'id' => $gender['id'],
        'name' => $gender['name'],
        'slug' => $gender['slug'],
        'product_count' => $gender['product_count'],
        'categories' => []
    ];
}

foreach ($mega_menu_genders as $gender) {
    $gender_categories[$gender['slug']]['categories'] = array_slice($mega_menu_categories, 0, 8);
}

$authService->checkSessionSecurity();

if (!$authService->isLoggedIn()) {
    $authService->loginWithRememberMeCookie();
}

$is_logged_in = $authService->isLoggedIn();
$current_user = $is_logged_in ? $authService->getCurrentUser() : null;

$csrf_token = '';
if ($is_logged_in) {
    $csrf_token = $authService->generateCsrfToken();
}

switch ($current_page) {
    case 'index.php':
        // Varsayılan SEO ayarlarının (veritabanından gelen) kullanılmasını sağlamak için
        // özel setTitle ve setDescription çağrıları kaldırıldı.
        // SEOManager artık bu sayfa için varsayılanları otomatik olarak kullanacak.
        $seo->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/')
            ->setOpenGraph([
                'type' => 'website',
                'image' => '/assets/images/og-homepage.jpg'
            ]);
        break;

    case 'products.php':
        $seo->setTitle('Ayakkabı Koleksiyonu')
            ->setDescription('Geniş ayakkabı koleksiyonumuzdan size uygun modeli bulun. Spor, klasik, casual ve özel tasarım ayakkabılar.')
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/products.php')
            ->setOpenGraph(['type' => 'website']);
        break;

    case 'about.php':
        $seo->setTitle('Hakkımızda')
            ->setDescription('Bandland Shoes\'un hikayesi, değerleri ve kalite anlayışı. Müşteri memnuniyeti odaklı hizmet yaklaşımımız.')
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/about.php');
        break;

    case 'blog.php':
        $seo->setTitle('Blog')
            ->setDescription('Ayakkabı bakımı, moda trendleri ve stil önerileri hakkında güncel yazılarımızı okuyun.')
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/blog.php')
            ->setOpenGraph(['type' => 'website']);
        break;

    case 'contact.php':
        $seo->setTitle('İletişim')
            ->setDescription('Bizimle iletişime geçin. Mağaza adresimiz, telefon numaralarımız ve iletişim formu.')
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/contact.php');
        break;

    default:
        $seo->setTitle('Bandland Shoes', false)
            ->setDescription('Türkiye\'nin en kaliteli ayakkabı markası')
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
}

$breadcrumbs = [];
switch ($current_page) {
    case 'products.php':
        $breadcrumbs = [
            ['name' => 'Ana Sayfa', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/'],
            ['name' => 'Ürünler', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/products.php']
        ];
        break;
    case 'about.php':
        $breadcrumbs = [
            ['name' => 'Ana Sayfa', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/'],
            ['name' => 'Hakkımızda', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/about.php']
        ];
        break;
    case 'blog.php':
        $breadcrumbs = [
            ['name' => 'Ana Sayfa', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/'],
            ['name' => 'Blog', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/blog.php']
        ];
        break;
    case 'contact.php':
        $breadcrumbs = [
            ['name' => 'Ana Sayfa', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/'],
            ['name' => 'İletişim', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/contact.php']
        ];
        break;
}

if (!empty($breadcrumbs)) {
    $seo->addBreadcrumbSchema($breadcrumbs);
}

require_once __DIR__ . '/../services/SettingsService.php';
$settingsService = new SettingsService();
$primary_color = $settingsService->getSiteSetting('primary_color', '#e91e63');
$secondary_color = $settingsService->getSiteSetting('secondary_color', '#333');
$site_logo = $settingsService->getSiteSetting('site_logo', '/assets/images/mt-logo.png');
$site_favicon = $settingsService->getSiteSetting('site_favicon', '/favicon.ico');
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <?php echo $seo->renderMetaTags(); ?>

    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($site_favicon); ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo htmlspecialchars($site_favicon); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '<?php echo $primary_color; ?>',
                        secondary: '<?php echo $secondary_color; ?>',
                        light: '#f4f4f4',
                        dark: '#222',
                        brand: '#8BFD87'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'display': ['Poppins', 'sans-serif'],
                    },
                    maxWidth: {
                        '8xl': '88rem',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            opacity: 0;
            transition: opacity 0.4s ease-in-out;
        }

        body.loaded {
            opacity: 1;
        }

        body.fade-out {
            opacity: 0;
        }

        .page-loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .page-loading.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #e91e63;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        header nav a {
            transition: all 0.3s ease;
        }

        /* Mobile menu body scroll lock */
        body.mobile-menu-open {
            overflow: hidden;
        }

        /* Mobile menu improvements */
        .mobile-menu-overlay {
            backdrop-filter: blur(4px);
        }

        /* Smooth transitions for mobile menu */
        .mobile-menu {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .mobile-menu::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="font-sans bg-gray-50">
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-8xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="logo">
                    <a href="/index.php">
                        <img src="<?php echo htmlspecialchars($site_logo); ?>"
                            alt="<?php echo htmlspecialchars($settingsService->getSiteSetting('site_name', 'Bandland Shoes')); ?> Logo"
                            class="h-8 w-auto">
                    </a>
                </div>
                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="/index.php"
                        class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'index.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">Ana
                        Sayfa</a>

                    <div x-data="{
                        open: false,
                        activeGender: '<?php echo !empty($mega_menu_genders) ? $mega_menu_genders[0]['slug'] : 'kadin'; ?>',
                        openTimeout: null,
                        closeTimeout: null
                    }" class="relative group">
                        <a href="/products.php"
                            @mouseenter="clearTimeout(closeTimeout); openTimeout = setTimeout(() => open = true, 150)"
                            @mouseleave="clearTimeout(openTimeout); closeTimeout = setTimeout(() => open = false, 300)"
                            class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 flex items-center gap-1 <?php echo ($current_page == 'products.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">
                            Ürünler
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200"
                                :class="{ 'rotate-180': open }"></i>
                        </a>

                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-95"
                            @mouseenter="clearTimeout(closeTimeout); clearTimeout(openTimeout)"
                            @mouseleave="closeTimeout = setTimeout(() => open = false, 300)"
                            class="absolute left-1/2 transform -translate-x-1/2 mt-2 w-screen max-w-6xl bg-white rounded-lg shadow-2xl border border-gray-100 z-50"
                            style="display: none;">

                            <div class="p-8">
                                <div class="grid grid-cols-12 gap-8">
                                    <div class="col-span-3">
                                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                                            CİNSİYET</h3>
                                        <div class="space-y-2">
                                            <?php foreach ($gender_categories as $slug => $gender): ?>
                                                <button @click="activeGender = '<?php echo $slug; ?>'"
                                                    class="w-full text-left px-4 py-3 rounded-lg transition-all duration-200 group/gender"
                                                    :class="activeGender === '<?php echo $slug; ?>' ? 'bg-primary text-white shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-primary'">
                                                    <div class="flex items-center justify-between">
                                                        <span
                                                            class="font-medium"><?php echo htmlspecialchars($gender['name']); ?></span>
                                                        <span class="text-xs px-2 py-1 rounded-full"
                                                            :class="activeGender === '<?php echo $slug; ?>' ? 'bg-white bg-opacity-20 text-white' : 'bg-gray-100 text-gray-500 group-hover/gender:bg-primary group-hover/gender:text-white'">
                                                            <?php echo $gender['product_count']; ?>
                                                        </span>
                                                    </div>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="mt-6 pt-4 border-t border-gray-200">
                                            <a href="/products.php"
                                                class="block w-full text-center px-4 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors duration-200 font-medium">
                                                Tüm Ürünleri Gör
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-span-6">
                                        <?php foreach ($gender_categories as $slug => $gender): ?>
                                            <div x-show="activeGender === '<?php echo $slug; ?>'"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 transform translate-y-2"
                                                x-transition:enter-end="opacity-100 transform translate-y-0">
                                                <h3
                                                    class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                                                    <?php echo strtoupper(htmlspecialchars($gender['name'])); ?>
                                                    KATEGORİLERİ
                                                </h3>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <?php foreach ($gender['categories'] as $category): ?>
                                                        <a href="/products.php?genders[]=<?php echo $slug; ?>&categories[]=<?php echo htmlspecialchars($category['category_slug']); ?>"
                                                            class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:border-primary hover:bg-primary hover:text-white transition-all duration-200 group/cat">
                                                            <div>
                                                                <span
                                                                    class="font-medium text-sm"><?php echo htmlspecialchars($category['category_name']); ?></span>
                                                            </div>
                                                            <span
                                                                class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500 group-hover/cat:bg-white group-hover/cat:bg-opacity-20 group-hover/cat:text-white">
                                                                <?php echo $category['product_count']; ?>
                                                            </span>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>

                                                <div class="mt-4">
                                                    <a href="/products.php?genders[]=<?php echo $slug; ?>"
                                                        class="inline-flex items-center text-primary hover:text-primary-dark font-medium text-sm transition-colors duration-200">
                                                        Tüm <?php echo htmlspecialchars($gender['name']); ?> Ürünleri
                                                        <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="col-span-3">
                                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                                            ÖNE ÇIKANLAR</h3>
                                        <div class="space-y-4">
                                            <div
                                                class="bg-gradient-to-br from-primary to-pink-500 rounded-lg p-6 text-white text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-fire text-2xl mb-2"></i>
                                                    <h4 class="font-bold text-lg">Yeni Koleksiyon</h4>
                                                </div>
                                                <p class="text-sm mb-4 opacity-90">2025 Bahar koleksiyonumuz yayında!
                                                </p>
                                                <a href="/products.php?featured=1"
                                                    class="inline-block bg-white text-primary px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-100 transition-colors">
                                                    Keşfet
                                                </a>
                                            </div>

                                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                                <div class="mb-3">
                                                    <i class="fas fa-truck text-primary text-xl mb-2"></i>
                                                    <h4 class="font-semibold text-gray-900">Ücretsiz Kargo</h4>
                                                </div>
                                                <p class="text-xs text-gray-600">250₺ ve üzeri alışverişlerde</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="/about.php"
                        class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'about.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">Hakkımızda</a>
                    <a href="/blog.php"
                        class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'blog.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">Blog</a>
                    <a href="/contact.php"
                        class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'contact.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">İletişim</a>
                </nav>

                <div class="flex items-center space-x-4">
                    <!-- Mobile Menu Button -->
                    <button x-data="{ mobileMenuOpen: false }"
                        @click="mobileMenuOpen = !mobileMenuOpen; $dispatch('mobile-menu-toggle', { open: mobileMenuOpen })"
                        class="md:hidden flex items-center justify-center w-10 h-10 text-gray-600 hover:text-primary focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <?php if ($is_logged_in): ?>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                class="flex items-center space-x-2 text-gray-600 hover:text-primary focus:outline-none">
                                <i class="fas fa-user-circle text-2xl"></i>
                                <span class="hidden sm:inline-block text-sm font-medium">
                                    <?php echo htmlspecialchars($current_user['full_name'] ?? $current_user['email']); ?>
                                </span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5"
                                style="display: none;">
                                <a href="/user/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    data-no-transition="true" rel="nofollow">Profilim</a>
                                <a href="/user/profile.php?tab=favorites"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    data-no-transition="true" rel="nofollow">Favorilerim</a>
                                <form action="/logout.php" method="POST" class="w-full">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit"
                                        class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        data-no-transition="true" rel="nofollow">Çıkış Yap</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-gray-600 hover:text-primary focus:outline-none">
                                <i class="fas fa-user text-2xl"></i>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5"
                                style="display: none;">
                                <a href="/login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Giriş
                                    Yap</a>
                                <a href="/register.php"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Kayıt Ol</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div x-data="{ mobileMenuOpen: false }"
            @mobile-menu-toggle.window="mobileMenuOpen = $event.detail.open; document.body.classList.toggle('mobile-menu-open', mobileMenuOpen)"
            x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden mobile-menu-overlay" style="display: none;"
            @click="mobileMenuOpen = false; $dispatch('mobile-menu-toggle', { open: false })">
        </div>

        <!-- Mobile Menu -->
        <div x-data="{
                mobileMenuOpen: false,
                activeSubmenu: null,
                activeGender: '<?php echo !empty($mega_menu_genders) ? $mega_menu_genders[0]['slug'] : 'kadin'; ?>'
             }"
            @mobile-menu-toggle.window="mobileMenuOpen = $event.detail.open; if (!mobileMenuOpen) { activeSubmenu = null; }"
            x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed top-0 left-0 w-80 h-full bg-white shadow-xl z-50 md:hidden overflow-y-auto"
            style="display: none;" @click.stop>

            <!-- Mobile Menu Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <img src="<?php echo htmlspecialchars($site_logo); ?>"
                    alt="<?php echo htmlspecialchars($settingsService->getSiteSetting('site_name', 'Bandland Shoes')); ?> Logo"
                    class="h-8 w-auto">
                <button @click="mobileMenuOpen = false; $dispatch('mobile-menu-toggle', { open: false })"
                    class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-primary">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Mobile Menu Content -->
            <div class="p-4">
                <!-- Main Navigation -->
                <nav class="space-y-2">
                    <a href="/index.php"
                        class="block py-3 px-4 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors <?php echo ($current_page == 'index.php') ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-home w-5 mr-3"></i>Ana Sayfa
                    </a>

                    <!-- Products with Submenu -->
                    <div class="relative">
                        <button @click="activeSubmenu = activeSubmenu === 'products' ? null : 'products'"
                            class="w-full flex items-center justify-between py-3 px-4 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors <?php echo ($current_page == 'products.php') ? 'bg-primary text-white' : ''; ?>">
                            <span><i class="fas fa-shopping-bag w-5 mr-3"></i>Ürünler</span>
                            <i class="fas fa-chevron-down text-sm transition-transform duration-200"
                                :class="{ 'rotate-180': activeSubmenu === 'products' }"></i>
                        </button>

                        <div x-show="activeSubmenu === 'products'" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 max-h-96" x-transition:leave-end="opacity-0 max-h-0"
                            class="overflow-hidden" style="display: none;">
                            <div class="mt-2 ml-4 space-y-2">
                                <a href="/products.php"
                                    class="block py-2 px-4 text-sm text-gray-600 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors">
                                    Tüm Ürünler
                                </a>

                                <!-- Gender Categories -->
                                <?php foreach ($gender_categories as $slug => $gender): ?>
                                    <div class="border-l-2 border-gray-100 pl-4">
                                        <div class="py-2">
                                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                                <?php echo htmlspecialchars($gender['name']); ?>
                                                (<?php echo $gender['product_count']; ?>)
                                            </span>
                                        </div>
                                        <div class="space-y-1">
                                            <a href="/products.php?genders[]=<?php echo $slug; ?>"
                                                class="block py-1 px-3 text-sm text-gray-600 hover:bg-gray-50 hover:text-primary rounded transition-colors">
                                                Tüm <?php echo htmlspecialchars($gender['name']); ?> Ürünleri
                                            </a>
                                            <?php foreach (array_slice($gender['categories'], 0, 4) as $category): ?>
                                                <a href="/products.php?genders[]=<?php echo $slug; ?>&categories[]=<?php echo htmlspecialchars($category['category_slug']); ?>"
                                                    class="block py-1 px-3 text-sm text-gray-600 hover:bg-gray-50 hover:text-primary rounded transition-colors">
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                    (<?php echo $category['product_count']; ?>)
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="pt-2 border-t border-gray-100">
                                    <a href="/products.php?featured=1"
                                        class="block py-2 px-4 text-sm text-primary font-medium hover:bg-primary hover:text-white rounded-lg transition-colors">
                                        <i class="fas fa-fire mr-2"></i>Öne Çıkanlar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="/about.php"
                        class="block py-3 px-4 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors <?php echo ($current_page == 'about.php') ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-info-circle w-5 mr-3"></i>Hakkımızda
                    </a>

                    <a href="/blog.php"
                        class="block py-3 px-4 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors <?php echo ($current_page == 'blog.php') ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-blog w-5 mr-3"></i>Blog
                    </a>

                    <a href="/contact.php"
                        class="block py-3 px-4 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors <?php echo ($current_page == 'contact.php') ? 'bg-primary text-white' : ''; ?>">
                        <i class="fas fa-envelope w-5 mr-3"></i>İletişim
                    </a>
                </nav>

                <!-- User Section -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <?php if ($is_logged_in): ?>
                        <div class="space-y-2">
                            <div class="flex items-center px-4 py-3 bg-gray-50 rounded-lg">
                                <i class="fas fa-user-circle text-2xl text-primary mr-3"></i>
                                <div>
                                    <div class="font-medium text-gray-900 text-sm">
                                        <?php echo htmlspecialchars($current_user['full_name'] ?? $current_user['email']); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">Hoş geldiniz</div>
                                </div>
                            </div>

                            <a href="/user/profile.php"
                                class="block py-2 px-4 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors">
                                <i class="fas fa-user w-5 mr-3"></i>Profilim
                            </a>

                            <a href="/user/profile.php?tab=favorites"
                                class="block py-2 px-4 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors">
                                <i class="fas fa-heart w-5 mr-3"></i>Favorilerim
                            </a>

                            <form action="/logout.php" method="POST" class="w-full">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit"
                                    class="w-full text-left py-2 px-4 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition-colors">
                                    <i class="fas fa-sign-out-alt w-5 mr-3"></i>Çıkış Yap
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <a href="/login.php"
                                class="block py-3 px-4 bg-primary text-white text-center rounded-lg hover:bg-primary-dark transition-colors font-medium">
                                <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                            </a>

                            <a href="/register.php"
                                class="block py-3 px-4 border border-primary text-primary text-center rounded-lg hover:bg-primary hover:text-white transition-colors font-medium">
                                <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>


    <main>