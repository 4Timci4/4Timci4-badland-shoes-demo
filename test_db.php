<?php
require_once 'config/database.php';

// Veritabanı bağlantısını al
$db = database();

// Veritabanı türünü kontrol et
$dbType = DatabaseFactory::getCurrentType();
echo "Veritabanı türü: " . $dbType . "\n";

// Ürün ID 1 için veri çek
echo "Ürün ID 1 için veriler (product_details_view):\n";
try {
    $product = $db->select('product_details_view', ['id' => ['eq', 1]]);
    if (!empty($product)) {
        echo "Ürün adı: " . $product[0]['name'] . "\n";
        
        // JSON verilerini decode et
        $categories = json_decode($product[0]['categories'], true);
        $genders = json_decode($product[0]['genders'], true);
        $variants = json_decode($product[0]['variants'], true);
        $images = json_decode($product[0]['images'], true);
        
        echo "Kategoriler: " . print_r($categories, true) . "\n";
        echo "Cinsiyetler: " . print_r($genders, true) . "\n";
        echo "Varyantlar: " . print_r($variants, true) . "\n";
        echo "Resimler: " . print_r($images, true) . "\n";
    } else {
        echo "Ürün bulunamadı.\n";
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}

// Benzer ürünleri çek
echo "\nBenzer ürünler:\n";
try {
    $product_api_service = product_api_service();
    $similar_products = $product_api_service->getSimilarProducts(1, 5);
    echo "Benzer ürün sayısı: " . count($similar_products) . "\n";
    foreach ($similar_products as $product) {
        echo "- " . $product['name'] . "\n";
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>