<!-- Renk Seçimi -->
<div class="color-selection">
    <h3 class="text-lg font-semibold text-secondary mb-3">Renk Seçimi:</h3>
    <div class="flex gap-3">
        <?php foreach($colors as $index => $color): ?>
            <button class="color-option w-10 h-10 rounded-full border-2 <?php echo $index === 0 ? 'border-secondary' : 'border-gray-300'; ?> hover:border-secondary transition-colors"
                    style="background-color: <?php echo $color['hex_code']; ?>"
                    data-color-id="<?php echo $color['id']; ?>"
                    data-color-name="<?php echo $color['name']; ?>"
                    title="<?php echo $color['name']; ?>">
            </button>
        <?php endforeach; ?>
    </div>
    <p class="text-sm text-gray-600 mt-2">Seçili renk: <span id="selected-color"><?php echo !empty($colors) ? $colors[0]['name'] : '-'; ?></span></p>
</div>
