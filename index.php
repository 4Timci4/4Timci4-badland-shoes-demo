<?php
// Simple PHP Router for clean URLs
// Works with both Apache and PHP built-in server

// Get the request URI and clean it
$request_uri = $_SERVER['REQUEST_URI'];
$request_path = parse_url($request_uri, PHP_URL_PATH);
$request_path = rtrim($request_path, '/'); // Remove trailing slash

// If empty path, show homepage
if ($request_path === '' || $request_path === '/') {
    include 'index-content.php';
    exit;
}

// Define routes mapping
$routes = [
    '/about' => 'about.php',
    '/blog' => 'blog.php',
    '/blog-detail' => 'blog-detail.php',
    '/contact' => 'contact.php',
    '/forgot-password' => 'forgot-password.php',
    '/login' => 'login.php',
    '/logout' => 'logout.php',
    '/maintenance' => 'maintenance.php',
    '/product-details' => 'product-details.php',
    '/products' => 'products.php',
    '/register' => 'register.php',
    '/reset-password' => 'reset-password.php',
    '/user/profile' => 'user/profile.php',
    '/user/favorites' => 'user/favorites.php'
];

// Check if the route exists
if (array_key_exists($request_path, $routes)) {
    $file_path = $routes[$request_path];
    
    // Check if file exists
    if (file_exists($file_path)) {
        include $file_path;
        exit;
    }
}

// If no route matches, check for static files or admin panel
// Admin panel should work with .php extensions
if (strpos($request_path, '/admin/') === 0) {
    // Let Apache handle admin panel normally
    // For PHP built-in server, we need to let it handle PHP files
    $admin_file = ltrim($request_path, '/');
    if (file_exists($admin_file)) {
        include $admin_file;
        exit;
    }
}

// Check for assets (CSS, JS, images)
if (strpos($request_path, '/assets/') === 0) {
    // Let the server handle static files normally
    return false; // For PHP built-in server
}

// Check for API endpoints
if (strpos($request_path, '/api/') === 0) {
    $api_file = ltrim($request_path, '/');
    if (file_exists($api_file)) {
        include $api_file;
        exit;
    }
}

// If nothing matches, show 404 page or redirect to homepage
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sayfa Bulunamadı - Bandland Shoes</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-5 text-center">
            <div class="mb-8">
                <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
                <h1 class="text-4xl font-bold text-secondary mb-4">404 - Sayfa Bulunamadı</h1>
                <p class="text-gray-600 text-lg mb-8">
                    Aradığınız sayfa mevcut değil veya taşınmış olabilir.
                </p>
                <div class="space-x-4">
                    <a href="/" class="inline-block px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                        Ana Sayfa
                    </a>
                    <a href="/products" class="inline-block px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                        Ürünler
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>