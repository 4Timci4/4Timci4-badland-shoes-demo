<?php
/**
 * =================================================================
 * BANDLAND SHOES - OPTIMIZED CATEGORY SERVICE
 * =================================================================
 * N+1 Query Problem Solution for Categories
 * 
 * OPTIMIZATIONS:
 * - Batch processing for category queries
 * - Cache integration for frequent requests
 * - Single JOIN operations instead of multiple queries
 * - Performance metrics tracking
 * =================================================================
 */

require_once __DIR__ . '/../lib/DatabaseFactory.php';
require_once __DIR__ . '/../lib/SimpleCache.php';

class OptimizedCategoryService {
    private $db;
    private $cache;
    private $performance_metrics;
    
    public function __construct() {
        $this->db = database();
        $this->cache = simple_cache();
        $this->performance_metrics = [
            'execution_time_ms' => 0,
            'queries_executed' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'categories_count' => 0
        ];
    }
    
    /**
     * Get categories with product counts using REST API (N+1 solution)
     */
    public function getCategoriesWithProductCountsOptimized($include_empty = true) {
        $start_time = microtime(true);
        $cache_key = 'categories_with_counts_optimized_' . ($include_empty ? 'all' : 'non_empty');
        
        // Try cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== null) {
            $this->performance_metrics['cache_hits']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return $cached_result;
        }
        
        $this->performance_metrics['cache_misses']++;
        
