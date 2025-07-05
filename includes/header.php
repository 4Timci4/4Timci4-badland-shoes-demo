<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandland Shoes | Türkiye'nin En Kaliteli Ayakkabı Markası</title>
    
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
