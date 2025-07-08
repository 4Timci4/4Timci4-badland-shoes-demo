<!-- Renk Seçimi -->
<div class="color-selection">
    <h3 class="text-lg font-semibold text-secondary mb-3">Renk Seçimi:</h3>
    <div class="flex gap-3">
        <?php 
        // Benzersiz renkleri al
        $unique_color_variants = [];
        $seen_colors = [];
        
        foreach($product_variants as $variant) {
            $color_id = $variant['color_id'];
            if (!isset($seen_colors[$color_id])) {
                $seen_colors[$color_id] = true;
                $unique_color_variants[] = $variant;
            }
        }
        
        foreach($unique_color_variants as $index => $variant): 
            $color_id = $variant['color_id'];
            $color = $all_colors_map[$color_id] ?? null;
            if (!$color) continue;
            
            // Renk slug'ını oluştur
            $color_slug = createColorSlug($color['name']);
            
            // URL'yi oluştur
            $color_url = "product-details.php?id=" . $product_id . "&color=" . $color_slug;
            
            // Bu renk seçili mi kontrol et
            $is_selected = ($selected_color_id == $color['id']);
        ?>
            <a href="<?php echo htmlspecialchars($color_url); ?>" 
               class="color-option w-10 h-10 rounded-full border-2 <?php echo $is_selected ? 'border-secondary' : 'border-gray-300'; ?> hover:border-secondary transition-colors block"
               style="background-color: <?php echo htmlspecialchars($color['hex_code']); ?>"
               data-color-id="<?php echo $color['id']; ?>"
               data-color-name="<?php echo htmlspecialchars($color['name']); ?>"
               data-color-slug="<?php echo htmlspecialchars($color_slug); ?>"
               title="<?php echo htmlspecialchars($color['name']); ?>">
            </a>
        <?php endforeach; ?>
    </div>
    <p class="text-sm text-gray-600 mt-2">Seçili renk: <span id="selected-color"><?php 
        echo $selected_color ? htmlspecialchars($selected_color['name']) : '-';
    ?></span></p>
</div>