        try {
            // Get categories using REST API
            $categories = $this->db->select('categories', [], '*', [
                'order' => 'name ASC'
            ]);
            
            $this->performance_metrics['queries_executed']++;
            
            if (empty($categories)) {
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return [];
            }
            
            // Get all product-category relationships for counting
            $category_ids = array_column($categories, 'id');
            $product_categories = $this->db->select('product_categories',
                ['category_id' => ['IN', $category_ids]],
                'category_id'
            );
            
            $this->performance_metrics['queries_executed']++;
            
            // Count products per category in PHP
            $counts_lookup = [];
            foreach ($product_categories as $pc) {
                $category_id = $pc['category_id'];
                $counts_lookup[$category_id] = ($counts_lookup[$category_id] ?? 0) + 1;
            }
            
            // Merge categories with their counts
            $result = [];
            foreach ($categories as $category) {
                $category['product_count'] = $counts_lookup[$category['id']] ?? 0;
                
                // If include_empty is false, skip categories with 0 products
                if (!$include_empty && $category['product_count'] == 0) {
                    continue;
                }
                
                $result[] = $category;
            }
            
            $this->performance_metrics['categories_count'] = count($result);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            // Cache for 1 hour
            $this->cache->set($cache_key, $result, 3600);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("OptimizedCategoryService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }
    
    /**
     * Get category product counts using REST API
     */
    public function getCategoryProductCountsOptimized() {
        $start_time = microtime(true);
        $cache_key = 'category_product_counts_optimized';
        
        // Try cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== null) {
            $this->performance_metrics['cache_hits']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return $cached_result;
        }
        
        $this->performance_metrics['cache_misses']++;
        
        try {
            // Get categories
            $categories = $this->db->select('categories', [], 'id, name');
            $this->performance_metrics['queries_executed']++;
            
            if (empty($categories)) {
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return [];
            }
            
            // Get product counts using REST API
            $category_ids = array_column($categories, 'id');
            $product_counts = $this->db->select('product_categories', 
                ['category_id' => ['IN', $category_ids]], 
                'category_id, COUNT(*) as product_count',
                ['group_by' => 'category_id']
            );
            
            $this->performance_metrics['queries_executed']++;
            
            // Create result array
            $result = [];
            foreach ($categories as $category) {
                $count = 0;
                foreach ($product_counts as $pc) {
                    if ($pc['category_id'] == $category['id']) {
                        $count = intval($pc['product_count']);
                        break;
                    }
                }
                $result[] = [
                    'category_id' => $category['id'],
                    'category_name' => $category['name'],
                    'product_count' => $count
                ];
            }
            
            $this->performance_metrics['categories_count'] = count($result);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            // Cache for 30 minutes
            $this->cache->set($cache_key, $result, 1800);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("OptimizedCategoryService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }
    
    /**
     * Get categories hierarchy using REST API
     */
    public function getCategoriesHierarchyOptimized() {
        $start_time = microtime(true);
        $cache_key = 'categories_hierarchy_optimized';
        
        // Try cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== null) {
            $this->performance_metrics['cache_hits']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return $cached_result;
        }
        
        $this->performance_metrics['cache_misses']++;
        
        try {
            // Get all categories with parent relationships
            $categories = $this->db->select('categories', [], '*', [
                'order' => 'name ASC'
            ]);
            
            $this->performance_metrics['queries_executed']++;
            
            if (empty($categories)) {
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return [];
            }
            
            // Build hierarchy
            $hierarchy = [];
            $categories_by_id = [];
            
            // Index categories by ID
            foreach ($categories as $category) {
                $categories_by_id[$category['id']] = $category;
                $categories_by_id[$category['id']]['children'] = [];
            }
            
            // Build parent-child relationships
            foreach ($categories as $category) {
                if ($category['parent_id'] === null) {
                    // Root category
                    $hierarchy[] = &$categories_by_id[$category['id']];
                } else {
                    // Child category
                    if (isset($categories_by_id[$category['parent_id']])) {
                        $categories_by_id[$category['parent_id']]['children'][] = &$categories_by_id[$category['id']];
                    }
                }
            }
            
            $this->performance_metrics['categories_count'] = count($hierarchy);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            // Cache for 2 hours
            $this->cache->set($cache_key, $hierarchy, 7200);
            
            return $hierarchy;
            
        } catch (Exception $e) {
            error_log("OptimizedCategoryService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }
    
    /**
     * Get categories with subcategories and counts (for JavaScript frontend)
     */
    public function getCategoriesWithSubcategoriesAndCounts() {
        $start_time = microtime(true);
        $cache_key = 'categories_with_subcategories_counts';
        
        // Try cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== null) {
            $this->performance_metrics['cache_hits']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return $cached_result;
        }
        
        $this->performance_metrics['cache_misses']++;
        
        try {
            // Get all categories
            $categories = $this->db->select('categories', [], '*', [
                'order' => 'name ASC'
            ]);
            
            $this->performance_metrics['queries_executed']++;
            
            if (empty($categories)) {
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return [];
            }
            
            // Get product counts for all categories
            $category_ids = array_column($categories, 'id');
            $product_counts = $this->db->select('product_categories',
                ['category_id' => ['IN', $category_ids]],
                'category_id, COUNT(*) as product_count',
                ['group_by' => 'category_id']
            );
            
            $this->performance_metrics['queries_executed']++;
            
            // Create lookup array for counts
            $counts_lookup = [];
            foreach ($product_counts as $count) {
                $counts_lookup[$count['category_id']] = intval($count['product_count']);
            }
            
            // Build hierarchy with subcategories
            $result = [];
            $categories_by_id = [];
            
            // Index categories by ID and add product counts
            foreach ($categories as $category) {
                $category['product_count'] = $counts_lookup[$category['id']] ?? 0;
                $categories_by_id[$category['id']] = $category;
            }
            
            // Build parent-child relationships
            foreach ($categories as $category) {
                if ($category['parent_id'] === null) {
                    // Root category
                    $main_category = $categories_by_id[$category['id']];
                    $main_category['subcategories'] = [];
                    
                    // Find subcategories
                    foreach ($categories as $sub_category) {
                        if ($sub_category['parent_id'] == $category['id']) {
                            $sub_category['product_count'] = $counts_lookup[$sub_category['id']] ?? 0;
                            $main_category['subcategories'][] = $sub_category;
                        }
                    }
                    
                    $result[] = $main_category;
                }
            }
            
            $this->performance_metrics['categories_count'] = count($result);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            // Cache for 1 hour
            $this->cache->set($cache_key, $result, 3600);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("OptimizedCategoryService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }
    
    /**
     * Get category IDs from slugs
     */
    public function getCategoryIdsBySlug($slugs) {
        if (empty($slugs)) {
            return [];
        }
        
        if (!is_array($slugs)) {
            $slugs = [$slugs];
        }
        
        try {
            $categories = $this->db->select('categories',
                ['slug' => ['IN', $slugs]],
                'id, slug'
            );
            
            return array_column($categories, 'id');
        } catch (Exception $e) {
            error_log("OptimizedCategoryService::getCategoryIdsBySlug Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics() {
        return $this->performance_metrics;
    }
    
    /**
     * Clear category cache
     */
    public function clearCache() {
        $this->cache->delete('categories_with_counts_optimized_all');
        $this->cache->delete('categories_with_counts_optimized_non_empty');
        $this->cache->delete('category_product_counts_optimized');
        $this->cache->delete('categories_hierarchy_optimized');
    }
}

/**
 * Global function for service access
 */
function optimized_category_service() {
    static $instance = null;
    if ($instance === null) {
        $instance = new OptimizedCategoryService();
    }
    return $instance;
}