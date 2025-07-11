<script id="color-image-data" type="application/json">
<?php
    $colorImageData = [];
    foreach ($all_colors as $color) {
        $colorId = $color['id'];
        $colorImageData[$colorId] = [];
        
        // Bu renk için görselleri filtrele
        foreach ($product['images'] as $image) {
            if ($image['color_id'] === $colorId) {
                $colorImageData[$colorId][] = $image;
            }
        }
        
        // Eğer bu renk için görsel yoksa, ana görselleri kullan
        if (empty($colorImageData[$colorId])) {
            foreach ($product['images'] as $image) {
                if ($image['is_primary']) {
                    $colorImageData[$colorId][] = $image;
                }
            }
        }
        
        // Hala görsel yoksa, herhangi bir görseli kullan
        if (empty($colorImageData[$colorId]) && !empty($product['images'])) {
            $colorImageData[$colorId][] = $product['images'][0];
        }
    }
    
    echo json_encode($colorImageData);
?>
</script>

<script>
// Verileri global değişkenler olarak tanımlayalım
const productData = <?php echo json_encode($product); ?>;
const productVariantsData = productData.variants || [];
const productColorsData = <?php echo json_encode($all_colors ?? []); ?>;
const productName = "<?php echo addslashes($product['name']); ?>";
const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
const currentUser = <?php echo $current_user ? json_encode($current_user) : 'null'; ?>;
const favoriteVariantIds = <?php echo json_encode($favorite_variant_ids); ?>;

// Beden verilerini global değişken olarak tanımla
const productSizesData = <?php echo json_encode($available_sizes ?? []); ?>;
</script>
<script src="/assets/js/product-detail.js"></script>

<!-- Favori özelliği aktif -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Favori özelliği aktif. Giriş durumu:', isLoggedIn);
});
</script>
