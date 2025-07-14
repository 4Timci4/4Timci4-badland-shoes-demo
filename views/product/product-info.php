<div class="lg:col-span-2 space-y-5">
    <div>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-secondary mb-2"><?php echo $product['name']; ?></h1>
            <button id="favorite-btn"
                class="flex items-center justify-center h-10 w-10 rounded-full hover:bg-gray-100 transition-colors duration-200"
                title="Favorilere ekle/çıkar">
                <i class="far fa-heart text-xl text-gray-600" id="favorite-icon"></i>
            </button>
        </div>
        <p class="text-gray-600"><?php echo htmlspecialchars($product['categories'][0]['name'] ?? 'Kategori Yok'); ?>
        </p>
    </div>

    <?php include 'views/product/product-colors.php'; ?>

    <?php include 'views/product/product-sizes.php'; ?>

    <?php include 'views/product/product-actions.php'; ?>
</div>