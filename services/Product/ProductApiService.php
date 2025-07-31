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
        if (!$this->db) {
            // Demo ürünler döndür
            return $this->getDemoProducts($params);
        }
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

    public function getSimilarProducts($product_id, $limit = 5): array
    {
        if (!$this->db) {
            // Database bağlantısı yoksa demo similar products döndür
            return $this->getDemoSimilarProducts($product_id, $limit);
        }
        
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

    /**
     * Demo ürünleri döndürür
     */
    private function getDemoProducts($params = [])
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 9;
        
        $demoProducts = [
            [
                'id' => 1,
                'name' => 'Nike Air Max 270',
                'slug' => 'nike-air-max-270',
                'description' => 'Rahat ve stilin bir arada. Nike Air Max 270 ile her adımda konfor yaşayın.',
                'price' => 899.99,
                'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop&crop=center',
                'is_featured' => true,
                'created_at' => '2024-01-15 10:00:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Erkek', 'Kadın'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['erkek', 'kadin']
            ],
            [
                'id' => 2,
                'name' => 'Adidas Ultraboost 22',
                'slug' => 'adidas-ultraboost-22',
                'description' => 'Yenilikçi teknoloji ile maksimum performans. Adidas Ultraboost 22.',
                'price' => 1299.99,
                'image_url' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop&crop=center',
                'is_featured' => true,
                'created_at' => '2024-01-14 09:30:00',
                'category_names' => ['Koşu Ayakkabısı'],
                'gender_names' => ['Erkek'],
                'category_slugs' => ['kosu-ayakkabisi'],
                'gender_slugs' => ['erkek']
            ],
            [
                'id' => 3,
                'name' => 'Converse Chuck Taylor All Star',
                'slug' => 'converse-chuck-taylor-all-star',
                'description' => 'Klasik stil hiç eskimez. Converse Chuck Taylor All Star.',
                'price' => 449.99,
                'image_url' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=400&h=400&fit=crop&crop=center',
                'is_featured' => false,
                'created_at' => '2024-01-13 14:15:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Unisex'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['unisex']
            ],
            [
                'id' => 4,
                'name' => 'Vans Old Skool',
                'slug' => 'vans-old-skool',
                'description' => 'Sokak stilinin vazgeçilmezi. Vans Old Skool ile farkını ortaya koy.',
                'price' => 549.99,
                'image_url' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop&crop=center',
                'is_featured' => false,
                'created_at' => '2024-01-12 11:20:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Kadın', 'Erkek'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['kadin', 'erkek']
            ],
            [
                'id' => 5,
                'name' => 'New Balance 990v5',
                'slug' => 'new-balance-990v5',
                'description' => 'Premium kalite ve üstün konfor. New Balance 990v5.',
                'price' => 1599.99,
                'image_url' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=400&fit=crop&crop=center',
                'is_featured' => true,
                'created_at' => '2024-01-11 16:45:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Erkek'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['erkek']
            ],
            [
                'id' => 6,
                'name' => 'Puma RS-X³',
                'slug' => 'puma-rs-x3',
                'description' => 'Geleceğin tasarımı bugünden. Puma RS-X³ ile öne çık.',
                'price' => 799.99,
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&crop=center',
                'is_featured' => false,
                'created_at' => '2024-01-10 13:30:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Kadın'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['kadin']
            ],
            [
                'id' => 7,
                'name' => 'Jordan 1 Retro High',
                'slug' => 'jordan-1-retro-high',
                'description' => 'Efsanevi tasarım, zamansız stil. Jordan 1 Retro High.',
                'price' => 1899.99,
                'image_url' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop&crop=center',
                'is_featured' => true,
                'created_at' => '2024-01-09 10:15:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Erkek', 'Kadın'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['erkek', 'kadin']
            ],
            [
                'id' => 8,
                'name' => 'Reebok Classic Leather',
                'slug' => 'reebok-classic-leather',
                'description' => 'Sade ve şık. Reebok Classic Leather ile klasik tarzını tamamla.',
                'price' => 399.99,
                'image_url' => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=400&fit=crop&crop=center',
                'is_featured' => false,
                'created_at' => '2024-01-08 15:20:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Unisex'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['unisex']
            ],
            [
                'id' => 9,
                'name' => 'ASICS Gel-Kayano 29',
                'slug' => 'asics-gel-kayano-29',
                'description' => 'Koşu tutkunları için mükemmel destek. ASICS Gel-Kayano 29.',
                'price' => 1399.99,
                'image_url' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop&crop=center',
                'is_featured' => false,
                'created_at' => '2024-01-07 12:00:00',
                'category_names' => ['Koşu Ayakkabısı'],
                'gender_names' => ['Erkek', 'Kadın'],
                'category_slugs' => ['kosu-ayakkabisi'],
                'gender_slugs' => ['erkek', 'kadin']
            ],
            [
                'id' => 10,
                'name' => 'Under Armour HOVR Phantom 2',
                'slug' => 'under-armour-hovr-phantom-2',
                'description' => 'Teknoloji ve spor bir arada. Under Armour HOVR Phantom 2.',
                'price' => 999.99,
                'image_url' => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=400&fit=crop&crop=center',
                'is_featured' => false,
                'created_at' => '2024-01-06 09:45:00',
                'category_names' => ['Koşu Ayakkabısı'],
                'gender_names' => ['Erkek'],
                'category_slugs' => ['kosu-ayakkabisi'],
                'gender_slugs' => ['erkek']
            ],
            [
                'id' => 11,
                'name' => 'Skechers D\'Lites',
                'slug' => 'skechers-dlites',
                'description' => 'Retro tarz ve günlük konfor. Skechers D\'Lites ile her yerde rahat ol.',
                'price' => 649.99,
                'image_url' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?w=400&h=400&fit=crop&crop=center',
                'is_featured' => false,
                'created_at' => '2024-01-05 14:30:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Kadın'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['kadin']
            ],
            [
                'id' => 12,
                'name' => 'Fila Disruptor 2',
                'slug' => 'fila-disruptor-2',
                'description' => 'Chunky sneaker trendin öncüsü. Fila Disruptor 2.',
                'price' => 579.99,
                'image_url' => 'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=400&h=400&fit=crop&crop=center',
                'primary_image' => 'https://images.unsplash.com/photo-1600269452121-4f2416e55c28?w=400&h=400&fit=crop&crop=center',
                'is_featured' => false,
                'created_at' => '2024-01-04 11:15:00',
                'category_names' => ['Sneaker'],
                'gender_names' => ['Kadın', 'Erkek'],
                'category_slugs' => ['sneaker'],
                'gender_slugs' => ['kadin', 'erkek']
            ]
        ];

        // Filtreleme uygula
        $filteredProducts = $demoProducts;
        
        // Kategori filtresi
        if (!empty($params['categories'])) {
            $filteredProducts = array_filter($filteredProducts, function($product) use ($params) {
                return !empty(array_intersect($product['category_slugs'], $params['categories']));
            });
        }
        
        // Cinsiyet filtresi
        if (!empty($params['genders'])) {
            $filteredProducts = array_filter($filteredProducts, function($product) use ($params) {
                return !empty(array_intersect($product['gender_slugs'], $params['genders']));
            });
        }
        
        // Öne çıkan filtresi
        if ($params['featured'] !== null) {
            $filteredProducts = array_filter($filteredProducts, function($product) use ($params) {
                return $product['is_featured'] == (bool)$params['featured'];
            });
        }

        // Sıralama
        $sort = $params['sort'] ?? 'created_at-desc';
        $sort_parts = explode('-', $sort);
        $sort_field = $sort_parts[0] ?? 'created_at';
        $order_direction = $sort_parts[1] ?? 'desc';
        
        usort($filteredProducts, function($a, $b) use ($sort_field, $order_direction) {
            $valueA = $a[$sort_field] ?? 0;
            $valueB = $b[$sort_field] ?? 0;
            
            if ($sort_field === 'name') {
                $comparison = strcmp($valueA, $valueB);
            } else {
                $comparison = $valueA <=> $valueB;
            }
            
            return $order_direction === 'desc' ? -$comparison : $comparison;
        });

        $total = count($filteredProducts);
        $totalPages = $limit > 0 ? ceil($total / $limit) : 0;
        
        // Sayfalama
        $offset = ($page - 1) * $limit;
        $paginatedProducts = array_slice($filteredProducts, $offset, $limit);

        return [
            'products' => $paginatedProducts,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => $totalPages
        ];
    }


    public function clearCache()
    {


        if (method_exists($this->db, 'clearCache')) {
            $this->db->clearCache();
        }
    }

    /**
     * Filtrelenmiş ürünlere göre cinsiyet ve kategori sayılarını hesaplar
     *
     * @param array $params Filtre parametreleri
     * @return array Cinsiyet ve kategori sayıları
     */
    public function getFilterCounts($params = [])
    {
        if (!$this->db) {
            // Database bağlantısı yoksa demo filter counts döndür
            return $this->getDemoFilterCounts($params);
        }
        
        $start_time = microtime(true);

        $categories = $params['categories'] ?? [];
        $genders = $params['genders'] ?? [];
        $featured = $params['featured'] ?? null;

        try {
            $dbType = DatabaseFactory::getCurrentType();

            if ($dbType === 'supabase') {
                return $this->getFilterCountsSupabase($params, $start_time);
            } else {
                return $this->getFilterCountsMariaDB($params, $start_time);
            }

        } catch (Exception $e) {
            error_log("ProductApiService Error (getFilterCounts): " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return ['gender_counts' => [], 'category_counts' => []];
        }
    }

    /**
     * MariaDB için filtrelenmiş ürünlere göre cinsiyet ve kategori sayılarını hesaplar
     */
    private function getFilterCountsMariaDB($params, $start_time)
    {
        $categories = $params['categories'] ?? [];
        $genders = $params['genders'] ?? [];
        $featured = $params['featured'] ?? null;

        // Temel sorgu
        $baseQuery = "FROM product_api_summary p
                     LEFT JOIN product_categories pc ON p.id = pc.product_id
                     LEFT JOIN categories c ON pc.category_id = c.id
                     LEFT JOIN product_genders pg ON p.id = pg.product_id
                     LEFT JOIN genders g ON pg.gender_id = g.id";

        $whereConditions = [];
        $whereParams = [];

        // Kategori filtresi
        if (!empty($categories)) {
            $categoryPlaceholders = implode(',', array_fill(0, count($categories), '?'));
            $whereConditions[] = "c.slug IN ($categoryPlaceholders)";
            $whereParams = array_merge($whereParams, $categories);
        }

        // Cinsiyet filtresi
        if (!empty($genders)) {
            $genderPlaceholders = implode(',', array_fill(0, count($genders), '?'));
            $whereConditions[] = "g.slug IN ($genderPlaceholders)";
            $whereParams = array_merge($whereParams, $genders);
        }

        // Öne çıkan filtresi
        if ($featured !== null) {
            $whereConditions[] = "p.is_featured = ?";
            $whereParams[] = (bool) $featured;
        }

        // WHERE koşulu oluştur
        $whereClause = !empty($whereConditions) ? " WHERE " . implode(' AND ', $whereConditions) : "";

        // Cinsiyet sayılarını hesapla
        $genderCountsQuery = "SELECT g.id, g.name, g.slug, COUNT(DISTINCT p.id) as product_count
                             $baseQuery
                             $whereClause
                             GROUP BY g.id, g.name, g.slug
                             ORDER BY g.name";

        // Kategori sayılarını hesapla
        $categoryCountsQuery = "SELECT c.id, c.name, c.slug, COUNT(DISTINCT p.id) as product_count
                               $baseQuery
                               $whereClause
                               GROUP BY c.id, c.name, c.slug
                               ORDER BY c.name";

        // Cinsiyet filtresi olmadan cinsiyet sayılarını hesapla
        $genderCountsWithoutGenderFilter = $genderCountsQuery;
        $paramsWithoutGenderFilter = $whereParams;

        if (!empty($genders)) {
            // Cinsiyet filtresini kaldır
            $whereConditionsWithoutGenderFilter = array_filter($whereConditions, function ($condition) {
                return strpos($condition, "g.slug IN") === false;
            });

            $whereClauseWithoutGenderFilter = !empty($whereConditionsWithoutGenderFilter)
                ? " WHERE " . implode(' AND ', $whereConditionsWithoutGenderFilter)
                : "";

            $genderCountsWithoutGenderFilter = "SELECT g.id, g.name, g.slug, COUNT(DISTINCT p.id) as product_count
                                              $baseQuery
                                              $whereClauseWithoutGenderFilter
                                              GROUP BY g.id, g.name, g.slug
                                              ORDER BY g.name";

            $paramsWithoutGenderFilter = array_diff($whereParams, $genders);
        }

        // Kategori filtresi olmadan kategori sayılarını hesapla
        $categoryCountsWithoutCategoryFilter = $categoryCountsQuery;
        $paramsWithoutCategoryFilter = $whereParams;

        if (!empty($categories)) {
            // Kategori filtresini kaldır
            $whereConditionsWithoutCategoryFilter = array_filter($whereConditions, function ($condition) {
                return strpos($condition, "c.slug IN") === false;
            });

            $whereClauseWithoutCategoryFilter = !empty($whereConditionsWithoutCategoryFilter)
                ? " WHERE " . implode(' AND ', $whereConditionsWithoutCategoryFilter)
                : "";

            $categoryCountsWithoutCategoryFilter = "SELECT c.id, c.name, c.slug, COUNT(DISTINCT p.id) as product_count
                                                  $baseQuery
                                                  $whereClauseWithoutCategoryFilter
                                                  GROUP BY c.id, c.name, c.slug
                                                  ORDER BY c.name";

            $paramsWithoutCategoryFilter = array_diff($whereParams, $categories);
        }

        // Sorguları çalıştır
        $genderCounts = $this->db->executeRawSql($genderCountsWithoutGenderFilter, $paramsWithoutGenderFilter);
        $this->performance_metrics['queries_executed']++;

        $categoryCounts = $this->db->executeRawSql($categoryCountsWithoutCategoryFilter, $paramsWithoutCategoryFilter);
        $this->performance_metrics['queries_executed']++;

        $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

        return [
            'gender_counts' => $genderCounts,
            'category_counts' => $categoryCounts
        ];
    }

    /**
     * Supabase için filtrelenmiş ürünlere göre cinsiyet ve kategori sayılarını hesaplar
     */
    private function getFilterCountsSupabase($params, $start_time)
    {
        // Supabase implementasyonu burada olacak
        // Şimdilik boş bir array döndürüyoruz
        $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
        return [
            'gender_counts' => [],
            'category_counts' => []
        ];
    }

    /**
     * Demo filter counts döndürür
     */
    private function getDemoFilterCounts($params = [])
    {
        // Demo gender counts
        $genderCounts = [
            [
                'id' => 1,
                'name' => 'Erkek',
                'slug' => 'erkek',
                'product_count' => 8
            ],
            [
                'id' => 2,
                'name' => 'Kadın',
                'slug' => 'kadin',
                'product_count' => 7
            ],
            [
                'id' => 3,
                'name' => 'Unisex',
                'slug' => 'unisex',
                'product_count' => 2
            ]
        ];

        // Demo category counts
        $categoryCounts = [
            [
                'id' => 1,
                'name' => 'Sneaker',
                'slug' => 'sneaker',
                'product_count' => 9
            ],
            [
                'id' => 2,
                'name' => 'Koşu Ayakkabısı',
                'slug' => 'kosu-ayakkabisi',
                'product_count' => 3
            ]
        ];
return [
    'gender_counts' => $genderCounts,
    'category_counts' => $categoryCounts
];
}

/**
* Demo similar products döndürür
*/
private function getDemoSimilarProducts($product_id, $limit = 5)
{
$demoProducts = [
    [
        'id' => 2,
        'name' => 'Adidas Ultraboost 22',
        'slug' => 'adidas-ultraboost-22',
        'description' => 'Yenilikçi teknoloji ile maksimum performans.',
        'price' => 1299.99,
        'image_url' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop&crop=center',
        'is_featured' => true
    ],
    [
        'id' => 3,
        'name' => 'Converse Chuck Taylor All Star',
        'slug' => 'converse-chuck-taylor-all-star',
        'description' => 'Klasik stil hiç eskimez.',
        'price' => 449.99,
        'image_url' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=400&h=400&fit=crop&crop=center',
        'is_featured' => false
    ],
    [
        'id' => 4,
        'name' => 'Vans Old Skool',
        'slug' => 'vans-old-skool',
        'description' => 'Sokak stilinin vazgeçilmezi.',
        'price' => 549.99,
        'image_url' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop&crop=center',
        'is_featured' => false
    ],
    [
        'id' => 5,
        'name' => 'New Balance 990v5',
        'slug' => 'new-balance-990v5',
        'description' => 'Premium kalite ve üstün konfor.',
        'price' => 1599.99,
        'image_url' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=400&fit=crop&crop=center',
        'is_featured' => true
    ],
    [
        'id' => 6,
        'name' => 'Puma RS-X³',
        'slug' => 'puma-rs-x3',
        'description' => 'Geleceğin tasarımı bugünden.',
        'price' => 799.99,
        'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&crop=center',
        'is_featured' => false
    ]
];

// Mevcut ürünü hariç tut
$filteredProducts = array_filter($demoProducts, function($product) use ($product_id) {
    return $product['id'] != $product_id;
});

// Limit uygula
return array_slice($filteredProducts, 0, $limit);
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