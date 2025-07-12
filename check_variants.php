<?php
require_once 'config/database.php';

$db = database();
$variants = $db->select('product_variants', ['model_id' => ['eq', 1]]);

echo "Ürün ID 1 için varyantlar:\n";
print_r($variants);

// Gri renk ID'sini bul
$gray_color = $db->select('colors', ['name' => ['like', '%gri%']]);
echo "\nGri renk bilgileri:\n";
print_r($gray_color);

// Gri renk için varyantlar
if (!empty($gray_color)) {
    $gray_color_id = $gray_color[0]['id'];
    $gray_variants = $db->select('product_variants', [
        'model_id' => ['eq', 1],
        'color_id' => ['eq', $gray_color_id]
    ]);
    
    echo "\nGri renk için varyantlar:\n";
    print_r($gray_variants);
    
    // 41 numara ID'sini bul
    $size_41 = $db->select('sizes', ['size_value' => ['eq', '41']]);
    echo "\n41 numara bilgileri:\n";
    print_r($size_41);
    
    // Gri renk ve 41 numara için varyant
    if (!empty($size_41)) {
        $size_41_id = $size_41[0]['id'];
        $gray_41_variant = $db->select('product_variants', [
            'model_id' => ['eq', 1],
            'color_id' => ['eq', $gray_color_id],
            'size_id' => ['eq', $size_41_id]
        ]);
        
        echo "\nGri renk ve 41 numara için varyant:\n";
        print_r($gray_41_variant);
    }
}
?>