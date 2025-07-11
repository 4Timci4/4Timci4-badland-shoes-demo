<!-- Sağ Taraf - Ürün Bilgileri -->
<div class="lg:col-span-2 space-y-5">
    <div>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-secondary mb-2"><?php echo $product['name']; ?></h1>
            <!-- Favori butonu -->
            <?php if ($is_logged_in): ?>
                <button id="favorite-btn" class="flex items-center justify-center h-10 w-10 rounded-full hover:bg-gray-100 transition-colors duration-200" title="Favorilere ekle/çıkar">
                    <i class="far fa-heart text-xl text-gray-600" id="favorite-icon"></i>
                </button>
            <?php else: ?>
                <a href="/login.php" class="flex items-center justify-center h-10 w-10 rounded-full hover:bg-gray-100 transition-colors duration-200" title="Favorilere eklemek için giriş yapın">
                    <i class="far fa-heart text-xl text-gray-400"></i>
                </a>
            <?php endif; ?>
        </div>
        <p class="text-gray-600"><?php echo htmlspecialchars($product['categories'][0]['name'] ?? 'Kategori Yok'); ?></p>
    </div>
    
    <!-- Renk Seçimi -->
    <?php include 'views/product/product-colors.php'; ?>
    
    <!-- Beden Seçimi -->
    <?php include 'views/product/product-sizes.php'; ?>
    
    <!-- Aksiyon Butonları -->
    <?php include 'views/product/product-actions.php'; ?>
</div>
