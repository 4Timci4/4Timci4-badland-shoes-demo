<script>
// Verileri global değişkenler olarak tanımlayalım
const productVariantsData = <?php echo json_encode($product_variants); ?>;
const productColorsData = <?php echo json_encode($colors); ?>;
const productName = "<?php echo addslashes($product['name']); ?>";
</script>
<script src="/assets/js/product-detail.js"></script>
