<!-- Fiyat Bilgisi -->
<div class="price-section">
    <div class="flex items-center gap-3">
        <span class="text-2xl font-bold text-secondary">₺ <?php echo number_format($product['base_price'], 2); ?></span>
        <?php
        $has_discount = false;
        $original_price = 0;
        
        foreach ($product_variants as $variant) {
            if (isset($variant['original_price']) && $variant['original_price'] > 0) {
                $has_discount = true;
                $original_price = $variant['original_price'];
                break;
            }
        }
        
        if ($has_discount):
        ?>
            <span class="text-lg text-gray-400 line-through">₺ <?php echo number_format($original_price, 2); ?></span>
            <span class="bg-green-500 text-white text-sm px-2 py-1 rounded">İndirim</span>
        <?php endif; ?>
    </div>
</div>
