<!-- Renk Seçimi -->
<div class="color-selection">
    <h3 class="text-lg font-semibold text-secondary mb-3">Renk Seçimi:</h3>
    <div class="flex gap-3">
        <?php foreach($all_colors as $color):
            $color_id = $color['id'];
            
            // Renk slug'ını oluştur
            $color_slug = createColorSlug($color['name']);
            
            // URL'yi oluştur
            $color_url = "product-details.php?id=" . $product_id . "&color=" . $color_slug;
            
            // Bu renk seçili mi kontrol et
            $is_selected = ($selected_color_id == $color['id']);
        ?>
            <button type="button"
               class="color-option w-10 h-10 rounded-full border-2 <?php echo $is_selected ? 'border-secondary' : 'border-gray-300'; ?> hover:border-secondary transition-all duration-200 block"
               style="background-color: <?php echo htmlspecialchars($color['hex_code']); ?>"
               data-color-id="<?php echo $color['id']; ?>"
               data-color-name="<?php echo htmlspecialchars($color['name']); ?>"
               data-color-slug="<?php echo htmlspecialchars($color_slug); ?>"
               title="<?php echo htmlspecialchars($color['name']); ?>">
            </button>
        <?php endforeach; ?>
    </div>
    <p class="text-sm text-gray-600 mt-2">Seçili renk: <span id="selected-color"><?php
        $selected_color_name = '-';
        if ($selected_color_id) {
            foreach ($all_colors as $color) {
                if ($color['id'] === $selected_color_id) {
                    $selected_color_name = htmlspecialchars($color['name']);
                    break;
                }
            }
        }
        echo $selected_color_name;
    ?></span></p>
</div>
