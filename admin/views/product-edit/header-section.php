<!-- Header Section -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Ürün Düzenle
        </h1>
        <p class="text-gray-600">
            <span class="font-semibold"><?= htmlspecialchars($product['name']) ?></span> ürününün bilgilerini güncelleyin
        </p>
    </div>
    <div class="mt-4 lg:mt-0 flex space-x-3">
        <a href="products.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Ürün Listesi
        </a>
        <a href="../product-details.php?id=<?= $product_id ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 font-medium rounded-lg hover:bg-blue-200 transition-colors">
            <i class="fas fa-external-link-alt mr-2"></i>
            Önizle
        </a>
    </div>
</div>