<?php
/**
 * =================================================================
 * BANDLAND SHOES - OPTIMIZED PRODUCT API SERVICE
 * =================================================================
 * N+1 Query Problem Solution for Product API
 * 
 * OPTIMIZATIONS:
 * - Batch enrichment for product data
 * - Single query approach replacing 50+ individual queries
 * - Cache integration with TTL support
 * - Memory efficient processing
 * - Performance metrics tracking
 * =================================================================
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/../../lib/SimpleCache.php';

class OptimizedProductApiService {
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
            'products_processed' => 0,
            'batch_processing_active' => true
        ];
    }
    
    /**
     * Get products for API with filters, sorting, and pagination
     */
    public function getProductsForApi($params = []) {
        $start_time = microtime(true);
        
        // Extract parameters
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 9;
        $sort = $params['sort'] ?? 'created_at-desc';
        $categories = $params['categories'] ?? [];
        $genders = $params['genders'] ?? [];
        
        $offset = ($page - 1) * $limit;
        
        $cache_key = "products_api_" . md5(serialize($params));
        
        // Try cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== null) {
            $this->performance_metrics['cache_hits']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return $cached_result;
        }
        
        $this->performance_metrics['cache_misses']++;
        
        try {
            // Build conditions for filtering
            $conditions = [];
            
            // Apply category filter if provided
            if (!empty($categories)) {
                $category_product_ids = $this->db->select('product_categories',
                    ['category_id' => ['IN', $categories]],
                    'product_id'
                );
                
                $this->performance_metrics['queries_executed']++;
                
                if (!empty($category_product_ids)) {
                    $product_ids = array_column($category_product_ids, 'product_id');
                    $conditions['id'] = ['IN', $product_ids];
                } else {
                    // No products match category filter
                    $result = [
                        'products' => [],
                        'total' => 0,
                        'page' => $page,
                        'limit' => $limit,
                        'pages' => 0
                    ];
                    
                    $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                    return $result;
                }
            }
            
            // Apply gender filter if provided
            if (!empty($genders)) {
                $gender_product_ids = $this->db->select('product_genders',
                    ['gender_id' => ['IN', $genders]],
                    'product_id'
                );
                
                $this->performance_metrics['queries_executed']++;
                
                if (!empty($gender_product_ids)) {
                    $gender_filtered_ids = array_column($gender_product_ids, 'product_id');
                    
                    if (isset($conditions['id'])) {
                        // Intersection of category and gender filters
                        $conditions['id'] = ['IN', array_intersect($conditions['id'][1], $gender_filtered_ids)];
                    } else {
                        $conditions['id'] = ['IN', $gender_filtered_ids];
                    }
                } else {
                    // No products match gender filter
                    $result = [
                        'products' => [],
                        'total' => 0,
                        'page' => $page,
                        'limit' => $limit,
                        'pages' => 0
                    ];
                    
                    $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                    return $result;
                }
            }
            
            // Build sort order
            $sort_parts = explode('-', $sort);
            $sort_field = $sort_parts[0];
            $sort_direction = $sort_parts[1] ?? 'desc';
            
            $order_mapping = [
                'created_at' => 'created_at',
                'price' => 'base_price',
                'name' => 'name'
            ];
            
            $order_field = $order_mapping[$sort_field] ?? 'created_at';
            $order = $order_field . ' ' . strtoupper($sort_direction);
            
            // Get total count first
            $total_count = $this->db->count('product_models', $conditions);
            $this->performance_metrics['queries_executed']++;
            
            // Get products with pagination
            $products = $this->db->select('product_models',
                $conditions,
                '*',
                [
                    'limit' => $limit,
                    'offset' => $offset,
                    'order' => $order
                ]
            );
            
            $this->performance_metrics['queries_executed']++;
            
            if (empty($products)) {
                $result = [
                    'products' => [],
                    'total' => $total_count,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => 0
                ];
                
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return $result;
            }
            
            // Batch enrich products
            $enriched_products = $this->batchEnrichProducts($products);
            
            $total_pages = ceil($total_count / $limit);
            
            $result = [
                'products' => $enriched_products,
                'total' => $total_count,
                'page' => $page,
                'limit' => $limit,
                'pages' => $total_pages
            ];
            
            $this->performance_metrics['products_processed'] = count($enriched_products);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            // Cache for 30 minutes
            $this->cache->set($cache_key, $result, 1800);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("OptimizedProductApiService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [
                'products' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'pages' => 0
            ];
        }
    }
    
    /**
     * Get products for API with optimized batch enrichment (legacy method)
     */
    public function getProductsForApiOptimized($options = []) {
        $start_time = microtime(true);
        
        // Default options
        $limit = $options['limit'] ?? 10;
        $page = $options['page'] ?? 1;
        $offset = ($page - 1) * $limit;
        
        $cache_key = "products_api_page_{$page}_limit_{$limit}";
        
        // Try cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== null) {
            $this->performance_metrics['cache_hits']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return $cached_result;
        }
        
        $this->performance_metrics['cache_misses']++;
        
        try {
            // Get products using REST API with pagination
            $products = $this->db->select('product_models',
                [],
                '*',
                [
                    'limit' => $limit,
                    'offset' => $offset,
                    'order' => 'created_at DESC'
                ]
            );
            
            $this->performance_metrics['queries_executed']++;
            
            if (empty($products)) {
                $result = [
                    'products' => [],
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                    'has_more' => false
                ];
                
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return $result;
            }
            
            // Batch enrich products
            $enriched_products = $this->batchEnrichProducts($products);
            
            // Get total count for pagination
            $total_count = $this->db->count('product_models', []);
            $this->performance_metrics['queries_executed']++;
            
            $result = [
                'products' => $enriched_products,
                'total' => $total_count,
                'page' => $page,
                'limit' => $limit,
                'has_more' => ($offset + $limit) < $total_count
            ];
            
            $this->performance_metrics['products_processed'] = count($enriched_products);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            // Cache for 30 minutes
            $this->cache->set($cache_key, $result, 1800);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("OptimizedProductApiService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [
                'products' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'has_more' => false
            ];
        }
    }
    
    /**
     * Batch enrich products with categories, genders, and images
     */
    private function batchEnrichProducts($products) {
        if (empty($products)) {
            return [];
        }
        
        $product_ids = array_column($products, 'id');
        
        // Get all categories for products in batch
        $product_categories = $this->db->select('product_categories', 
            ['product_id' => ['IN', $product_ids]], 
            'product_id, category_id'
        );
        
        $this->performance_metrics['queries_executed']++;
        
        // Get category details if we have categories
        $categories = [];
        if (!empty($product_categories)) {
            $category_ids = array_unique(array_column($product_categories, 'category_id'));
            $categories = $this->db->select('categories', 
                ['id' => ['IN', $category_ids]], 
                'id, name, slug'
            );
            $this->performance_metrics['queries_executed']++;
        }
        
        // Get all genders for products in batch
        $product_genders = $this->db->select('product_genders', 
            ['product_id' => ['IN', $product_ids]], 
            'product_id, gender_id'
        );
        
        $this->performance_metrics['queries_executed']++;
        
        // Get gender details if we have genders
        $genders = [];
        if (!empty($product_genders)) {
            $gender_ids = array_unique(array_column($product_genders, 'gender_id'));
            $genders = $this->db->select('genders', 
                ['id' => ['IN', $gender_ids]], 
                'id, name, slug'
            );
            $this->performance_metrics['queries_executed']++;
        }
        
        // Get all images for products in batch
        $product_images = $this->db->select('product_images', 
            ['model_id' => ['IN', $product_ids]], 
            '*', 
            ['order' => 'is_primary DESC, sort_order ASC']
        );
        
        $this->performance_metrics['queries_executed']++;
        
        // Create lookup arrays for efficient mapping
        $categories_lookup = [];
        foreach ($categories as $category) {
            $categories_lookup[$category['id']] = $category;
        }
        
        $genders_lookup = [];
        foreach ($genders as $gender) {
            $genders_lookup[$gender['id']] = $gender;
        }
        
        $product_categories_lookup = [];
        foreach ($product_categories as $pc) {
            $product_categories_lookup[$pc['product_id']][] = $pc['category_id'];
        }
        
        $product_genders_lookup = [];
        foreach ($product_genders as $pg) {
            $product_genders_lookup[$pg['product_id']][] = $pg['gender_id'];
        }
        
        $product_images_lookup = [];
        foreach ($product_images as $image) {
            $product_images_lookup[$image['model_id']][] = $image;
        }
        
        // Enrich each product
        $enriched_products = [];
        foreach ($products as $product) {
            $product_id = $product['id'];
            
            // Add categories
            $product['categories'] = [];
            $product['category_name'] = ''; // For frontend compatibility
            if (isset($product_categories_lookup[$product_id])) {
                foreach ($product_categories_lookup[$product_id] as $category_id) {
                    if (isset($categories_lookup[$category_id])) {
                        $product['categories'][] = $categories_lookup[$category_id];
                    }
                }
                // Set first category as primary category name
                if (!empty($product['categories'])) {
                    $product['category_name'] = $product['categories'][0]['name'];
                }
            }
            
            // Add genders
            $product['genders'] = [];
            if (isset($product_genders_lookup[$product_id])) {
                foreach ($product_genders_lookup[$product_id] as $gender_id) {
                    if (isset($genders_lookup[$gender_id])) {
                        $product['genders'][] = $genders_lookup[$gender_id];
                    }
                }
            }
            
            // Add images
            $product['images'] = $product_images_lookup[$product_id] ?? [];
            
            // Add primary image for quick access
            $product['primary_image'] = null;
            $product['image_url'] = 'assets/images/placeholder.svg'; // Default image
            
            foreach ($product['images'] as $image) {
                if ($image['is_primary']) {
                    $product['primary_image'] = $image;
                    $product['image_url'] = $image['image_url'] ?? 'assets/images/placeholder.svg';
                    break;
                }
            }
            
            // If no primary image, use first image
            if (!$product['primary_image'] && !empty($product['images'])) {
                $product['primary_image'] = $product['images'][0];
                $product['image_url'] = $product['images'][0]['image_url'] ?? 'assets/images/placeholder.svg';
            }
            
            $enriched_products[] = $product;
        }
        
        return $enriched_products;
    }
    
    /**
     * Get popular products (featured products)
     */
    public function getPopularProductsOptimized($limit = 5) {
        $start_time = microtime(true);
        $cache_key = "popular_products_limit_{$limit}";
        
        // Try cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== null) {
            $this->performance_metrics['cache_hits']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return $cached_result;
        }
        
        $this->performance_metrics['cache_misses']++;
        
        try {
            // Get featured products using REST API
            $products = $this->db->select('product_models',
                ['is_featured' => true],
                '*',
                [
                    'limit' => $limit,
                    'order' => 'created_at DESC'
                ]
            );
            
            $this->performance_metrics['queries_executed']++;
            
            if (empty($products)) {
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return [];
            }
            
            // Batch enrich products
            $enriched_products = $this->batchEnrichProducts($products);
            
            $this->performance_metrics['products_processed'] = count($enriched_products);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            // Cache for 1 hour
            $this->cache->set($cache_key, $enriched_products, 3600);
            
            return $enriched_products;
            
        } catch (Exception $e) {
            error_log("OptimizedProductApiService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }
    
    /**
     * Get similar products based on categories
     */
    public function getSimilarProductsOptimized($product_id, $limit = 4) {
        $start_time = microtime(true);
        $cache_key = "similar_products_{$product_id}_limit_{$limit}";
        
        // Try cache first
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== null) {
            $this->performance_metrics['cache_hits']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return $cached_result;
        }
        
        $this->performance_metrics['cache_misses']++;
        
        try {
            // Get categories of the original product
            $product_categories = $this->db->select('product_categories', 
                ['product_id' => $product_id], 
                'category_id'
            );
            
            $this->performance_metrics['queries_executed']++;
            
            if (empty($product_categories)) {
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return [];
            }
            
            $category_ids = array_column($product_categories, 'category_id');
            
            // Get other products in same categories
            $similar_product_categories = $this->db->select('product_categories', 
                ['category_id' => ['IN', $category_ids]], 
                'product_id'
            );
            
            $this->performance_metrics['queries_executed']++;
            
            $similar_product_ids = array_unique(array_column($similar_product_categories, 'product_id'));
            
            // Remove original product from similar products
            $similar_product_ids = array_filter($similar_product_ids, function($id) use ($product_id) {
                return $id != $product_id;
            });
            
            if (empty($similar_product_ids)) {
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return [];
            }
            
            // Get similar products
            $similar_products = $this->db->select('product_models',
                ['id' => ['IN', $similar_product_ids]],
                '*',
                [
                    'limit' => $limit,
                    'order' => 'created_at DESC'
                ]
            );
            
            $this->performance_metrics['queries_executed']++;
            
            if (empty($similar_products)) {
                $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
                return [];
            }
            
            // Batch enrich products
            $enriched_products = $this->batchEnrichProducts($similar_products);
            
            $this->performance_metrics['products_processed'] = count($enriched_products);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            // Cache for 2 hours
            $this->cache->set($cache_key, $enriched_products, 7200);
            
            return $enriched_products;
            
        } catch (Exception $e) {
            error_log("OptimizedProductApiService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
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
     * Clear product cache
     */
    public function clearCache() {
        $this->cache->deleteByPattern('products_api_*');
        $this->cache->deleteByPattern('popular_products_*');
        $this->cache->deleteByPattern('similar_products_*');
    }
}

/**
 * Global function for service access
 */
function optimized_product_api_service() {
    static $instance = null;
    if ($instance === null) {
        $instance = new OptimizedProductApiService();
    }
    return $instance;
}