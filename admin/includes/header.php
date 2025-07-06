<?php
require_once 'config/auth.php';

// Auth kontrolü yapılmışsa başlık bilgilerini al
$page_title = $page_title ?? 'Dashboard';
$breadcrumb_items = $breadcrumb_items ?? [];

$admin_info = get_admin_info();
$flash_message = get_flash_message();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern Admin Panel - Ürün ve İçerik Yönetimi">
    <meta name="author" content="Admin Panel">
    <title><?= get_page_title($page_title) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f0ff',
                            100: '#e0e1ff',
                            200: '#c7c8ff',
                            300: '#9e95f5',
                            400: '#7367f0',
                            500: '#5a52d6',
                            600: '#4b46c7',
                            700: '#3e3ba3',
                            800: '#35327a',
                            900: '#2e2b5e',
                        },
                        sidebar: '#2c2c54',
                        'sidebar-dark': '#1e1e3f',
                    },
                    fontFamily: {
                        'inter': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        'custom': '0 4px 25px 0 rgba(0, 0, 0, 0.1)',
                        'custom-lg': '0 8px 40px rgba(0, 0, 0, 0.12)',
                    }
                }
            }
        }
    </script>
    
    <!-- Additional CSS -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50 font-inter">
    
    <!-- Flash Messages -->
    <?php if ($flash_message): ?>
        <div id="flash-message" 
             class="fixed top-5 right-5 z-50 min-w-80 bg-white rounded-lg shadow-lg border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <?php if ($flash_message['type'] === 'success'): ?>
                        <i class="fas fa-check-circle text-green-500 text-lg"></i>
                    <?php elseif ($flash_message['type'] === 'error'): ?>
                        <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                    <?php else: ?>
                        <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                    <?php endif; ?>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($flash_message['message']) ?></p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Layout wrapper -->
    <div class="min-h-screen flex">
        
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-white shadow-lg border-r border-gray-200 flex flex-col transition-all duration-300 lg:translate-x-0 -translate-x-full fixed lg:relative z-40 h-screen">
            
            <!-- App Brand -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <a href="dashboard.php" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shoe-prints text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-900">Admin Panel</span>
                </a>
                
                <button id="sidebar-close" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Menu -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <?php 
                $menu_items = get_admin_menu();
                foreach ($menu_items as $item): 
                ?>
                    <?php if (isset($item['submenu'])): ?>
                        <!-- Menu with submenu -->
                        <div class="menu-item-submenu">
                            <button class="menu-toggle w-full flex items-center justify-between px-3 py-2 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3">
                                    <i class="<?= $item['icon'] ?> w-5 text-gray-500"></i>
                                    <span class="font-medium"><?= $item['title'] ?></span>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 transition-transform"></i>
                            </button>
                            
                            <div class="submenu hidden ml-8 mt-2 space-y-1">
                                <?php foreach ($item['submenu'] as $subitem): ?>
                                    <a href="<?= $subitem['url'] ?>" 
                                       class="block px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors <?= $subitem['active'] ?? false ? 'bg-primary-50 text-primary-700' : '' ?>">
                                        <?= $subitem['title'] ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Single menu item -->
                        <a href="<?= $item['url'] ?>" 
                           class="flex items-center space-x-3 px-3 py-2 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors <?= $item['active'] ?? false ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-500' : '' ?>">
                            <i class="<?= $item['icon'] ?> w-5 text-gray-500"></i>
                            <span class="font-medium"><?= $item['title'] ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <!-- Divider -->
                <div class="border-t border-gray-200 my-4"></div>
                
                <!-- Logout -->
                <a href="?action=logout" 
                   onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')"
                   class="flex items-center space-x-3 px-3 py-2 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="font-medium">Çıkış Yap</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen">
            
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    
                    <!-- Left side -->
                    <div class="flex items-center space-x-4">
                        <!-- Mobile menu button -->
                        <button id="sidebar-toggle" class="lg:hidden text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <!-- Breadcrumb -->
                        <nav class="hidden sm:flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2">
                                <?php 
                                $breadcrumb = get_breadcrumb($breadcrumb_items);
                                $last_index = count($breadcrumb) - 1;
                                foreach ($breadcrumb as $index => $crumb): 
                                ?>
                                    <li class="flex items-center">
                                        <?php if ($index > 0): ?>
                                            <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                                        <?php endif; ?>
                                        
                                        <?php if ($index === $last_index): ?>
                                            <span class="text-gray-700 font-medium">
                                                <i class="<?= $crumb['icon'] ?? 'fas fa-circle' ?> mr-2 text-xs"></i>
                                                <?= $crumb['title'] ?>
                                            </span>
                                        <?php else: ?>
                                            <a href="<?= $crumb['url'] ?>" class="text-gray-500 hover:text-gray-700 transition-colors">
                                                <i class="<?= $crumb['icon'] ?? 'fas fa-circle' ?> mr-2 text-xs"></i>
                                                <?= $crumb['title'] ?>
                                            </a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        
                        <!-- Search -->
                        <div class="relative hidden md:block">
                            <input type="text" 
                                   placeholder="Ara... (Ctrl+/)" 
                                   class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="relative">
                            <button id="quick-actions-toggle" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-plus-circle text-xl"></i>
                            </button>
                            
                            <div id="quick-actions-menu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                <div class="px-4 py-2 border-b border-gray-200">
                                    <h6 class="font-semibold text-gray-900">Hızlı Eylemler</h6>
                                </div>
                                <a href="product-add.php" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-plus text-primary-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Ürün Ekle</div>
                                        <div class="text-sm text-gray-500">Yeni ürün oluştur</div>
                                    </div>
                                </a>
                                <a href="blogs.php" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-edit text-green-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Blog Ekle</div>
                                        <div class="text-sm text-gray-500">Yeni yazı oluştur</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Notifications -->
                        <div class="relative">
                            <button id="notifications-toggle" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors relative">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                            </button>
                            
                            <div id="notifications-menu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                                    <h6 class="font-semibold text-gray-900">Bildirimler</h6>
                                    <span class="bg-primary-100 text-primary-800 text-xs px-2 py-1 rounded-full">3 yeni</span>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <div class="p-4 hover:bg-gray-50 transition-colors border-b border-gray-100">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-shopping-cart text-green-600 text-sm"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900">Yeni sipariş alındı</p>
                                                <p class="text-sm text-gray-500">2 dakika önce</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-envelope text-blue-600 text-sm"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900">Yeni mesaj</p>
                                                <p class="text-sm text-gray-500">15 dakika önce</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border-t border-gray-200">
                                    <a href="messages.php" class="block w-full text-center py-2 text-primary-600 hover:text-primary-700 font-medium transition-colors">
                                        Tümünü Gör
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button id="user-menu-toggle" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin_info['username']) ?>&background=7367f0&color=fff&size=32" 
                                     alt="Avatar" 
                                     class="w-8 h-8 rounded-full">
                                <div class="hidden sm:block text-left">
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($admin_info['username']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($admin_info['role']) ?></div>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                            </button>
                            
                            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($admin_info['username']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($admin_info['role']) ?></div>
                                </div>
                                <a href="settings.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-user mr-3 text-gray-400"></i>
                                    Profilim
                                </a>
                                <a href="settings.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-cog mr-3 text-gray-400"></i>
                                    Ayarlar
                                </a>
                                <div class="border-t border-gray-200 my-2"></div>
                                <a href="?action=logout" 
                                   onclick="return confirm('Çıkış yapmak istediğinize emin misiniz?')"
                                   class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    Çıkış Yap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 p-6">

<!-- Sidebar overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden"></div>

<!-- JavaScript for sidebar and dropdowns -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.remove('hidden');
    }
    
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
    }
    
    sidebarToggle.addEventListener('click', openSidebar);
    sidebarClose.addEventListener('click', closeSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);
    
    // Submenu toggle
    document.querySelectorAll('.menu-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const submenu = this.nextElementSibling;
            const chevron = this.querySelector('.fa-chevron-right');
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                chevron.style.transform = 'rotate(90deg)';
            } else {
                submenu.classList.add('hidden');
                chevron.style.transform = 'rotate(0deg)';
            }
        });
    });
    
    // Dropdown toggles
    function setupDropdown(toggleId, menuId) {
        const toggle = document.getElementById(toggleId);
        const menu = document.getElementById(menuId);
        
        if (toggle && menu) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function(e) {
                if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        }
    }
    
    setupDropdown('quick-actions-toggle', 'quick-actions-menu');
    setupDropdown('notifications-toggle', 'notifications-menu');
    setupDropdown('user-menu-toggle', 'user-menu');
    
    // Auto-hide flash messages
    setTimeout(() => {
        const flashMessage = document.getElementById('flash-message');
        if (flashMessage) {
            flashMessage.remove();
        }
    }, 5000);
});
</script>
