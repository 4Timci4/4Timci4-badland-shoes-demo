<script>
// Verileri global değişkenler olarak tanımlayalım
const productData = <?php echo json_encode($product); ?>;
const productVariantsData = productData.variants || [];
const productColorsData = <?php echo json_encode($all_colors ?? []); ?>;
const productName = "<?php echo addslashes($product['name']); ?>";
const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
const currentUser = <?php echo $current_user ? json_encode($current_user) : 'null'; ?>;
const favoriteVariantIds = <?php echo json_encode($favorite_variant_ids); ?>;
</script>
<script src="/assets/js/product-detail.js"></script>

<!-- Favori özelliği aktif -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Favori özelliği aktif. Giriş durumu:', isLoggedIn);
});
</script>
