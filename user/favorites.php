<?php
/**
 * User Favorites Page
 *
 * Session tabanlı kimlik doğrulama ile korumalı kullanıcı favorileri sayfası
 */

// Start output buffering to prevent session errors
ob_start();

require_once '../services/AuthService.php';
$authService = new AuthService();

// Session güvenlik kontrollerini yap ve giriş kontrolü
$authService->checkSessionSecurity();
if (!$authService->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

// Kullanıcı bilgilerini al
$currentUser = $authService->getCurrentUser();
$user = $currentUser; // FavoriteService için backward compatibility

// Favori servisi ve ürün sorgulama servisini yükle
require_once '../services/Product/FavoriteService.php';

// Favori servisini başlat
try {
    $favorite_service = new FavoriteService();
    // Kullanıcının favorilerini getir
    $favorites_data = $favorite_service->getFavorites($user['id']);
    $favorites = $favorites_data['favorites'] ?? [];
    $total = $favorites_data['total'] ?? 0;
} catch (Exception $e) {
    error_log("Favorites.php hatası: " . $e->getMessage());
    $favorites = [];
    $total = 0;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorilerim - Bandland Shoes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-3">
            <?php
            // Aktif sayfayı belirle
            $active_page = 'favorites';
            // Ortak sidebar'ı dahil et
            include '../includes/sidebar.php';
            ?>

            <!-- Main content -->
            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-9">
                <!-- Profile Header -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-20 w-20 rounded-full bg-primary flex items-center justify-center">
                                    <i class="fas fa-user text-white text-2xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 flex-1">
                                <h1 class="text-2xl font-bold text-gray-900">
                                    <?php echo htmlspecialchars($currentUser['full_name'] ?: 'Kullanıcı'); ?>
                                </h1>
                                <p class="text-sm font-medium text-gray-500">
                                    <?php echo htmlspecialchars($currentUser['email']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Favorites Content -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl leading-6 font-semibold text-gray-900">Favorilerim</h3>
                            <span id="favorites-total" class="px-3 py-1 bg-gray-100 rounded-full text-sm font-medium text-gray-700"><?php echo $total; ?> ürün</span>
                        </div>

                        <?php if (empty($favorites)): ?>
                            <div class="text-center py-12 bg-gray-50 rounded-lg">
                                <i class="far fa-heart text-5xl text-gray-300 mb-4"></i>
                                <p class="text-gray-600 text-lg mb-2">Henüz favori ürününüz bulunmamaktadır.</p>
                                <p class="text-gray-500 mb-6">Beğendiğiniz ürünleri favorilerinize ekleyerek daha sonra kolayca ulaşabilirsiniz.</p>
                                <a href="/products.php" class="mt-4 inline-block px-6 py-3 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                                    <i class="fas fa-search mr-2 text-sm"></i>Ürünleri Keşfet
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 gap-6">
                                <?php foreach ($favorites as $variant): ?>
                                    <?php
                                    $product = $variant['product'] ?? [];
                                    if (empty($product)) continue;
                                    
                                    // Varyant renk ve beden bilgilerini al
                                    $color_id = $variant['color_id'] ?? null;
                                    $size_id = $variant['size_id'] ?? null;
                                    
                                    // Ürün URL'sini oluştur
                                    $product_url = '/product-details.php?id=' . $product['id'];
                                    if ($color_id) {
                                        $product_url .= '&color=' . $color_id;
                                    }
                                    
                                    // Görsel URL'sini al (önce varyanta özgü görsel, yoksa ürünün ana görseli)
                                    $image_url = $product['variant_image_url'] ?? $product['image_url'] ?? '/assets/images/placeholder.svg';

                                    // Kategori bilgisi
                                    $category = $product['category_name'] ?? 'Ayakkabı';
                                    
                                    // Ekleme tarihi (favori tablosundan)
                                    $added_date = isset($variant['created_at']) ? date('d.m.Y', strtotime($variant['created_at'])) : '';

                                    // Stok durumu
                                    $stock = $variant['stock_quantity'] ?? 0;
                                    $stock_status = $stock > 0 ? 'Stokta' : 'Tükendi';
                                    $stock_class = $stock > 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100';
                                    ?>
                                    <div class="bg-white border rounded-lg overflow-hidden shadow hover:shadow-lg transition-shadow">
                                        <div class="flex">
                                            <!-- Ürün Görseli -->
                                            <div class="relative w-1/3">
                                                <a href="<?php echo $product_url; ?>" class="block">
                                                    <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['name'] ?? 'Ürün'); ?>"
                                                         class="w-full h-48 object-cover">
                                                </a>
                                                <button
                                                    class="remove-favorite absolute top-2 right-2 text-red-500 hover:text-red-600 focus:outline-none bg-white rounded-full p-1.5 shadow-sm"
                                                    data-variant-id="<?php echo $variant['id']; ?>"
                                                    title="Favorilerden kaldır">
                                                    <i class="fas fa-heart text-sm"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Ürün Bilgileri -->
                                            <div class="p-4 w-2/3 flex flex-col">
                                                <div class="mb-2">
                                                    <h4 class="font-medium text-gray-900">
                                                        <a href="<?php echo $product_url; ?>" class="hover:text-primary">
                                                            <?php echo htmlspecialchars($product['name'] ?? 'Ürün Adı'); ?>
                                                        </a>
                                                    </h4>
                                                    
                                                    <!-- Ürün özellikleri - Modern tasarım -->
                                                    <div class="flex items-center space-x-3 mt-3 border-t border-gray-100 pt-3">
                                                        <!-- Renk bilgisi -->
                                                        <div class="flex items-center">
                                                            <span class="w-4 h-4 rounded-full mr-1.5 border shadow-sm"
                                                                  style="background-color: <?php echo $variant['color_hex'] ?? '#ccc'; ?>"></span>
                                                            <span class="text-xs text-gray-600"><?php echo $variant['color_name'] ?? 'Renk bilgisi yok'; ?></span>
                                                        </div>
                                                        
                                                        <!-- Ayırıcı -->
                                                        <span class="text-gray-300">|</span>
                                                        
                                                        <!-- Beden bilgisi -->
                                                        <div class="flex items-center">
                                                            <span class="text-xs text-gray-600">Beden: <span class="font-medium"><?php echo $variant['size_value'] ?? '-'; ?></span></span>
                                                        </div>
                                                        
                                                        <!-- Ayırıcı -->
                                                        <span class="text-gray-300">|</span>
                                                        
                                                        <!-- Stok durumu -->
                                                        <div class="flex items-center">
                                                            <span class="w-2 h-2 rounded-full mr-1.5 <?php echo $stock > 0 ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                                            <span class="text-xs <?php echo $stock > 0 ? 'text-green-600' : 'text-red-600'; ?> font-medium">
                                                                <?php echo $stock_status; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-auto">
                                                    <a href="<?php echo $product_url; ?>" class="inline-block px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-primary hover:bg-primary-dark transition-colors">
                                                        Ürüne Git
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const removeFavoriteButtons = document.querySelectorAll('.remove-favorite');

        removeFavoriteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const variantId = this.dataset.variantId;
                if (!variantId) return;

                if (confirm('Bu ürünü favorilerinizden kaldırmak istediğinizden emin misiniz?')) {
                    const formData = new FormData();
                    formData.append('variant_id', variantId);
                    formData.append('action', 'remove');

                    fetch('/api/favorites.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const productCard = this.closest('.transition-shadow');
                            if (productCard) {
                                productCard.remove();
                            }
                            
                            const totalElement = document.getElementById('favorites-total');
                            if (totalElement) {
                                const currentTotal = parseInt(totalElement.textContent) - 1;
                                totalElement.textContent = currentTotal + ' ürün';

                                if (currentTotal === 0) {
                                    location.reload();
                                }
                            }
                        } else {
                            alert(data.message || 'Bir hata oluştu.');
                        }
                    })
                    .catch(error => {
                        console.error('Hata:', error);
                        alert('Bir hata oluştu.');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
<?php
// End output buffering and flush buffer
ob_end_flush();
?>