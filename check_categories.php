<?php
require_once 'config/database.php';
require_once 'services/CategoryService.php';

// CategoryService'i başlat
$categoryService = new CategoryService();

// Tüm kategorileri ürün sayılarıyla birlikte al
echo "Tüm kategoriler ürün sayılarıyla (category_name'e göre sıralı):\n";
$categories = $categoryService->getCategoriesWithProductCounts();
print_r($categories);

// Kategori sayılarını al (boş kategoriler dahil)
echo "\nKategori ürün sayıları (boş kategoriler dahil):\n";
$categoryCounts = $categoryService->getCategoriesWithProductCounts(true);
print_r($categoryCounts);

// Performans metriklerini al
echo "\nPerformans metrikleri:\n";
$metrics = $categoryService->getPerformanceMetrics();
print_r($metrics);

echo "\nTest tamamlandı.\n";
?>