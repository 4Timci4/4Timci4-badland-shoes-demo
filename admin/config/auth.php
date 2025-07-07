<?php
/**
 * Admin Panel Authentication ve Configuration
 * 
 * Veritabanı tabanlı session yönetimi ve güvenlik kontrolleri
 */

// AdminAuthService'i dahil et
require_once __DIR__ . '/../../services/AdminAuthService.php';

// Global auth service instance
$auth_service = new AdminAuthService();

/**
 * Admin girişi kontrolü
 * Eğer kullanıcı giriş yapmamışsa login sayfasına yönlendir
 */
function check_admin_auth() {
    global $auth_service;
    
    // Timeout kontrolü yap
    if ($auth_service->checkTimeout()) {
        header('Location: index.php?timeout=1');
        exit;
    }
    
    // Giriş kontrolü
    if (!$auth_service->isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Admin çıkışı
 */
function admin_logout() {
    global $auth_service;
    $auth_service->destroySession();
    header('Location: index.php');
    exit;
}

/**
 * CSRF Token oluşturma
 */
function generate_csrf_token() {
    global $auth_service;
    return $auth_service->getCsrfToken();
}

/**
 * CSRF Token doğrulama
 */
function verify_csrf_token($token) {
    global $auth_service;
    return $auth_service->verifyCsrfToken($token);
}

/**
 * Sayfa başlığı belirleme
 */
function get_page_title($page = '') {
    $base_title = 'Admin Panel';
    return $page ? $page . ' - ' . $base_title : $base_title;
}

/**
 * Breadcrumb oluşturma
 */
function get_breadcrumb($items = []) {
    $breadcrumb = [
        ['title' => 'Ana Sayfa', 'url' => 'dashboard.php', 'icon' => 'fas fa-home']
    ];
    
    foreach ($items as $item) {
        $breadcrumb[] = $item;
    }
    
    return $breadcrumb;
}

/**
 * Flash mesaj sistemi
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message,
        'timestamp' => time()
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Admin bilgileri - Sadece veritabanından gerçek veriler
 */
function get_admin_info() {
    global $auth_service;
    
    $current_admin = $auth_service->getCurrentAdmin();
    
    if (!$current_admin) {
        return null; // Giriş yapılmamışsa null döndür
    }
    
    return [
        'id' => $current_admin['id'],
        'username' => $current_admin['username'],
        'full_name' => $current_admin['full_name'] ?? $current_admin['username'],
        'role' => 'Admin', // Tüm adminler aynı yetkide
        'last_login' => $current_admin['last_login'] ? date('d.m.Y H:i', strtotime($current_admin['last_login'])) : 'İlk giriş',
        'login_time' => $current_admin['login_time'] ? date('d.m.Y H:i', $current_admin['login_time']) : date('d.m.Y H:i')
    ];
}

/**
 * Aktif sayfa kontrolü
 */
function is_active_page($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return $current_page === $page;
}

/**
 * Menü öğelerini tanımlama
 */
function get_admin_menu() {
    return [
        [
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'url' => 'dashboard.php',
            'active' => is_active_page('dashboard.php')
        ],
        [
            'title' => 'Ürün Yönetimi',
            'icon' => 'fas fa-box',
            'submenu' => [
                [
                    'title' => 'Tüm Ürünler',
                    'url' => 'products.php',
                    'active' => is_active_page('products.php')
                ],
                [
                    'title' => 'Ürün Ekle',
                    'url' => 'product-add.php',
                    'active' => is_active_page('product-add.php')
                ],
                [
                    'title' => 'Kategoriler',
                    'url' => 'categories.php',
                    'active' => is_active_page('categories.php')
                ],
                [
                    'title' => 'Cinsiyetler',
                    'url' => 'genders.php',
                    'active' => is_active_page('genders.php')
                ],
                [
                    'title' => 'Renkler & Bedenler',
                    'url' => 'attributes.php',
                    'active' => is_active_page('attributes.php')
                ]
            ]
        ],
        [
            'title' => 'İçerik Yönetimi',
            'icon' => 'fas fa-edit',
            'submenu' => [
                [
                    'title' => 'Blog Yazıları',
                    'url' => 'blogs.php',
                    'active' => is_active_page('blogs.php') || is_active_page('blog-add.php') || is_active_page('blog-edit.php')
                ],
                [
                    'title' => 'Yeni Blog Yazısı',
                    'url' => 'blog-add.php',
                    'active' => is_active_page('blog-add.php')
                ],
                [
                    'title' => 'Slider Yönetimi',
                    'url' => 'sliders.php',
                    'active' => is_active_page('sliders.php') || is_active_page('slider-add.php') || is_active_page('slider-edit.php')
                ],
                [
                    'title' => 'Hakkında Sayfası',
                    'url' => 'about.php',
                    'active' => is_active_page('about.php') || is_active_page('about-content-add.php') || is_active_page('about-content-edit.php')
                ]
            ]
        ],
        [
            'title' => 'İletişim',
            'icon' => 'fas fa-envelope',
            'submenu' => [
                [
                    'title' => 'Gelen Mesajlar',
                    'url' => 'messages.php',
                    'active' => is_active_page('messages.php')
                ],
                [
                    'title' => 'İletişim Bilgileri',
                    'url' => 'contact-settings.php',
                    'active' => is_active_page('contact-settings.php')
                ]
            ]
        ],
        [
            'title' => 'Ayarlar',
            'icon' => 'fas fa-cog',
            'submenu' => [
                [
                    'title' => 'Genel Ayarlar',
                    'url' => 'settings.php',
                    'active' => is_active_page('settings.php')
                ],
                [
                    'title' => 'SEO Ayarları',
                    'url' => 'seo-settings.php',
                    'active' => is_active_page('seo-settings.php')
                ]
            ]
        ],
        [
            'title' => 'Admin Yönetimi',
            'icon' => 'fas fa-users-cog',
            'submenu' => [
                [
                    'title' => 'Tüm Adminler',
                    'url' => 'admins.php',
                    'active' => is_active_page('admins.php')
                ],
                [
                    'title' => 'Yeni Admin Ekle',
                    'url' => 'admin-add.php',
                    'active' => is_active_page('admin-add.php')
                ]
            ]
        ]
    ];
}

/**
 * İstatistik verileri (dashboard için) - Supabase'den gerçek veriler
 */
function get_dashboard_stats() {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        // Bu ayın başlangıç tarihi
        $current_month_start = date('Y-m-01 00:00:00');
        
        // Toplam ürün sayısı
        $products_response = supabase()->request('product_models?select=id');
        $total_products = count($products_response['body'] ?? []);
        
        // Bu ay eklenen ürünler
        $monthly_products_response = supabase()->request("product_models?select=id&created_at=gte.$current_month_start");
        $monthly_products = count($monthly_products_response['body'] ?? []);
        
        // Toplam kategori sayısı
        $categories_response = supabase()->request('categories?select=id');
        $total_categories = count($categories_response['body'] ?? []);
        
        // Toplam blog sayısı
        $blogs_response = supabase()->request('blogs?select=id');
        $total_blogs = count($blogs_response['body'] ?? []);
        
        // Bu ay eklenen bloglar
        $monthly_blogs_response = supabase()->request("blogs?select=id&created_at=gte.$current_month_start");
        $monthly_blogs = count($monthly_blogs_response['body'] ?? []);
        
        $total_messages = 0;
        $pending_messages = 0;
        
        // Öne çıkan ürünler
        $featured_products_response = supabase()->request('product_models?select=id&is_featured=eq.true');
        $featured_products = count($featured_products_response['body'] ?? []);
        
        return [
            'total_products' => $total_products,
            'total_categories' => $total_categories,
            'total_blogs' => $total_blogs,
            'total_messages' => $total_messages,
            'monthly_products' => $monthly_products,
            'monthly_blogs' => $monthly_blogs,
            'pending_messages' => $pending_messages,
            'featured_products' => $featured_products
        ];
        
    } catch (Exception $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
        
        // Hata durumunda fallback veriler
        return [
            'total_products' => 0,
            'total_categories' => 0,
            'total_blogs' => 0,
            'total_messages' => 0,
            'monthly_products' => 0,
            'monthly_blogs' => 0,
            'pending_messages' => 0,
            'featured_products' => 0
        ];
    }
}

/**
 * Son eklenen ürünleri getir
 */
function get_recent_products($limit = 5) {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $response = supabase()->request("product_models?select=*,categories(name)&order=created_at.desc&limit=$limit");
        return $response['body'] ?? [];
    } catch (Exception $e) {
        error_log("Recent products error: " . $e->getMessage());
        return [];
    }
}

/**
 * Son eklenen blog yazılarını getir
 */
function get_recent_blogs($limit = 5) {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $response = supabase()->request("blogs?select=id,title,excerpt,category,created_at&order=created_at.desc&limit=$limit");
        return $response['body'] ?? [];
    } catch (Exception $e) {
        error_log("Recent blogs error: " . $e->getMessage());
        return [];
    }
}

/**
 * Logout işlemi
 */
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Session'ı tamamen yok et
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Tüm session verilerini temizle
    session_unset();
    session_destroy();
    
    // Yeni session başlat ve güvenlik için
    session_start();
    session_regenerate_id(true);
    
    // Login sayfasına yönlendir
    header('Location: index.php?logout=1');
    exit;
}

// Auto-logout after 2 hours of inactivity
if (isset($_SESSION['admin_last_activity'])) {
    if (time() - $_SESSION['admin_last_activity'] > 7200) { // 2 hours
        session_destroy();
        header('Location: index.php?timeout=1');
        exit;
    }
}

// Update last activity time
$_SESSION['admin_last_activity'] = time();
?>
