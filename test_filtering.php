<?php
/**
 * Filtreleme Sistemi Test Scripti
 */

// Gerekli dosyaları dahil et
require_once 'services/OptimizedCategoryService.php';
require_once 'services/Product/OptimizedProductApiService.php';
require_once 'services/GenderService.php';
require_once 'lib/SimpleCache.php';

echo "=== FİLTRELEME SİSTEMİ TEST SCRİPTİ ===\n\n";

// Servis instanceları
$category_service = optimized_category_service();
$product_api_service = optimized_product_api_service();
$gender_service = gender_service();

// Test 1: Kategorileri listele
echo "1. KATEGORİLER:\n";
$categories = $category_service->getCategoriesWithProductCountsOptimized();
foreach ($categories as $category) {
    echo "  - {$category['name']} (slug: {$category['slug']}, ID: {$category['id']}, ürün sayısı: {$category['product_count']})\n";
}
echo "\n";

// Test 2: Cinsiyetleri listele
echo "2. CİNSİYETLER:\n";
$genders = $gender_service->getAllGenders();
foreach ($genders as $gender) {
    echo "  - {$gender['name']} (slug: {$gender['slug']}, ID: {$gender['id']})\n";
}
echo "\n";

// Test 3: Slug'dan ID'ye dönüşüm testi
echo "3. SLUG'DAN ID'YE DÖNÜŞÜM TESTİ:\n";
$test_category_slugs = ['spor-ayakkabi', 'bot'];
$category_ids = $category_service->getCategoryIdsBySlug($test_category_slugs);
echo "  Kategori slug'ları: " . implode(', ', $test_category_slugs) . "\n";
echo "  Dönüştürülen ID'ler: " . implode(', ', $category_ids) . "\n";

$test_gender_slugs = ['erkek', 'kadin'];
$gender_ids = $gender_service->getGenderIdsBySlug($test_gender_slugs);
echo "  Cinsiyet slug'ları: " . implode(', ', $test_gender_slugs) . "\n";
echo "  Dönüştürülen ID'ler: " . implode(', ', $gender_ids) . "\n\n";

// Test 4: Filtrelemesiz ürün listesi
echo "4. TÜM ÜRÜNLER (ilk 5):\n";
$all_products = $product_api_service->getProductsForApi([
    'page' => 1,
    'limit' => 5
]);
echo "  Toplam ürün sayısı: {$all_products['total']}\n";
echo "  Sayfa: {$all_products['page']}\n";
echo "  Limit: {$all_products['limit']}\n";
echo "  Toplam sayfa: {$all_products['pages']}\n";
echo "  Bulunan ürünler:\n";
foreach ($all_products['products'] as $product) {
    echo "    - {$product['name']} (ID: {$product['id']}, Fiyat: {$product['base_price']})\n";
}
echo "\n";

// Test 5: Kategori filtreleme
echo "5. KATEGORİ FİLTRELEME TESTİ:\n";
if (!empty($category_ids)) {
    $filtered_products = $product_api_service->getProductsForApi([
        'page' => 1,
        'limit' => 3,
        'categories' => [$category_ids[0]] // İlk kategoriyi kullan
    ]);
    echo "  Kategori ID {$category_ids[0]} ile filtrelenmiş ürünler:\n";
    echo "  Toplam ürün sayısı: {$filtered_products['total']}\n";
    foreach ($filtered_products['products'] as $product) {
        echo "    - {$product['name']} (ID: {$product['id']})\n";
    }
} else {
    echo "  Kategori ID'si bulunamadı, test atlanıyor.\n";
}
echo "\n";

// Test 6: Cinsiyet filtreleme
echo "6. CİNSİYET FİLTRELEME TESTİ:\n";
if (!empty($gender_ids)) {
    $gender_filtered_products = $product_api_service->getProductsForApi([
        'page' => 1,
        'limit' => 3,
        'genders' => [$gender_ids[0]] // İlk cinsiyeti kullan
    ]);
    echo "  Cinsiyet ID {$gender_ids[0]} ile filtrelenmiş ürünler:\n";
    echo "  Toplam ürün sayısı: {$gender_filtered_products['total']}\n";
    foreach ($gender_filtered_products['products'] as $product) {
        echo "    - {$product['name']} (ID: {$product['id']})\n";
    }
} else {
    echo "  Cinsiyet ID'si bulunamadı, test atlanıyor.\n";
}
echo "\n";

// Test 7: Öne çıkan ürünler filtreleme
echo "7. ÖNE ÇIKAN ÜRÜNLER FİLTRELEME TESTİ:\n";
$featured_products = $product_api_service->getProductsForApi([
    'page' => 1,
    'limit' => 3,
    'featured' => true
]);
echo "  Öne çıkan ürünler:\n";
echo "  Toplam ürün sayısı: {$featured_products['total']}\n";
foreach ($featured_products['products'] as $product) {
    echo "    - {$product['name']} (ID: {$product['id']}, Öne çıkan: " . ($product['is_featured'] ? 'Evet' : 'Hayır') . ")\n";
}
echo "\n";

// Test 8: Kombine filtreleme
echo "8. KOMBİNE FİLTRELEME TESTİ (Kategori + Cinsiyet):\n";
if (!empty($category_ids) && !empty($gender_ids)) {
    $combined_products = $product_api_service->getProductsForApi([
        'page' => 1,
        'limit' => 3,
        'categories' => [$category_ids[0]],
        'genders' => [$gender_ids[0]]
    ]);
    echo "  Kategori ID {$category_ids[0]} + Cinsiyet ID {$gender_ids[0]} ile filtrelenmiş ürünler:\n";
    echo "  Toplam ürün sayısı: {$combined_products['total']}\n";
    foreach ($combined_products['products'] as $product) {
        echo "    - {$product['name']} (ID: {$product['id']})\n";
    }
} else {
    echo "  Gerekli ID'ler bulunamadı, test atlanıyor.\n";
}
echo "\n";

// Test 9: Sıralama testi
echo "9. SIRALAMA TESTİ:\n";
$sorted_products = $product_api_service->getProductsForApi([
    'page' => 1,
    'limit' => 3,
    'sort' => 'price-asc'
]);
echo "  Fiyata göre artan sırada:\n";
foreach ($sorted_products['products'] as $product) {
    echo "    - {$product['name']} (Fiyat: {$product['base_price']})\n";
}
echo "\n";

echo "=== TEST TAMAMLANDI ===\n";
?>