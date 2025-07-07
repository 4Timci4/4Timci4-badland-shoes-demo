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
        ?>
            <button class="color-option w-10 h-10 rounded-full border-2 <?php echo $index === 0 ? 'border-secondary' : 'border-gray-300'; ?> hover:border-secondary transition-colors"
                    style="background-color: <?php echo $color['hex_code']; ?>"
                    data-color-id="<?php echo $color['id']; ?>"
                    data-color-name="<?php echo $color['name']; ?>"
                    title="<?php echo $color['name']; ?>">
            </button>
        <?php endforeach; ?>
    </div>
    <p class="text-sm text-gray-600 mt-2">Seçili renk: <span id="selected-color"><?php 
        if (!empty($unique_color_variants)) {
            $first_color_id = $unique_color_variants[0]['color_id'];
            echo isset($all_colors_map[$first_color_id]) ? $all_colors_map[$first_color_id]['name'] : '-';
        } else {
            echo '-';
        }
    ?></span></p>
</div>
