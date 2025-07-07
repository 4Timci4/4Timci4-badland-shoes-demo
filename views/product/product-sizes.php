<!-- Beden Seçimi -->
<div class="size-selection">
    <h3 class="text-lg font-semibold text-secondary mb-3">Beden Seçimi:</h3>
    <div class="flex flex-wrap" style="max-width: 320px;">
        <?php 
        // Benzersiz bedenleri doğrudan ürün varyantlarından al
        $unique_size_variants = [];
        $seen_sizes = [];
        
        foreach($product_variants as $variant) {
            $size_id = $variant['size_id'];
            if (!isset($seen_sizes[$size_id])) {
                $seen_sizes[$size_id] = true;
                $unique_size_variants[] = $variant;
            }
        }
        
        // Bedenleri sırala
        usort($unique_size_variants, function($a, $b) use ($all_sizes_map) {
            $size_a = isset($all_sizes_map[$a['size_id']]) ? $all_sizes_map[$a['size_id']]['size_value'] : '0';
            $size_b = isset($all_sizes_map[$b['size_id']]) ? $all_sizes_map[$b['size_id']]['size_value'] : '0';
            return strnatcmp($size_a, $size_b);
        });
        
        foreach($unique_size_variants as $variant): 
            $size_id = $variant['size_id'];
            $size = $all_sizes_map[$size_id] ?? null;
            if (!$size) continue;
        ?>
            <button class="size-option w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:border-brand hover:bg-brand hover:text-secondary transition-all font-medium m-1"
                    data-size="<?php echo $size['id']; ?>"
                    data-size-value="<?php echo $size['size_value']; ?>">
                <?php echo $size['size_value']; ?>
            </button>
        <?php endforeach; ?>
    </div>
    <p class="text-sm text-gray-600 mt-2">Seçili beden: <span id="selected-size">-</span></p>
</div>
