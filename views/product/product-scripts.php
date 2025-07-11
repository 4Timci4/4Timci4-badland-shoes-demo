<script>
// Verileri global değişkenler olarak tanımlayalım
const productData = <?php echo json_encode($product); ?>;
const productVariantsData = productData.variants || [];
const productColorsData = <?php echo json_encode($all_colors ?? []); ?>; // Bu, controller'dan gelmeye devam ediyor
const productName = "<?php echo addslashes($product['name']); ?>";
const isLoggedIn = false; // Session kaldırıldı
</script>
<script src="/assets/js/product-detail.js"></script>

<!-- Session kaldırıldı - Favori özelliği çalışmaz -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Session yönetimi kaldırıldı - Favori özelliği çalışmamaktadır.');
});
</script>
