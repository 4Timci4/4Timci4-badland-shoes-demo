<?php
require_once 'config/database.php';

$db = database();

// product_details_view görünümünden ürün ID 1 için veri çek
echo "Ürün ID 1 için veriler (product_details_view):\n";
$product = $db->select('product_details_view', ['id' => ['eq', 1]]);
if (!empty($product)) {
    // Varyantları decode et
    $variants = json_decode($product[0]['variants'], true);
    
    echo "\nÜrün varyantları:\n";
    print_r($variants);
    
    // Gri renk ID'si 3 için varyantları filtrele
    echo "\nGri renk (ID: 3) için varyantlar:\n";
    $grayVariants = array_filter($variants, function($v) {
        return $v['color_id'] == 3;
    });
    print_r($grayVariants);
    
    // 41 numara (ID: 18) için varyantları filtrele
    echo "\n41 numara (ID: 18) için varyantlar:\n";
    $size41Variants = array_filter($variants, function($v) {
        return $v['size_id'] == 18;
    });
    print_r($size41Variants);
    
    // Gri renk ve 41 numara için varyantları filtrele
    echo "\nGri renk (ID: 3) ve 41 numara (ID: 18) için varyantlar:\n";
    $gray41Variants = array_filter($variants, function($v) {
        return $v['color_id'] == 3 && $v['size_id'] == 18;
    });
    print_r($gray41Variants);
    
    // Tüm renkleri listele
    echo "\nTüm renkler:\n";
    $colors = $db->select('colors');
    print_r($colors);
    
    // Tüm bedenleri listele
    echo "\nTüm bedenler:\n";
    $sizes = $db->select('sizes');
    print_r($sizes);
}
?>