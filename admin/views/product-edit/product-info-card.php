<!-- Product Info Card -->
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
    <div class="flex items-center space-x-4">
        <div class="w-16 h-16 bg-blue-500 rounded-xl flex items-center justify-center">
            <i class="fas fa-box text-white text-2xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
            <p class="text-gray-600">Ürün ID: #<?= $product_id ?></p>
            <p class="text-sm text-gray-500">
                Son güncelleme: <?= date('d.m.Y H:i', strtotime($product['updated_at'] ?? $product['created_at'])) ?>
            </p>
        </div>
    </div>
</div>