<?php
require_once 'lib/SimpleCache.php';

// Cache'i temizle
$cache = simple_cache();

// Kategori cache'lerini temizle
$cache->delete('categories_with_counts_optimized');
$cache->delete('categories_with_counts_optimized_all');
$cache->delete('categories_with_counts_optimized_non_empty');
$cache->delete('category_product_counts_optimized');
$cache->delete('categories_hierarchy_optimized');

// Ürün cache'lerini temizle
$cache->deleteByPattern('products_api_*');
$cache->deleteByPattern('popular_products_*');
$cache->deleteByPattern('similar_products_*');

echo "Cache temizlendi!\n";
?>