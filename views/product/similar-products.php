<!-- Benzer Ürünler -->
<?php if (!empty($similar_products)): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-secondary mb-4">Benzer Ürünler</h2>
            <p class="text-gray-600">Beğenebileceğiniz diğer modellerimiz</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <!-- Benzer Ürünler -->
            <?php foreach($similar_products as $p): ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                    <div class="aspect-square bg-gray-100">
                        <img src="<?php echo isset($p['image_url']) ? $p['image_url'] : ''; ?>"
                             alt="<?php echo $p['name']; ?>"
                             class="w-full h-full object-cover">
                    </div>
                    <div class="p-4 text-center">
                        <h3 class="font-semibold text-secondary mb-2"><?php echo $p['name']; ?></h3>
                        <div class="text-xl font-bold text-secondary">₺ <?php echo number_format($p['base_price'], 2); ?></div>
                        <a href="/product-details.php?id=<?php echo $p['id']; ?>"
                           class="inline-block mt-3 bg-primary text-white px-6 py-2 rounded-lg hover:bg-pink-600 transition-colors mx-auto text-center w-full">
                            Ürün Detayı
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
