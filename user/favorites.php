<?php

ob_start();

// Çerez tabanlı favori sistemi için gerekli servisleri yükle
require_once '../services/Product/ProductQueryService.php';
require_once '../lib/DatabaseFactory.php';

$favorites = [];
$total = 0;

// Çerezlerden favori ürün ID'lerini al
if (isset($_COOKIE['user_favorites'])) {
    try {
        $cookie_data = json_decode($_COOKIE['user_favorites'], true);
        if (is_array($cookie_data) && !empty($cookie_data)) {
            $db = database();
            $productQueryService = new ProductQueryService($db);

            // Her favori için ürün bilgilerini çek
            foreach ($cookie_data as $favorite_item) {
                if (isset($favorite_item['variantId'])) {
                    $variant_id = intval($favorite_item['variantId']);
                    $color_id = isset($favorite_item['colorId']) ? intval($favorite_item['colorId']) : null;

                    // Varyant bilgilerini al
                    $variant = $productQueryService->getVariantById($variant_id);
                    if (!empty($variant)) {
                        // Ürün bilgilerini al
                        $product = $productQueryService->getProductModel($variant['model_id']);
                        if (!empty($product)) {
                            // Varyant resmi varsa al
                            if ($color_id) {
                                $variantImages = $productQueryService->getVariantImages($variant['model_id'], $color_id);
                                if (!empty($variantImages)) {
                                    $product['variant_image_url'] = $variantImages[0]['image_url'];
                                }
                            }

                            // Favori ekleme tarihini ekle
                            $variant['created_at'] = isset($favorite_item['addedAt']) ? $favorite_item['addedAt'] : date('Y-m-d H:i:s');
                            $variant['product'] = $product;
                            $favorites[] = $variant;
                        }
                    }
                }
            }

            $total = count($favorites);
        }
    } catch (Exception $e) {
        error_log("Favorites.php çerez okuma hatası: " . $e->getMessage());
        $favorites = [];
        $total = 0;
    }
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
    <script src="../assets/js/cookie-manager.js"></script>
</head>

<body class="bg-gray-100">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-20 w-20 rounded-full bg-primary flex items-center justify-center">
                                <i class="fas fa-heart text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 flex-1">
                            <h1 class="text-2xl font-bold text-gray-900">Favorilerim</h1>
                            <p class="text-sm font-medium text-gray-500">
                                Beğendiğiniz ürünleri buradan takip edebilirsiniz
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl leading-6 font-semibold text-gray-900">Favorilerim</h3>
                        <span id="favorites-total"
                            class="px-3 py-1 bg-gray-100 rounded-full text-sm font-medium text-gray-700"><?php echo $total; ?>
                            ürün</span>
                    </div>

                    <?php if (empty($favorites)): ?>
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="far fa-heart text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600 text-lg mb-2">Henüz favori ürününüz bulunmamaktadır.</p>
                            <p class="text-gray-500 mb-6">Beğendiğiniz ürünleri favorilerinize ekleyerek daha sonra
                                kolayca ulaşabilirsiniz.</p>
                            <a href="/products.php"
                                class="mt-4 inline-block px-6 py-3 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                                <i class="fas fa-search mr-2 text-sm"></i>Ürünleri Keşfet
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($favorites as $variant): ?>
                                <?php
                                $product = $variant['product'] ?? [];
                                if (empty($product))
                                    continue;

                                $color_id = $variant['color_id'] ?? null;
                                $size_id = $variant['size_id'] ?? null;
                                $product_url = '/product-details.php?id=' . $product['id'];
                                if ($color_id) {
                                    $product_url .= '&color=' . $color_id;
                                }
                                $image_url = $product['variant_image_url'] ?? $product['image_url'] ?? '/assets/images/placeholder.svg';
                                $category = $product['category_name'] ?? 'Ayakkabı';
                                $added_date = isset($variant['created_at']) ? date('d.m.Y', strtotime($variant['created_at'])) : '';
                                $stock = $variant['stock_quantity'] ?? 0;
                                $stock_status = $stock > 0 ? 'Stokta' : 'Tükendi';
                                $stock_class = $stock > 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100';
                                ?>
                                <div
                                    class="bg-white border rounded-lg overflow-hidden shadow hover:shadow-lg transition-shadow">
                                    <div class="flex">
                                        <div class="relative w-1/3">
                                            <a href="<?php echo $product_url; ?>" class="block">
                                                <img src="<?php echo $image_url; ?>"
                                                    alt="<?php echo htmlspecialchars($product['name'] ?? 'Ürün'); ?>"
                                                    class="w-full h-48 object-cover">
                                            </a>
                                            <button
                                                class="remove-favorite absolute top-2 right-2 text-red-500 hover:text-red-600 focus:outline-none bg-white rounded-full p-1.5 shadow-sm"
                                                data-variant-id="<?php echo $variant['id']; ?>" title="Favorilerden kaldır">
                                                <i class="fas fa-heart text-sm"></i>
                                            </button>
                                        </div>

                                        <div class="p-4 w-2/3 flex flex-col">
                                            <div class="mb-2">
                                                <h4 class="font-medium text-gray-900">
                                                    <a href="<?php echo $product_url; ?>" class="hover:text-primary">
                                                        <?php echo htmlspecialchars($product['name'] ?? 'Ürün Adı'); ?>
                                                    </a>
                                                </h4>

                                                <div class="flex items-center space-x-3 mt-3 border-t border-gray-100 pt-3">
                                                    <div class="flex items-center">
                                                        <span class="w-4 h-4 rounded-full mr-1.5 border shadow-sm"
                                                            style="background-color: <?php echo $variant['color_hex'] ?? '#ccc'; ?>"></span>
                                                        <span
                                                            class="text-xs text-gray-600"><?php echo $variant['color_name'] ?? 'Renk bilgisi yok'; ?></span>
                                                    </div>

                                                    <span class="text-gray-300">|</span>

                                                    <div class="flex items-center">
                                                        <span class="text-xs text-gray-600">Beden: <span
                                                                class="font-medium"><?php echo $variant['size_value'] ?? '-'; ?></span></span>
                                                    </div>

                                                    <span class="text-gray-300">|</span>

                                                    <div class="flex items-center">
                                                        <span
                                                            class="w-2 h-2 rounded-full mr-1.5 <?php echo $stock > 0 ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                                        <span
                                                            class="text-xs <?php echo $stock > 0 ? 'text-green-600' : 'text-red-600'; ?> font-medium">
                                                            <?php echo $stock_status; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-auto">
                                                <a href="<?php echo $product_url; ?>"
                                                    class="inline-block px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-primary hover:bg-primary-dark transition-colors">
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
        document.addEventListener('DOMContentLoaded', function () {
            // FavoritesManager'ın yüklendiğinden emin ol
            if (typeof window.FavoritesManager === 'undefined') {
                console.error('FavoritesManager yüklenemedi');
                return;
            }

            const removeFavoriteButtons = document.querySelectorAll('.remove-favorite');

            removeFavoriteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const variantId = parseInt(this.dataset.variantId);
                    if (!variantId) return;

                    if (confirm('Bu ürünü favorilerinizden kaldırmak istediğinizden emin misiniz?')) {
                        // Çerez tabanlı favori kaldırma işlemi
                        const result = window.FavoritesManager.removeFavorite(variantId);

                        if (result) {
                            // Ürün kartını DOM'dan kaldır
                            const productCard = this.closest('.transition-shadow');
                            if (productCard) {
                                productCard.remove();
                            }

                            // Toplam sayıyı güncelle
                            const totalElement = document.getElementById('favorites-total');
                            if (totalElement) {
                                const currentTotal = window.FavoritesManager.getFavoriteCount();
                                totalElement.textContent = currentTotal + ' ürün';

                                // Eğer hiç favori kalmadıysa sayfayı yenile
                                if (currentTotal === 0) {
                                    location.reload();
                                }
                            }

                            // Başarı mesajı göster
                            console.log('Ürün favorilerden kaldırıldı');
                        } else {
                            alert('Ürün favorilerden kaldırılırken bir hata oluştu.');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
<?php
ob_end_flush();
?>