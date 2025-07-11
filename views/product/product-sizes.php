<!-- Beden Seçimi -->
<div class="size-selection">
    <h3 class="text-lg font-semibold text-secondary mb-3">Beden Seçimi:</h3>
    <div class="flex flex-wrap" style="max-width: 320px;">
        <?php
        // Ürüne tanımlı tüm bedenleri topla
        $product_size_ids = [];
        foreach ($product['variants'] as $variant) {
            if (!in_array($variant['size_id'], $product_size_ids)) {
                $product_size_ids[] = $variant['size_id'];
            }
        }
        
        // Ürüne tanımlı bedenlerin bilgilerini al
        $product_sizes = [];
        if (!empty($product_size_ids)) {
            $product_sizes = $db->select('sizes', ['id' => ['in', $product_size_ids]]);
            usort($product_sizes, fn($a, $b) => strnatcmp($a['size_value'], $b['size_value']));
        }
        
        foreach($product_sizes as $size):
            // Seçilen renk için bu beden mevcut mu kontrol et
            $is_available = false;
            if ($selected_color_id) {
                foreach ($product['variants'] as $variant) {
                    if ($variant['color_id'] === $selected_color_id && $variant['size_id'] === $size['id'] && $variant['stock_quantity'] > 0) {
                        $is_available = true;
                        break;
                    }
                }
            }
            
            $class = "size-option w-12 h-12 flex items-center justify-center border border-gray-300 rounded-lg hover:border-brand hover:bg-brand hover:text-secondary transition-all font-medium m-1";
            if (!$is_available) {
                $class .= " line-through opacity-50 unavailable";
            }
        ?>
            <button class="<?php echo $class; ?>"
                    data-size="<?php echo $size['id']; ?>"
                    data-size-value="<?php echo $size['size_value']; ?>"
                    <?php echo !$is_available ? 'disabled' : ''; ?>>
                <?php echo $size['size_value']; ?>
            </button>
        <?php endforeach; ?>
    </div>
    <p class="text-sm text-gray-600 mt-2">Seçili beden: <span id="selected-size">-</span></p>
</div>
