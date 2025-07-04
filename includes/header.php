<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schön | Türkiye'nin En Kaliteli Ayakkabı Markası</title>
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
                        dark: '#222'
                    }
                }
            }
        }
    </script>
</head>
<body>
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-5">
            <div class="flex justify-between items-center py-5">
                <div class="logo -ml-20">
                    <a href="/index.php">
                        <img src="/assets/images/mt-logo.png" alt="Schön Logo" class="h-8 w-auto block md:h-8">
                    </a>
                </div>
                <nav class="hidden md:block">
                    <ul class="flex space-x-5">
                        <li><a href="/index.php" class="px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'index.php') ? 'text-primary' : 'text-secondary'; ?>">Ana Sayfa</a></li>
                        <li><a href="/products.php" class="px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'products.php') ? 'text-primary' : 'text-secondary'; ?>">Ürünler</a></li>
                        <li><a href="/about.php" class="px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'about.php') ? 'text-primary' : 'text-secondary'; ?>">Hakkımızda</a></li>
                        <li><a href="/blog.php" class="px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'blog.php') ? 'text-primary' : 'text-secondary'; ?>">Blog</a></li>
                        <li><a href="/contact.php" class="px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'contact.php') ? 'text-primary' : 'text-secondary'; ?>">İletişim</a></li>
                    </ul>
                </nav>
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-secondary hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-5">
                <ul class="flex flex-col space-y-2">
                    <li><a href="/index.php" class="block px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'index.php') ? 'text-primary' : 'text-secondary'; ?>">Ana Sayfa</a></li>
                    <li><a href="/products.php" class="block px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'products.php') ? 'text-primary' : 'text-secondary'; ?>">Ürünler</a></li>
                    <li><a href="/about.php" class="block px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'about.php') ? 'text-primary' : 'text-secondary'; ?>">Hakkımızda</a></li>
                    <li><a href="/blog.php" class="block px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'blog.php') ? 'text-primary' : 'text-secondary'; ?>">Blog</a></li>
                    <li><a href="/contact.php" class="block px-2.5 py-2.5 transition-all duration-300 hover:text-primary <?php echo ($current_page == 'contact.php') ? 'text-primary' : 'text-secondary'; ?>">İletişim</a></li>
                </ul>
            </div>
        </div>
    </header>
    <main>