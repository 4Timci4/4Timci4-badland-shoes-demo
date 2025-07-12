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
                    'title' => 'Favori Raporları',
                    'url' => 'favorited-products.php',
                    'active' => is_active_page('favorited-products.php')
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
 * İstatistik verileri (dashboard için) - Database Abstraction Layer ile
 */
function get_dashboard_stats() {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $db = database();
        
        // Bu ayın başlangıç tarihi
        $current_month_start = date('Y-m-01 00:00:00');
        
        // Toplam ürün sayısı
        $total_products = $db->count('product_models');
        
        // Bu ay eklenen ürünler
        $monthly_products = $db->count('product_models', ['created_at' => ['>=', $current_month_start]]);
        
        // Toplam kategori sayısı
        $total_categories = $db->count('categories');
        
        // Toplam blog sayısı
        $total_blogs = $db->count('blogs');
        
        // Bu ay eklenen bloglar
        $monthly_blogs = $db->count('blogs', ['created_at' => ['>=', $current_month_start]]);
        
        // Mesajlar (şimdilik sabit değerler)
        $total_messages = $db->count('contact_messages');
        $pending_messages = 0;
        
        // Öne çıkan ürünler
        $featured_products = $db->count('product_models', ['is_featured' => true]);
        
        // Favoriye alınan varyantların sayısı
        $total_favorited_variants = $db->count('favorites');
        
        // Bu ay favoriye eklenen varyantlar
        $monthly_favorited_variants = $db->count('favorites', ['created_at' => ['>=', $current_month_start]]);
        
        return [
            'total_products' => $total_products,
            'total_categories' => $total_categories,
            'total_blogs' => $total_blogs,
            'total_messages' => $total_messages,
            'monthly_products' => $monthly_products,
            'monthly_blogs' => $monthly_blogs,
            'pending_messages' => $pending_messages,
            'featured_products' => $featured_products,
            'total_favorited_variants' => $total_favorited_variants,
            'monthly_favorited_variants' => $monthly_favorited_variants
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
        $db = database();
        $products = $db->select('product_models', [], '*', ['order' => 'created_at DESC', 'limit' => $limit]);
        return $products;
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
        $db = database();
        $blogs = $db->select('blogs', [], ['id', 'title', 'excerpt', 'category', 'created_at'], ['order' => 'created_at DESC', 'limit' => $limit]);
        return $blogs;
    } catch (Exception $e) {
        error_log("Recent blogs error: " . $e->getMessage());
        return [];
    }
}

/**
 * Favori raporları - En çok favoriye alınan ürün varyantlarını getir
 */
function get_favorited_variants_summary($limit = 50) {
    require_once __DIR__ . '/../../config/database.php';

    try {
        $db = database();

        // favorites_view'den tüm verileri çek
        $favorited_items = $db->select('favorites_view', [], '*', ['limit' => 1000]);

        if (empty($favorited_items)) {
            return [];
        }

        // Sonuçları ürün bazında grupla
        $results = [];
        foreach ($favorited_items as $item) {
            $productId = $item['product_id'];

            // Ürün daha önce eklenmemişse, ana yapıyı oluştur
            if (!isset($results[$productId])) {
                $results[$productId] = [
                    'product_id' => $productId,
                    'product_name' => $item['product_name'],
                    'total_favorites' => 0,
                    'variants' => []
                ];
            }

            // Varyantı ve favori sayısını ekle
            // Bu view her favori kaydı için bir satır döndürdüğünden, varyantları saymamız gerekiyor
            $variantId = $item['variant_id'];
            if (!isset($results[$productId]['variants'][$variantId])) {
                $results[$productId]['variants'][$variantId] = [
                    'variant_id' => $variantId,
                    'color_name' => $item['color_name'],
                    'size_name' => $item['size_value'],
                    'color_hex' => $item['color_hex'],
                    'favorite_count' => 0
                ];
            }
            
            $results[$productId]['variants'][$variantId]['favorite_count']++;
            $results[$productId]['total_favorites']++;
        }

        // Toplam favori sayısına göre azalan sırada sırala
        uasort($results, function($a, $b) {
            return $b['total_favorites'] - $a['total_favorites'];
        });

        // Her ürün için varyantları favori sayısına göre sırala ve indeksi sıfırla
        foreach ($results as &$product) {
            usort($product['variants'], function($a, $b) {
                return $b['favorite_count'] - $a['favorite_count'];
            });
        }

        // İlk $limit ürünü döndür
        return array_slice(array_values($results), 0, $limit);

    } catch (Exception $e) {
        error_log("Favorited variants summary error: " . $e->getMessage());
        return [];
    }
}

/**
 * Favori trendleri - Son 30 günün favori ekleme istatistikleri
 */
