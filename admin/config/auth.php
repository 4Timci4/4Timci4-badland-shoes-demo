<?php
/**
 * Admin Panel Authentication ve Configuration
 * 
 * Session yönetimi ve güvenlik kontrolleri
 */

// Session başlatma
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Admin girişi kontrolü
 * Eğer kullanıcı giriş yapmamışsa login sayfasına yönlendir
 */
function check_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Admin çıkışı
 */
function admin_logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

/**
 * CSRF Token oluşturma
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token doğrulama
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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
 * Admin bilgileri
 */
function get_admin_info() {
    return [
        'username' => $_SESSION['admin_username'] ?? 'Admin',
        'email' => 'admin@example.com',
        'avatar' => 'assets/img/avatars/admin.jpg',
        'role' => 'Super Admin',
        'last_login' => $_SESSION['admin_last_login'] ?? date('Y-m-d H:i:s')
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
                    'active' => is_active_page('blogs.php')
                ],
                [
                    'title' => 'Slider Yönetimi',
                    'url' => 'sliders.php',
                    'active' => is_active_page('sliders.php')
                ],
                [
                    'title' => 'Hakkında Sayfası',
                    'url' => 'about.php',
                    'active' => is_active_page('about.php')
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
        $products_response = supabase()->request('products?select=id');
        $total_products = count($products_response['body'] ?? []);
        
        // Bu ay eklenen ürünler
        $monthly_products_response = supabase()->request("products?select=id&created_at=gte.$current_month_start");
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
        
        // Toplam mesaj sayısı
        $messages_response = supabase()->request('contacts?select=id');
        $total_messages = count($messages_response['body'] ?? []);
        
        // Bekleyen mesajlar (is_read = false)
        $pending_messages_response = supabase()->request('contacts?select=id&is_read=eq.false');
        $pending_messages = count($pending_messages_response['body'] ?? []);
        
        // Öne çıkan ürünler
        $featured_products_response = supabase()->request('products?select=id&is_featured=eq.true');
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
        $response = supabase()->request("products?select=*,categories(name)&order=created_at.desc&limit=$limit");
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
 * Son aktiviteleri getir
 */
function get_recent_activities($limit = 10) {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $activities = [];
        
        // Son eklenen ürünler
        $products_response = supabase()->request("products?select=name,created_at&order=created_at.desc&limit=3");
        if ($products_response['body']) {
            foreach ($products_response['body'] as $product) {
                $activities[] = [
                    'type' => 'product_added',
                    'title' => 'Yeni ürün eklendi',
                    'description' => $product['name'],
                    'created_at' => $product['created_at'],
                    'icon' => 'fa-plus',
                    'color' => 'green'
                ];
            }
        }
        
        // Son eklenen bloglar
        $blogs_response = supabase()->request("blogs?select=title,created_at&order=created_at.desc&limit=3");
        if ($blogs_response['body']) {
            foreach ($blogs_response['body'] as $blog) {
                $activities[] = [
                    'type' => 'blog_added',
                    'title' => 'Blog yazısı yayınlandı',
                    'description' => $blog['title'],
                    'created_at' => $blog['created_at'],
                    'icon' => 'fa-edit',
                    'color' => 'cyan'
                ];
            }
        }
        
        // Son mesajlar
        $messages_response = supabase()->request("contacts?select=name,subject,created_at&order=created_at.desc&limit=3");
        if ($messages_response['body']) {
            foreach ($messages_response['body'] as $message) {
                $activities[] = [
                    'type' => 'message_received',
                    'title' => 'Yeni mesaj alındı',
                    'description' => $message['subject'] ?: ($message['name'] . ' tarafından'),
                    'created_at' => $message['created_at'],
                    'icon' => 'fa-envelope',
                    'color' => 'yellow'
                ];
            }
        }
        
        // Tarihe göre sırala
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, $limit);
        
    } catch (Exception $e) {
        error_log("Recent activities error: " . $e->getMessage());
        return [];
    }
}

/**
 * Aylık istatistikleri getir (grafik için)
 */
function get_monthly_chart_data() {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $chart_data = [
            'products' => [],
            'blogs' => [],
            'messages' => []
        ];
        
        // Son 12 ay için veriler
        for ($i = 11; $i >= 0; $i--) {
            $month_start = date('Y-m-01 00:00:00', strtotime("-$i months"));
            $month_end = date('Y-m-t 23:59:59', strtotime("-$i months"));
            
            // Ürünler
            $products_response = supabase()->request("products?select=id&created_at=gte.$month_start&created_at=lte.$month_end");
            $chart_data['products'][] = count($products_response['body'] ?? []);
            
            // Bloglar
            $blogs_response = supabase()->request("blogs?select=id&created_at=gte.$month_start&created_at=lte.$month_end");
            $chart_data['blogs'][] = count($blogs_response['body'] ?? []);
            
            // Mesajlar
            $messages_response = supabase()->request("contacts?select=id&created_at=gte.$month_start&created_at=lte.$month_end");
            $chart_data['messages'][] = count($messages_response['body'] ?? []);
        }
        
        return $chart_data;
        
    } catch (Exception $e) {
        error_log("Monthly chart data error: " . $e->getMessage());
        
        // Fallback data
        return [
            'products' => [12, 15, 18, 22, 20, 25, 30, 28, 35, 40, 38, 45],
            'blogs' => [2, 3, 4, 3, 5, 4, 6, 5, 7, 8, 6, 9],
            'messages' => [8, 12, 15, 18, 22, 25, 20, 28, 30, 35, 32, 40]
        ];
    }
}

/**
 * Logout işlemi
 */
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    admin_logout();
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
