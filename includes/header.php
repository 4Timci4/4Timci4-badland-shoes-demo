<?php
// Session'ı sadece gerekirse başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

// SEO Manager'ı dahil et
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/SEOManager.php';
$seo = seo();

// Sayfa bazlı SEO ayarları
switch($current_page) {
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

// Breadcrumb için sayfa bazlı tanımlar
$breadcrumbs = [];
switch($current_page) {
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
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    }
                }
            }
        }
    </script>
    
    <!-- Smooth Page Transition CSS -->
    <style>
        /* Body fade transition */
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
        
        /* Loading spinner */
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Header links smooth hover */
        header nav a {
            transition: all 0.3s ease;
        }
        
        /* Mobile menu smooth animation */
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
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="logo">
                    <a href="/index.php">
                        <img src="/assets/images/mt-logo.png" alt="Bandland Shoes Logo" class="h-8 w-auto">
                    </a>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="/index.php" class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'index.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">Ana Sayfa</a>
                    <a href="/products.php" class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'products.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">Ürünler</a>
                    <a href="/about.php" class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'about.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">Hakkımızda</a>
                    <a href="/blog.php" class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'blog.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">Blog</a>
                    <a href="/contact.php" class="text-gray-600 hover:text-primary transition-colors duration-300 font-medium pb-2 border-b-2 <?php echo ($current_page == 'contact.php') ? 'border-primary text-primary' : 'border-transparent'; ?>">İletişim</a>
                </nav>
                <div class="flex items-center space-x-4">
                    <a href="#" class="text-gray-600 hover:text-primary"><i class="fas fa-search"></i></a>
                    <a href="#" class="text-gray-600 hover:text-primary"><i class="fas fa-user"></i></a>
                    <a href="#" class="text-gray-600 hover:text-primary relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="absolute -top-2 -right-2 bg-primary text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                    </a>
                    <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <nav class="flex flex-col p-4 space-y-2">
                <a href="/index.php" class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'index.php') ? 'bg-primary text-white' : ''; ?>">Ana Sayfa</a>
                <a href="/products.php" class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'products.php') ? 'bg-primary text-white' : ''; ?>">Ürünler</a>
                <a href="/about.php" class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'about.php') ? 'bg-primary text-white' : ''; ?>">Hakkımızda</a>
                <a href="/blog.php" class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'blog.php') ? 'bg-primary text-white' : ''; ?>">Blog</a>
                <a href="/contact.php" class="text-gray-600 hover:text-primary p-2 rounded <?php echo ($current_page == 'contact.php') ? 'bg-primary text-white' : ''; ?>">İletişim</a>
            </nav>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
