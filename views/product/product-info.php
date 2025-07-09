<!-- Sağ Taraf - Ürün Bilgileri -->
<div class="lg:col-span-2 space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-secondary mb-2"><?php echo $product['name']; ?></h1>
        <p class="text-gray-600"><?php echo htmlspecialchars($product['categories'][0]['name'] ?? 'Kategori Yok'); ?></p>
    </div>
    
    <!-- Fiyat Bilgisi -->
    <?php include 'views/product/product-price.php'; ?>
    
    <!-- Renk Seçimi -->
    <?php include 'views/product/product-colors.php'; ?>
    
    <!-- Beden Seçimi -->
    <?php include 'views/product/product-sizes.php'; ?>
    
    <!-- Aksiyon Butonları -->
    <?php include 'views/product/product-actions.php'; ?>
</div>
