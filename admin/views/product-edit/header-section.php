<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
            <?= isset($product) && !empty($product) ? 'Ürün Düzenle' : 'Yeni Ürün Ekle' ?>
        </h1>
        <p class="text-gray-600">
            <?php if (isset($product) && !empty($product)): ?>
                <span class="font-semibold"><?= htmlspecialchars($product['name']) ?></span> ürününün bilgilerini
                güncelleyin
            <?php else: ?>
                Yeni bir ürün oluşturmak için sihirbazı takip edin.
            <?php endif; ?>
        </p>
    </div>
    <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
        <a href="products.php"
            class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors text-sm sm:text-base">
            <i class="fas fa-arrow-left mr-2"></i>
            Ürün Listesi
        </a>
        <?php if (isset($product) && !empty($product)): ?>
            <a href="../product-details.php?id=<?= $product_id ?>" target="_blank"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-100 text-blue-700 font-medium rounded-lg hover:bg-blue-200 transition-colors text-sm sm:text-base">
                <i class="fas fa-external-link-alt mr-2"></i>
                Önizle
            </a>
        <?php endif; ?>
    </div>
</div>