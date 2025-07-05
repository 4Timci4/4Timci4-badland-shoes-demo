<!-- Sol Taraf - Ürün Görselleri -->
<div class="lg:col-span-1 space-y-4">
    <div class="main-image bg-gray-100 rounded-lg overflow-hidden aspect-square">
        <img id="main-product-image"
             src="<?php echo isset($product_images[0]['image_url']) ? $product_images[0]['image_url'] : ''; ?>"
             alt="<?php echo isset($product_images[0]['alt_text']) ? $product_images[0]['alt_text'] : $product['name']; ?>"
             class="w-full h-full object-cover">
    </div>
    
    <div class="thumbnail-images grid grid-cols-4 gap-4">
        <?php foreach($product_images as $index => $image): ?>
            <div class="thumbnail bg-gray-100 rounded-lg overflow-hidden aspect-square cursor-pointer border-2 <?php echo $index === 0 ? 'border-primary' : 'border-transparent hover:border-gray-300'; ?>"
                 onclick="changeMainImage('<?php echo $image['image_url']; ?>', this)">
                <img src="<?php echo $image['image_url']; ?>"
                     alt="<?php echo isset($image['alt_text']) ? $image['alt_text'] : $product['name']; ?>"
                     class="w-full h-full object-cover">
            </div>
        <?php endforeach; ?>
    </div>
</div>
