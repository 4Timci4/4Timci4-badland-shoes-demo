<!-- Beden Seçimi -->
<div class="size-selection">
    <h3 class="text-lg font-semibold text-secondary mb-3">Beden Seçimi:</h3>
    <div class="flex flex-wrap" style="max-width: 320px;">
        <?php foreach($sizes as $size): ?>
            <button class="size-option w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:border-brand hover:bg-brand hover:text-secondary transition-all font-medium m-1"
                    data-size="<?php echo $size['id']; ?>"
                    data-size-value="<?php echo $size['size_value']; ?>">
                <?php echo $size['size_value']; ?>
            </button>
        <?php endforeach; ?>
    </div>
    <p class="text-sm text-gray-600 mt-2">Seçili beden: <span id="selected-size">-</span></p>
</div>
