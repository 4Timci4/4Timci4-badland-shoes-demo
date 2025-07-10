<!-- Benzer Ürünler -->
<?php if (!empty($similar_products)): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-secondary mb-4">Benzer Ürünler</h2>
            <p class="text-gray-600">Beğenebileceğiniz diğer modellerimiz</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <?php foreach ($similar_products as $p): ?>
                <div class="product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 group h-full flex flex-col">
                    <div class="relative overflow-hidden bg-gray-100 aspect-square">
                        <a href="product-details.php?id=<?php echo $p['id']; ?>">
                            <img src="<?php echo htmlspecialchars($p['primary_image'] ?? 'assets/images/placeholder.svg'); ?>"
                                 alt="<?php echo htmlspecialchars($p['name']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                        </a>
                        <?php if ($p['is_featured']): ?>
                            <span class="absolute top-3 left-3 bg-primary text-white text-xs px-2 py-1 rounded">Öne Çıkan</span>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <a href="product-details.php?id=<?php echo $p['id']; ?>" class="w-10 h-10 bg-white rounded-full hover:bg-primary hover:text-white transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100" title="Ürün Detayı"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="p-4 text-center flex flex-col flex-grow">
                        <h3 class="text-lg font-medium text-secondary mb-3 min-h-[3.5rem] flex items-center justify-center">
                            <a href="product-details.php?id=<?php echo $p['id']; ?>" class="text-inherit hover:text-primary transition-colors line-clamp-2"><?php echo htmlspecialchars($p['name']); ?></a>
                        </h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