function get_favorite_trends($days = 30) {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $db = database();
        
        // Son X gün için tarih aralığı
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-$days days"));
        
        // Günlük favori sayılarını getir
        $favorites = $db->select('favorites', [
            'created_at' => ['>=', $start_date . ' 00:00:00']
        ], 'DATE(created_at) as date, COUNT(*) as count', [
            'group' => 'DATE(created_at)',
            'order' => 'date ASC'
        ]);
        
        // Tüm günler için veri oluştur (eksik günler için 0 değeri)
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $found = false;
            
            foreach ($favorites as $fav) {
                if ($fav['date'] === $date) {
                    $data[] = [
                        'date' => $date,
                        'formatted_date' => date('d.m', strtotime($date)),
                        'count' => (int)$fav['count']
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $data[] = [
                    'date' => $date,
                    'formatted_date' => date('d.m', strtotime($date)),
                    'count' => 0
                ];
            }
        }
        
        return $data;
        
    } catch (Exception $e) {
        error_log("Favorite trends error: " . $e->getMessage());
        return [];
    }
}

/**
 * Renk dağılımı - En popüler renkler
 */
function get_color_distribution() {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $db = database();
        
        // Renklere göre favori sayıları
        $colors = $db->select('favorites_view', [],
            'color_name, color_hex, COUNT(*) as favorite_count', [
            'group' => 'color_name, color_hex',
            'order' => 'favorite_count DESC',
            'limit' => 10
        ]);
        
        return $colors;
        
    } catch (Exception $e) {
        error_log("Color distribution error: " . $e->getMessage());
        return [];
    }
}

/**
 * Beden dağılımı - En popüler bedenler
 */
function get_size_distribution() {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $db = database();
        
        // Bedenlere göre favori sayıları
        $sizes = $db->select('favorites_view', [],
            'size_value, COUNT(*) as favorite_count', [
            'group' => 'size_value',
            'order' => 'favorite_count DESC'
        ]);
        
        return $sizes;
        
    } catch (Exception $e) {
        error_log("Size distribution error: " . $e->getMessage());
        return [];
    }
}

/**
 * Gelişmiş favori istatistikleri
 */
function get_advanced_favorite_stats() {
    require_once __DIR__ . '/../../config/database.php';
    
    try {
        $db = database();
        
        // Toplam veriler
        $total_favorites = $db->count('favorites');
        $total_users_with_favorites_result = $db->executeRawSql("SELECT COUNT(DISTINCT user_id) as total FROM favorites");
        $total_users_with_favorites = $total_users_with_favorites_result[0]['total'] ?? 0;
        
        $total_favorited_products_result = $db->executeRawSql("SELECT COUNT(DISTINCT product_id) as total FROM favorites_view");
        $total_favorited_products = $total_favorited_products_result[0]['total'] ?? 0;
        
        $total_favorited_variants_result = $db->executeRawSql("SELECT COUNT(DISTINCT variant_id) as total FROM favorites");
        $total_favorited_variants = $total_favorited_variants_result[0]['total'] ?? 0;
        
        // Bu ay eklenen favoriler
        $current_month_start = date('Y-m-01 00:00:00');
        $monthly_favorites = $db->count('favorites', ['created_at' => ['>=', $current_month_start]]);
        
        // Bu hafta eklenen favoriler
        $current_week_start = date('Y-m-d', strtotime('monday this week')) . ' 00:00:00';
        $weekly_favorites = $db->count('favorites', ['created_at' => ['>=', $current_week_start]]);
        
        // Bugün eklenen favoriler
        $today_start = date('Y-m-d 00:00:00');
        $daily_favorites = $db->count('favorites', ['created_at' => ['>=', $today_start]]);
        
        // Ortalama kullanıcı başına favori
        $avg_favorites_per_user = $total_users_with_favorites > 0 ?
            round($total_favorites / $total_users_with_favorites, 1) : 0;
        
        // En popüler ürünü bul
        $top_product = $db->select('favorites_view', [],
            'product_name, COUNT(*) as favorite_count', [
            'group' => 'product_id, product_name',
            'order' => 'favorite_count DESC',
            'limit' => 1
        ]);
        
        return [
            'total_favorites' => $total_favorites,
            'total_users_with_favorites' => $total_users_with_favorites,
            'total_favorited_products' => $total_favorited_products,
            'total_favorited_variants' => $total_favorited_variants,
            'monthly_favorites' => $monthly_favorites,
            'weekly_favorites' => $weekly_favorites,
            'daily_favorites' => $daily_favorites,
            'avg_favorites_per_user' => $avg_favorites_per_user,
            'top_product' => !empty($top_product) ? $top_product[0] : null
        ];
        
    } catch (Exception $e) {
        error_log("Advanced favorite stats error: " . $e->getMessage());
        return [
            'total_favorites' => 0,
            'total_users_with_favorites' => 0,
            'total_favorited_products' => 0,
            'total_favorited_variants' => 0,
            'monthly_favorites' => 0,
            'weekly_favorites' => 0,
            'daily_favorites' => 0,
            'avg_favorites_per_user' => 0,
            'top_product' => null
        ];
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
