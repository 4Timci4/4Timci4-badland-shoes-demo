<?php
/**
 * =================================================================
 * BANDLAND SHOES - PRODUCT API SERVICE
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

class ProductApiService {
    private $db;
    private $performance_metrics;
    
    public function __construct() {
        $this->db = database();
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
     * Get products for API using the new 'product_api_summary' materialized view.
     * This single query handles filtering, sorting, and pagination efficiently.
     */
    public function getProductsForApi($params = []) {
        $start_time = microtime(true);

        // Extract parameters
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 9;
        $sort = $params['sort'] ?? 'created_at-desc';
        $categories = $params['categories'] ?? []; // Expects category slugs
        $genders = $params['genders'] ?? []; // Expects gender slugs
        $featured = $params['featured'] ?? null;
        
        $offset = ($page - 1) * $limit;
        
        try {
            // Build conditions for the materialized view
            $conditions = [];
            if (!empty($categories)) {
                // Use the '&&' (overlap) operator for OR logic
                $conditions['category_slugs'] = ['&&', '{' . implode(',', $categories) . '}'];
            }
            if (!empty($genders)) {
                 // Use the '&&' (overlap) operator for OR logic
                $conditions['gender_slugs'] = ['&&', '{' . implode(',', $genders) . '}'];
            }
            if ($featured !== null) {
                $conditions['is_featured'] = (bool)$featured;
            }

            // Build sort order
            $sort_parts = explode('-', $sort);
            $order_field = $sort_parts[0] === 'price' ? 'base_price' : 'created_at';
            $order_direction = $sort_parts[1] ?? 'desc';
            $order = $order_field . ' ' . strtoupper($order_direction);
            
            // Get total count with filters applied
            $total_count = $this->db->count('product_api_summary', $conditions);
            $this->performance_metrics['queries_executed']++;

            // Get products from the materialized view
            $products = $this->db->select(
                'product_api_summary',
                $conditions,
                '*',
                ['limit' => $limit, 'offset' => $offset, 'order' => $order]
            );
            $this->performance_metrics['queries_executed']++;

            // The data is already enriched by the view, no need for batchEnrichProducts
            $total_pages = $limit > 0 ? ceil($total_count / $limit) : 0;
            
            $result = [
                'products' => $products,
                'total' => $total_count,
                'page' => $page,
                'limit' => $limit,
                'pages' => $total_pages
            ];
            
            $this->performance_metrics['products_processed'] = count($products);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            
            return $result;

        } catch (Exception $e) {
            error_log("ProductApiService Error (Materialized View): " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return ['products' => [], 'total' => 0, 'page' => $page, 'limit' => $limit, 'pages' => 0];
        }
    }
    /**
     * Get similar products using the 'get_similar_products_secure' RPC function.
     * This is the modern, secure, and efficient way to fetch related items.
     */
    public function getSimilarProducts($product_id, $limit = 5) {
        $start_time = microtime(true);

        try {
            // 1. Get details of the current product from the summary view.
            $current_product_data = $this->db->select(
                'product_api_summary',
                ['id' => ['eq', $product_id]],
                'name, category_slugs, gender_slugs'
            );
            $this->performance_metrics['queries_executed']++;

            if (empty($current_product_data)) {
                error_log("ProductApiService Error: Could not find product with ID {$product_id} for similar products.");
                return [];
            }

            $product_details = $current_product_data[0];

            // 2. Prepare parameters for the RPC call.
            // The SupabaseAdapter will correctly format the PHP arrays into PostgreSQL array literals (e.g., '{slug1,slug2}').
            $params = [
                'p_product_id' => $product_id,
                'p_product_name' => $product_details['name'],
                'p_category_slugs' => $product_details['category_slugs'] ?? [],
                'p_gender_slugs' => $product_details['gender_slugs'] ?? [],
                'p_limit' => $limit,
            ];

            // 3. Call the secure RPC function.
            $similar_products = $this->db->rpc('get_similar_products_secure', $params);
            $this->performance_metrics['queries_executed']++;
            
            $this->performance_metrics['products_processed'] = count($similar_products);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

            return $similar_products;

        } catch (Exception $e) {
            // Log the detailed error message for easier debugging.
            error_log("ProductApiService Error (getSimilarProducts RPC): " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }
    
    // Note: The following methods are now obsolete due to the new getProductsForApi
    // method that uses the 'product_api_summary' materialized view.
    // They are removed for code clarity and to prevent legacy code usage.
    // - getProductsForApiOptimized
    // - batchEnrichProducts
    // - getPopularProductsOptimized
    // - getSimilarProductsOptimized
    
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
function product_api_service() {
    static $instance = null;
    if ($instance === null) {
        $instance = new ProductApiService();
    }
    return $instance;
}