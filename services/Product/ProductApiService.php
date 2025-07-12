<?php

require_once __DIR__ . '/../../lib/DatabaseFactory.php';

class ProductApiService
{
    private $db;
    private $performance_metrics;

    public function __construct()
    {
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


    public function getProductsForApi($params = [])
    {
        $start_time = microtime(true);


        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 9;
        $sort = $params['sort'] ?? 'created_at-desc';
        $categories = $params['categories'] ?? [];
        $genders = $params['genders'] ?? [];
        $featured = $params['featured'] ?? null;

        $offset = ($page - 1) * $limit;

        try {

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


    private function getProductsForApiSupabase($params, $start_time)
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 9;
        $sort = $params['sort'] ?? 'created_at-desc';
        $categories = $params['categories'] ?? [];
        $genders = $params['genders'] ?? [];
        $featured = $params['featured'] ?? null;

        $offset = ($page - 1) * $limit;


        $conditions = [];

        if (!empty($categories)) {

            $conditions['category_slugs'] = ['&&', $categories];
        }

        if (!empty($genders)) {

            $conditions['gender_slugs'] = ['&&', $genders];
        }

        if ($featured !== null) {
            $conditions['is_featured'] = (bool) $featured;
        }


        $sort_parts = explode('-', $sort);
        $sort_field = $sort_parts[0] ?? 'created_at';
        $order_direction = $sort_parts[1] ?? 'desc';

        $order_field_map = [
            'name' => 'name',
            'price' => 'price',
            'created_at' => 'created_at'
        ];

        $order_field = $order_field_map[$sort_field] ?? 'created_at';

        $order = $order_field . ' ' . strtoupper($order_direction);


        $total_count = $this->db->count('product_api_summary', $conditions);
        $this->performance_metrics['queries_executed']++;


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


    private function getProductsForApiMariaDB($params, $start_time)
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 9;
        $sort = $params['sort'] ?? 'created_at-desc';
        $categories = $params['categories'] ?? [];
        $genders = $params['genders'] ?? [];
        $featured = $params['featured'] ?? null;

        $offset = ($page - 1) * $limit;


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
            $whereParams[] = (bool) $featured;
        }


        $sort_parts = explode('-', $sort);
        $sort_field = $sort_parts[0];
        $order_direction = $sort_parts[1] ?? 'desc';

        switch ($sort_field) {
            case 'name':
                $order_field = 'p.name';
                break;
            case 'price':
                $order_field = 'p.price';
                break;
            default:
                $order_field = 'p.created_at';
        }

        $order = $order_field . ' ' . strtoupper($order_direction);


        $countQuery = "SELECT COUNT(DISTINCT p.id) as total FROM product_api_summary p
                      LEFT JOIN product_categories pc ON p.id = pc.product_id
                      LEFT JOIN categories c ON pc.category_id = c.id
                      LEFT JOIN product_genders pg ON p.id = pg.product_id
                      LEFT JOIN genders g ON pg.gender_id = g.id";

        if (!empty($whereConditions)) {
            $countQuery .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $countResult = $this->db->executeRawSql($countQuery, $whereParams);
        $total_count = $countResult[0]['total'] ?? 0;
        $this->performance_metrics['queries_executed']++;


        $selectQuery = "SELECT p.*,
                            GROUP_CONCAT(DISTINCT c.name SEPARATOR '||') as category_names,
                            GROUP_CONCAT(DISTINCT g.name SEPARATOR '||') as gender_names
                       FROM product_api_summary p
                       LEFT JOIN product_categories pc ON p.id = pc.product_id
                       LEFT JOIN categories c ON pc.category_id = c.id
                       LEFT JOIN product_genders pg ON p.id = pg.product_id
                       LEFT JOIN genders g ON pg.gender_id = g.id";

        if (!empty($whereConditions)) {
            $selectQuery .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $selectQuery .= " GROUP BY p.id ORDER BY $order LIMIT $limit OFFSET $offset";

        $products = $this->db->executeRawSql($selectQuery, $whereParams);
        $this->performance_metrics['queries_executed']++;


        foreach ($products as &$product) {
            $product['category_names'] = !empty($product['category_names']) ? explode('||', $product['category_names']) : [];
            $product['gender_names'] = !empty($product['gender_names']) ? explode('||', $product['gender_names']) : [];
        }

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

    public function getSimilarProducts($product_id, $limit = 5)
    {
        $start_time = microtime(true);

        try {

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


    private function getSimilarProductsSupabase($product_id, $limit, $start_time)
    {

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


        $conditions = [];


        if (!empty($product_details['category_slugs'])) {
            $conditions['category_slugs'] = ['&&', $product_details['category_slugs']];
        }


        $conditions['id'] = ['!=', $product_id];


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


    private function getSimilarProductsMariaDB($product_id, $limit, $start_time)
    {

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


        $similarQuery = "SELECT DISTINCT p.*, c.name as category_name, c.slug as category_slug,
                        g.name as gender_name, g.slug as gender_slug
                        FROM product_api_summary p
                        LEFT JOIN product_categories pc ON p.id = pc.product_id
                        LEFT JOIN categories c ON pc.category_id = c.id
                        LEFT JOIN product_genders pg ON p.id = pg.product_id
                        LEFT JOIN genders g ON pg.gender_id = g.id
                        WHERE p.id != :product_id";


        error_log("Similar products query: " . $similarQuery);

        $params = ['product_id' => $product_id];



        $similarQuery .= " ORDER BY p.id DESC LIMIT :limit";
        $params['limit'] = $limit;

        $similar_products = $this->db->executeRawSql($similarQuery, $params);
        $this->performance_metrics['queries_executed']++;

        $this->performance_metrics['products_processed'] = count($similar_products);
        $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

        return $similar_products;
    }










    public function getPerformanceMetrics()
    {
        return $this->performance_metrics;
    }


    public function clearCache()
    {


        if (method_exists($this->db, 'clearCache')) {
            $this->db->clearCache();
        }
    }
}


function product_api_service()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new ProductApiService();
    }
    return $instance;
}