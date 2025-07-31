<?php



require_once 'config/database.php';
require_once 'services/Product/ProductApiService.php';
require_once 'services/AuthService.php';
require_once 'services/Product/FavoriteService.php';



$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$product_id) {
    header("Location: /products");
    exit;
}

$db = database();
$product_api_service = product_api_service();
$authService = new AuthService();
$favoriteService = new FavoriteService();


$is_logged_in = $authService->isLoggedIn();
$current_user = $is_logged_in ? $authService->getCurrentUser() : null;
$favorite_variant_ids = [];
if ($is_logged_in) {
    $favorite_variant_ids = $favoriteService->getFavoriteVariantIds($current_user['id']);
}

// Database bağlantısı kontrolü
if (!$db) {
    // Demo products listesi
    $demo_products = [
        1 => [
            'id' => 1,
            'name' => 'Nike Air Max 270',
            'slug' => 'nike-air-max-270',
            'description' => 'Rahat ve stilin bir arada. Nike Air Max 270 ile her adımda konfor yaşayın. Premium malzemeler ve yenilikçi tasarım ile ayak sağlığınızı ön planda tutarak size en iyi deneyimi sunuyoruz.',
            'price' => 899.99,
            'features' => "Hafif ve esnek yapı\nNefes alabilir kumaş\nYüksek kaliteli taban\nÇeşitli renk seçenekleri\nUzun ömürlü malzeme",
            'is_featured' => true,
            'created_at' => '2024-01-15 10:00:00',
            'categories' => [['id' => 1, 'name' => 'Sneaker', 'slug' => 'sneaker']],
            'genders' => [['id' => 1, 'name' => 'Erkek', 'slug' => 'erkek'], ['id' => 2, 'name' => 'Kadın', 'slug' => 'kadin']],
            'variants' => [
                ['id' => 1, 'color_id' => 1, 'size_id' => 5, 'price' => 899.99, 'stock_quantity' => 10],
                ['id' => 2, 'color_id' => 1, 'size_id' => 6, 'price' => 899.99, 'stock_quantity' => 8],
                ['id' => 3, 'color_id' => 2, 'size_id' => 5, 'price' => 899.99, 'stock_quantity' => 5]
            ],
            'images' => [
                ['id' => 1, 'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop&crop=center', 'color_id' => 1, 'is_primary' => true, 'sort_order' => 1],
                ['id' => 2, 'image_url' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop&crop=center', 'color_id' => 2, 'is_primary' => true, 'sort_order' => 1]
            ]
        ],
        2 => [
            'id' => 2,
            'name' => 'Adidas Ultraboost 22',
            'slug' => 'adidas-ultraboost-22',
            'description' => 'Yenilikçi teknoloji ile maksimum performans. Adidas Ultraboost 22 ile koşu deneyiminizi üst seviyeye taşıyın. Responsive Boost teknolojisi ve esnek üst yapı ile her adımda enerji iadesi.',
            'price' => 1299.99,
            'features' => "Responsive Boost teknolojisi\nEsnek Primeknit üst yapı\nContinental kauçuk taban\nEnerjili geri dönüş\nUzun mesafe konforu",
            'is_featured' => true,
            'created_at' => '2024-01-14 09:30:00',
            'categories' => [['id' => 2, 'name' => 'Koşu Ayakkabısı', 'slug' => 'kosu-ayakkabisi']],
            'genders' => [['id' => 1, 'name' => 'Erkek', 'slug' => 'erkek']],
            'variants' => [
                ['id' => 4, 'color_id' => 3, 'size_id' => 6, 'price' => 1299.99, 'stock_quantity' => 12],
                ['id' => 5, 'color_id' => 3, 'size_id' => 7, 'price' => 1299.99, 'stock_quantity' => 7],
                ['id' => 6, 'color_id' => 4, 'size_id' => 6, 'price' => 1299.99, 'stock_quantity' => 9]
            ],
            'images' => [
                ['id' => 3, 'image_url' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop&crop=center', 'color_id' => 3, 'is_primary' => true, 'sort_order' => 1],
                ['id' => 4, 'image_url' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop&crop=center', 'color_id' => 4, 'is_primary' => true, 'sort_order' => 1]
            ]
        ],
        3 => [
            'id' => 3,
            'name' => 'Converse Chuck Taylor All Star',
            'slug' => 'converse-chuck-taylor-all-star',
            'description' => 'Klasik stil hiç eskimez. Converse Chuck Taylor All Star ile zamansız tarzınızı tamamlayın. İkonik tasarım ve rahat kalıp ile günlük kullanım için mükemmel.',
            'price' => 449.99,
            'features' => "İkonik klasik tasarım\nDayanıklı kanvas malzeme\nYumuşak iç astar\nEsnek kauçuk taban\nÇok yönlü stil",
            'is_featured' => false,
            'created_at' => '2024-01-13 14:15:00',
            'categories' => [['id' => 1, 'name' => 'Sneaker', 'slug' => 'sneaker']],
            'genders' => [['id' => 3, 'name' => 'Unisex', 'slug' => 'unisex']],
            'variants' => [
                ['id' => 7, 'color_id' => 1, 'size_id' => 4, 'price' => 449.99, 'stock_quantity' => 15],
                ['id' => 8, 'color_id' => 1, 'size_id' => 5, 'price' => 449.99, 'stock_quantity' => 12],
                ['id' => 9, 'color_id' => 2, 'size_id' => 4, 'price' => 449.99, 'stock_quantity' => 8]
            ],
            'images' => [
                ['id' => 5, 'image_url' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=400&h=400&fit=crop&crop=center', 'color_id' => 1, 'is_primary' => true, 'sort_order' => 1],
                ['id' => 6, 'image_url' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop&crop=center', 'color_id' => 2, 'is_primary' => true, 'sort_order' => 1]
            ]
        ]
    ];
    
    // Belirtilen product_id için demo ürün varsa onu kullan, yoksa varsayılan olarak id=1'i kullan
    $product = $demo_products[$product_id] ?? $demo_products[1];
} else {
    $product_data = $db->select('product_details_view', ['id' => ['eq', $product_id]]);
    if (empty($product_data)) {
        header("Location: /products");
        exit;
    }

    $product = $product_data[0];

    // JSON verilerini decode et
    $product['categories'] = is_string($product['categories']) ? json_decode($product['categories'], true) : ($product['categories'] ?? []);
    $product['genders'] = is_string($product['genders']) ? json_decode($product['genders'], true) : ($product['genders'] ?? []);
    $product['variants'] = is_string($product['variants']) ? json_decode($product['variants'], true) : ($product['variants'] ?? []);
    $product['images'] = is_string($product['images']) ? json_decode($product['images'], true) : ($product['images'] ?? []);
}





$selected_color_slug = isset($_GET['color']) ? trim($_GET['color']) : '';
$all_colors = [];
$variants_by_color_id = [];
if (!empty($product['variants'])) {
    foreach ($product['variants'] as $variant) {
        $variants_by_color_id[$variant['color_id']][] = $variant;
    }
    $color_ids = array_keys($variants_by_color_id);
    if (!empty($color_ids)) {
        if ($db) {
            $all_colors = $db->select('colors', ['id' => ['in', $color_ids]]);
        } else {
            // Demo colors - tüm mevcut renkler
            $demo_colors = [
                ['id' => 1, 'name' => 'Siyah', 'hex_code' => '#000000'],
                ['id' => 2, 'name' => 'Beyaz', 'hex_code' => '#FFFFFF'],
                ['id' => 3, 'name' => 'Mavi', 'hex_code' => '#0066CC'],
                ['id' => 4, 'name' => 'Kırmızı', 'hex_code' => '#CC0000']
            ];
            // Sadece bu ürünün kullandığı renkleri filtrele
            $all_colors = array_filter($demo_colors, function($color) use ($color_ids) {
                return in_array($color['id'], $color_ids);
            });
            // Array'i yeniden indexle
            $all_colors = array_values($all_colors);
        }
    }
}

function createColorSlug($colorName)
{
    $slug = strtolower($colorName ?? '');
    $slug = str_replace(['ı', 'İ', 'ş', 'Ş', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'], ['i', 'i', 's', 's', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'], $slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    return preg_replace('/-+/', '-', trim($slug, '-'));
}

$selected_color_id = null;
if (!empty($selected_color_slug)) {
    foreach ($all_colors as $color) {
        if (createColorSlug($color['name']) === $selected_color_slug) {
            $selected_color_id = $color['id'];
            break;
        }
    }
}

if (!$selected_color_id && !empty($all_colors) && isset($all_colors[0])) {
    $selected_color_id = $all_colors[0]['id'];
}


$current_images = [];
if (!empty($product['images'])) {
    foreach ($product['images'] as $image) {
        if ($image['color_id'] === $selected_color_id) {
            $current_images[] = $image;
        }
    }
    if (empty($current_images)) {
        foreach ($product['images'] as $image) {
            if ($image['is_primary']) {
                $current_images[] = $image;
            }
        }
    }
    if (empty($current_images) && isset($product['images'][0])) {
        $current_images[] = $product['images'][0];
    }
}


$available_sizes = [];
if (!empty($product['variants'])) {

    $all_size_ids = array_unique(array_column($product['variants'], 'size_id'));

    if (!empty($all_size_ids)) {
        if ($db) {
            $available_sizes = $db->select('sizes', ['id' => ['in', $all_size_ids]]);
            usort($available_sizes, fn($a, $b) => strnatcmp($a['size_value'], $b['size_value']));
        } else {
            // Demo sizes - tüm mevcut bedenler
            $demo_sizes = [
                ['id' => 1, 'size_value' => '36', 'size_type' => 'EU'],
                ['id' => 2, 'size_value' => '37', 'size_type' => 'EU'],
                ['id' => 3, 'size_value' => '38', 'size_type' => 'EU'],
                ['id' => 4, 'size_value' => '39', 'size_type' => 'EU'],
                ['id' => 5, 'size_value' => '40', 'size_type' => 'EU'],
                ['id' => 6, 'size_value' => '41', 'size_type' => 'EU'],
                ['id' => 7, 'size_value' => '42', 'size_type' => 'EU'],
                ['id' => 8, 'size_value' => '43', 'size_type' => 'EU'],
                ['id' => 9, 'size_value' => '44', 'size_type' => 'EU'],
                ['id' => 10, 'size_value' => '45', 'size_type' => 'EU']
            ];
            // Sadece bu ürünün kullandığı bedenleri filtrele
            $available_sizes = array_filter($demo_sizes, function($size) use ($all_size_ids) {
                return in_array($size['id'], $all_size_ids);
            });
            usort($available_sizes, fn($a, $b) => strnatcmp($a['size_value'], $b['size_value']));
        }
    }
}


$features = !empty($product['features']) ? explode("\n", $product['features']) : [];


$similar_products = $product_api_service->getSimilarProducts($product['id'], 5);

?>