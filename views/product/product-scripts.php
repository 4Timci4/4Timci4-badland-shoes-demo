<script>
// Verileri global değişkenler olarak tanımlayalım
const productData = <?php echo json_encode($product); ?>;
const productVariantsData = productData.variants || [];
const productColorsData = <?php echo json_encode($all_colors ?? []); ?>; // Bu, controller'dan gelmeye devam ediyor
const productName = "<?php echo addslashes($product['name']); ?>";
</script>
<script src="/assets/js/product-detail.js"></script>
