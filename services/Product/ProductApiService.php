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
     * Get products for API - compatible with both Supabase and MariaDB.
     * Uses different approaches based on database type.
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
            // Check database type
            $dbType = DatabaseFactory::getCurrentType();
            
            if ($dbType === 'supabase') {
                return $this->getProductsForApiSupabase($params, $start_time);
            } else {
                return $this->getProductsForApiMariaDB($params, $start_time);
            }

        } catch (Exception $e) {
            error_log("ProductApiService Error: " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return ['products' => [], 'total' => 0, 'page' => $page, 'limit' => $limit, 'pages' => 0];
        }
    }
    
    /**
     * Supabase-specific implementation using array overlap operations
     */
    private function getProductsForApiSupabase($params, $start_time) {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 9;
        $sort = $params['sort'] ?? 'created_at-desc';
        $categories = $params['categories'] ?? [];
        $genders = $params['genders'] ?? [];
        $featured = $params['featured'] ?? null;
        
        $offset = ($page - 1) * $limit;
        
        // Build conditions for Supabase
        $conditions = [];
        
        if (!empty($categories)) {
            // Use array overlap for categories
            $conditions['category_slugs'] = ['&&', $categories];
        }
        
        if (!empty($genders)) {
            // Use array overlap for genders
            $conditions['gender_slugs'] = ['&&', $genders];
        }
        
        if ($featured !== null) {
            $conditions['is_featured'] = (bool)$featured;
        }

        // Build sort order
        $sort_parts = explode('-', $sort);
        $order_field = $sort_parts[0] === 'price' ? 'base_price' : 'created_at';
        $order_direction = $sort_parts[1] ?? 'desc';
        $order = $order_field . ' ' . strtoupper($order_direction);
        
        // Get total count
        $total_count = $this->db->count('product_api_summary', $conditions);
        $this->performance_metrics['queries_executed']++;

        // Get products
        $products = $this->db->select(
            'product_api_summary',
            $conditions,
            '*',
            ['limit' => $limit, 'offset' => $offset, 'order' => $order]
        );
        $this->performance_metrics['queries_executed']++;

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
    }
    
    /**
     * MariaDB-specific implementation using JOINs
     */
    private function getProductsForApiMariaDB($params, $start_time) {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 9;
        $sort = $params['sort'] ?? 'created_at-desc';
        $categories = $params['categories'] ?? [];
        $genders = $params['genders'] ?? [];
        $featured = $params['featured'] ?? null;
        
        $offset = ($page - 1) * $limit;
        
        // Build WHERE conditions for JOINs
        $whereConditions = [];
        $whereParams = [];

        if (!empty($categories)) {
            $categoryPlaceholders = implode(',', array_fill(0, count($categories), '?'));
            $whereConditions[] = "c.slug IN ($categoryPlaceholders)";
            $whereParams = array_merge($whereParams, $categories);
        }
        
        if (!empty($genders)) {
            $genderPlaceholders = implode(',', array_fill(0, count($genders), '?'));
            $whereConditions[] = "g.slug IN ($genderPlaceholders)";
            $whereParams = array_merge($whereParams, $genders);
        }
        
        if ($featured !== null) {
            $whereConditions[] = "p.is_featured = ?";
            $whereParams[] = (bool)$featured;
        }

        // Build sort order
        $sort_parts = explode('-', $sort);
        $order_field = $sort_parts[0] === 'price' ? 'p.base_price' : 'p.created_at';
        $order_direction = $sort_parts[1] ?? 'desc';
        $order = $order_field . ' ' . strtoupper($order_direction);
        
        // Get total count with JOINs
        $countQuery = "SELECT COUNT(DISTINCT p.id) as total FROM product_api_summary p
                      LEFT JOIN product_categories pc ON p.id = pc.product_id
                      LEFT JOIN categories c ON pc.category_id = c.id
                      LEFT JOIN product_genders pg ON p.id = pg.product_id
                      LEFT JOIN genders g ON pg.gender_id = g.id";
        
        if (!empty($whereConditions)) {
            $countQuery .= " WHERE (" . implode(' OR ', $whereConditions) . ")";
        }
        
        $countResult = $this->db->executeRawSql($countQuery, $whereParams);
        $total_count = $countResult[0]['total'] ?? 0;
        $this->performance_metrics['queries_executed']++;

        // Get products with JOINs
        $selectQuery = "SELECT DISTINCT p.*
                       FROM product_api_summary p
                       LEFT JOIN product_categories pc ON p.id = pc.product_id
                       LEFT JOIN categories c ON pc.category_id = c.id
                       LEFT JOIN product_genders pg ON p.id = pg.product_id
                       LEFT JOIN genders g ON pg.gender_id = g.id";
        
        if (!empty($whereConditions)) {
            $selectQuery .= " WHERE (" . implode(' OR ', $whereConditions) . ")";
        }
        
        $selectQuery .= " ORDER BY $order LIMIT $limit OFFSET $offset";
        
        $products = $this->db->executeRawSql($selectQuery, $whereParams);
        $this->performance_metrics['queries_executed']++;

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
    }
    /**
     * Get similar products - compatible with both Supabase and MariaDB.
     * Uses different approaches based on database type.
     */
    public function getSimilarProducts($product_id, $limit = 5) {
        $start_time = microtime(true);

        try {
            // Check database type
            $dbType = DatabaseFactory::getCurrentType();
            
            if ($dbType === 'supabase') {
                return $this->getSimilarProductsSupabase($product_id, $limit, $start_time);
            } else {
                return $this->getSimilarProductsMariaDB($product_id, $limit, $start_time);
            }

        } catch (Exception $e) {
            error_log("ProductApiService Error (getSimilarProducts): " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }
    
    /**
     * Supabase-specific implementation for similar products
     */
    private function getSimilarProductsSupabase($product_id, $limit, $start_time) {
        // Get current product details
        $current_product_data = $this->db->select(
            'product_api_summary',
            ['id' => $product_id],
            'name, category_slugs, gender_slugs'
        );
        $this->performance_metrics['queries_executed']++;

        if (empty($current_product_data)) {
            error_log("ProductApiService Error: Could not find product with ID {$product_id} for similar products.");
            return [];
        }

        $product_details = $current_product_data[0];

        // Build conditions for similar products
        $conditions = [];
        
        // Use array overlap for categories
        if (!empty($product_details['category_slugs'])) {
            $conditions['category_slugs'] = ['&&', $product_details['category_slugs']];
        }
        
        // Exclude current product
        $conditions['id'] = ['!=', $product_id];

        // Get similar products
        $similar_products = $this->db->select(
            'product_api_summary',
            $conditions,
            '*',
            ['limit' => $limit, 'order' => 'created_at DESC']
        );
        $this->performance_metrics['queries_executed']++;
        
        $this->performance_metrics['products_processed'] = count($similar_products);
        $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

        return $similar_products;
    }
    
    /**
     * MariaDB-specific implementation for similar products using JOINs
     */
    private function getSimilarProductsMariaDB($product_id, $limit, $start_time) {
        // Get current product details with JOINs
        $currentProductQuery = "SELECT p.*, c.slug as category_slug, g.slug as gender_slug
                               FROM product_api_summary p
                               LEFT JOIN product_categories pc ON p.id = pc.product_id
                               LEFT JOIN categories c ON pc.category_id = c.id
                               LEFT JOIN product_genders pg ON p.id = pg.product_id
                               LEFT JOIN genders g ON pg.gender_id = g.id
                               WHERE p.id = :product_id";
        
        $current_product_data = $this->db->executeRawSql($currentProductQuery, ['product_id' => $product_id]);
        $this->performance_metrics['queries_executed']++;

        if (empty($current_product_data)) {
            error_log("ProductApiService Error: Could not find product with ID {$product_id} for similar products.");
            return [];
        }

        $product_details = $current_product_data[0];

        // Build similar products query with JOINs
        $similarQuery = "SELECT DISTINCT p.*, c.name as category_name, c.slug as category_slug,
                        g.name as gender_name, g.slug as gender_slug
                        FROM product_api_summary p
                        LEFT JOIN product_categories pc ON p.id = pc.product_id
                        LEFT JOIN categories c ON pc.category_id = c.id
                        LEFT JOIN product_genders pg ON p.id = pg.product_id
                        LEFT JOIN genders g ON pg.gender_id = g.id
                        WHERE p.id != :product_id";
        
        $params = ['product_id' => $product_id];
        
        // Add category condition if available
        if (!empty($product_details['category_slug'])) {
            $similarQuery .= " AND c.slug = :category_slug";
            $params['category_slug'] = $product_details['category_slug'];
        }
        
        $similarQuery .= " ORDER BY p.created_at DESC LIMIT :limit";
        $params['limit'] = $limit;
        
        $similar_products = $this->db->executeRawSql($similarQuery, $params);
        $this->performance_metrics['queries_executed']++;
        
        $this->performance_metrics['products_processed'] = count($similar_products);
        $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

        return $similar_products;
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