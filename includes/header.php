<?php
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
        $seo->setTitle('Bandland Shoes | Türkiye\'nin En Kaliteli Ayakkabı Markası', false)
            ->setDescription('Modern tasarım, konfor ve dayanıklılığı bir araya getiren ayakkabı koleksiyonları. En trend modeller ve uygun fiyatlarla.')
            ->setKeywords(['ayakkabı', 'spor ayakkabı', 'klasik ayakkabı', 'kadın ayakkabı', 'erkek ayakkabı', 'Türkiye'])
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/')
            ->setOpenGraph([
                'type' => 'website',
                'image' => '/assets/images/og-homepage.jpg'
            ])
            ->setTwitterCard([])
            ->addOrganizationSchema()
            ->addLocalBusinessSchema();
        break;

    case 'products.php':
        $seo->setTitle('Ayakkabı Koleksiyonu')
            ->setDescription('Geniş ayakkabı koleksiyonumuzdan size uygun modeli bulun. Spor, klasik, casual ve özel tasarım ayakkabılar.')
            ->setKeywords(['ayakkabı koleksiyonu', 'ayakkabı modelleri', 'online ayakkabı'])
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/products.php')
            ->setOpenGraph(['type' => 'website']);
        break;

    case 'about.php':
        $seo->setTitle('Hakkımızda')
            ->setDescription('Bandland Shoes\'un hikayesi, değerleri ve kalite anlayışı. Müşteri memnuniyeti odaklı hizmet yaklaşımımız.')
            ->setKeywords(['hakkımızda', 'bandland shoes', 'kalite', 'müşteri memnuniyeti'])
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/about.php');
        break;

    case 'blog.php':
        $seo->setTitle('Blog')
            ->setDescription('Ayakkabı bakımı, moda trendleri ve stil önerileri hakkında güncel yazılarımızı okuyun.')
            ->setKeywords(['ayakkabı blog', 'moda', 'stil önerileri', 'ayakkabı bakımı'])
            ->setCanonical('https://' . $_SERVER['HTTP_HOST'] . '/blog.php')
            ->setOpenGraph(['type' => 'website']);
        break;

    case 'contact.php':
        $seo->setTitle('İletişim')
            ->setDescription('Bizimle iletişime geçin. Mağaza adresimiz, telefon numaralarımız ve iletişim formu.')
            ->setKeywords(['iletişim', 'adres', 'telefon', 'mağaza'])
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
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <?php echo $seo->renderMetaTags(); ?>

    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">

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
                        primary: '#e91e63',
                        secondary: '#333',
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

        #mobile-menu {
            transition: all 0.3s ease;
            transform: translateY(-10px);
            opacity: 0;
        }

        #mobile-menu.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>

<body class="font-sans bg-gray-50">
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-8xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="logo">
                    <a href="/index.php">
                        <img src="/assets/images/mt-logo.png" alt="Bandland Shoes Logo" class="h-8 w-auto">
                    </a>
                </div>
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
                    <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <nav class="flex flex-col p-4 space-y-2"
                x-data="{ openProducts: false, activeMobileGender: '<?php echo !empty($mega_menu_genders) ? $mega_menu_genders[0]['slug'] : 'kadin'; ?>' }">
                <a href="/index.php"
                    class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'index.php') ? 'bg-primary text-white' : ''; ?>">Ana
                    Sayfa</a>

                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="openProducts = !openProducts"
                        class="w-full flex items-center justify-between text-left text-gray-600 hover:text-primary p-3 transition-colors <?php echo ($current_page == 'products.php') ? 'bg-primary text-white' : ''; ?>">
                        <span class="font-medium">Ürünler</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"
                            :class="{ 'rotate-180': openProducts }"></i>
                    </button>

                    <div x-show="openProducts" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 max-h-96" x-transition:leave-end="opacity-0 max-h-0"
                        class="bg-gray-50 overflow-hidden" style="display: none;">

                        <div class="border-b border-gray-200 p-3">
                            <div class="grid grid-cols-<?php echo count($gender_categories); ?> gap-1">
                                <?php foreach ($gender_categories as $slug => $gender): ?>
                                    <button @click="activeMobileGender = '<?php echo $slug; ?>'"
                                        class="px-3 py-2 text-xs rounded-lg transition-all duration-200"
                                        :class="activeMobileGender === '<?php echo $slug; ?>' ? 'bg-primary text-white' : 'bg-white text-gray-600 hover:bg-primary hover:text-white'">
                                        <?php echo htmlspecialchars($gender['name']); ?>
                                        <span
                                            class="block text-xs opacity-75">(<?php echo $gender['product_count']; ?>)</span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php foreach ($gender_categories as $slug => $gender): ?>
                            <div x-show="activeMobileGender === '<?php echo $slug; ?>'" class="p-3">
                                <div class="space-y-2">
                                    <a href="/products.php?genders[]=<?php echo $slug; ?>"
                                        class="block p-2 bg-primary text-white rounded-lg text-center text-sm font-medium hover:bg-primary-dark transition-colors">
                                        Tüm <?php echo htmlspecialchars($gender['name']); ?> Ürünleri
                                    </a>

                                    <div class="grid grid-cols-2 gap-2">
                                        <?php foreach (array_slice($gender['categories'], 0, 6) as $category): ?>
                                            <a href="/products.php?genders[]=<?php echo $slug; ?>&categories[]=<?php echo htmlspecialchars($category['category_slug']); ?>"
                                                class="block p-2 bg-white border border-gray-200 rounded-lg text-xs text-center hover:border-primary hover:bg-primary hover:text-white transition-all duration-200">
                                                <span
                                                    class="font-medium"><?php echo htmlspecialchars($category['category_name']); ?></span>
                                                <span
                                                    class="block text-xs opacity-75 mt-1">(<?php echo $category['product_count']; ?>)</span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="border-t border-gray-200 p-3 space-y-2">
                            <a href="/products.php"
                                class="block p-2 bg-gray-900 text-white rounded-lg text-center text-sm font-medium hover:bg-gray-800 transition-colors">
                                Tüm Ürünleri Gör
                            </a>
                            <a href="/products.php?featured=1"
                                class="block p-2 bg-gradient-to-r from-primary to-pink-500 text-white rounded-lg text-center text-sm font-medium hover:from-primary-dark hover:to-pink-600 transition-all">
                                Öne Çıkanlar
                            </a>
                        </div>
                    </div>
                </div>

                <a href="/about.php"
                    class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'about.php') ? 'bg-primary text-white' : ''; ?>">Hakkımızda</a>
                <a href="/blog.php"
                    class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'blog.php') ? 'bg-primary text-white' : ''; ?>">Blog</a>
                <a href="/contact.php"
                    class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'contact.php') ? 'bg-primary text-white' : ''; ?>">İletişim</a>

                <?php if ($is_logged_in): ?>
                    <div class="border-t pt-2 mt-2">
                        <div class="text-xs text-gray-500 px-2 mb-2">
                            <?php echo htmlspecialchars($current_user['full_name'] ?? $current_user['email']); ?>
                        </div>
                        <a href="/user/profile.php" class="text-gray-600 hover:text-primary p-2 rounded block"
                            data-no-transition="true" rel="nofollow">
                            <i class="fas fa-user mr-2"></i>Profilim
                        </a>
                        <a href="/user/profile.php?tab=favorites" class="text-gray-600 hover:text-primary p-2 rounded block"
                            data-no-transition="true" rel="nofollow">
                            <i class="fas fa-heart mr-2"></i>Favorilerim
                        </a>
                        <form action="/logout.php" method="POST" class="w-full">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <button type="submit"
                                class="w-full text-left text-gray-600 hover:text-primary p-2 rounded block"
                                data-no-transition="true" rel="nofollow">
                                <i class="fas fa-sign-out-alt mr-2"></i>Çıkış Yap
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="border-t pt-2 mt-2">
                        <a href="/login.php" class="text-gray-600 hover:text-primary p-2 rounded block">
                            <i class="fas fa-sign-in-alt mr-2"></i>Giriş Yap
                        </a>
                        <a href="/register.php" class="text-gray-600 hover:text-primary p-2 rounded block">
                            <i class="fas fa-user-plus mr-2"></i>Kayıt Ol
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main>