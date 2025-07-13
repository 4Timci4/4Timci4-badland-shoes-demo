<?php


require_once __DIR__ . '/../lib/DatabaseFactory.php';

class CategoryService
{
    private $db;
    private $performance_metrics;

    public function __construct()
    {
        $this->db = database();
        $this->performance_metrics = [
            'execution_time_ms' => 0,
            'queries_executed' => 0,
            'categories_count' => 0
        ];
    }


    public function getCategoriesWithProductCountsOptimized($include_empty = true)
    {
        $start_time = microtime(true);

        try {
            $conditions = [];
            if (!$include_empty) {
                $conditions['product_count'] = ['>', 0];
            }

            $result = $this->db->select('category_product_counts', $conditions, '*', [
                'order' => 'category_name ASC'
            ]);

            $this->performance_metrics['queries_executed']++;
            $this->performance_metrics['categories_count'] = count($result);
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

            return $result;

        } catch (Exception $e) {
            error_log("CategoryService Error (Materialized View): " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }


    public function getCategoriesWithProductCounts($include_empty = true)
    {

        return $this->getCategoriesWithProductCountsOptimized($include_empty);
    }



    public function getPerformanceMetrics()
    {
        return $this->performance_metrics;
    }

    public function createCategory($data)
    {
        return $this->db->insert('categories', $data);
    }

    public function updateCategory($id, $data)
    {
        return $this->db->update('categories', $data, ['id' => $id]);
    }

    public function deleteCategory($id)
    {
        return $this->db->delete('categories', ['id' => $id]);
    }

    public function getCategoryById($id)
    {
        $start_time = microtime(true);
        try {
            $result = $this->db->select('category_product_counts', ['category_id' => $id], '*', ['limit' => 1]);

            $this->performance_metrics['queries_executed']++;
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

            return $result[0] ?? null;

        } catch (Exception $e) {
            error_log("CategoryService Error (getCategoryById): " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return null;
        }
    }

    /**
     * Cinsiyet bazında kategori sayılarını hesaplar
     */
    public function getCategoriesWithProductCountsByGender($gender_slug = null, $include_empty = true)
    {
        $start_time = microtime(true);

        try {
            $dbType = DatabaseFactory::getCurrentType();

            if ($dbType === 'supabase') {
                return $this->getCategoriesWithProductCountsByGenderSupabase($gender_slug, $include_empty, $start_time);
            } else {
                return $this->getCategoriesWithProductCountsByGenderMariaDB($gender_slug, $include_empty, $start_time);
            }

        } catch (Exception $e) {
            error_log("CategoryService Error (getCategoriesWithProductCountsByGender): " . $e->getMessage());
            $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
            return [];
        }
    }

    /**
     * MariaDB için cinsiyet bazında kategori sayılarını hesaplar
     */
    private function getCategoriesWithProductCountsByGenderMariaDB($gender_slug, $include_empty, $start_time)
    {
        $whereConditions = [];
        $whereParams = [];

        if ($gender_slug) {
            $whereConditions[] = "g.slug = ?";
            $whereParams[] = $gender_slug;
        }

        $whereClause = !empty($whereConditions) ? " WHERE " . implode(' AND ', $whereConditions) : "";

        $query = "SELECT c.id as category_id, c.name as category_name, c.slug as category_slug,
                         COUNT(DISTINCT pm.id) as product_count
                  FROM categories c
                  LEFT JOIN product_categories pc ON c.id = pc.category_id
                  LEFT JOIN product_models pm ON pc.product_id = pm.id
                  LEFT JOIN product_genders pg ON pm.id = pg.product_id
                  LEFT JOIN genders g ON pg.gender_id = g.id
                  $whereClause
                  GROUP BY c.id, c.name, c.slug";

        if (!$include_empty) {
            $query .= " HAVING product_count > 0";
        }

        $query .= " ORDER BY c.name ASC";

        $result = $this->db->executeRawSql($query, $whereParams);
        $this->performance_metrics['queries_executed']++;
        $this->performance_metrics['categories_count'] = count($result);
        $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);

        return $result;
    }

    /**
     * Supabase için cinsiyet bazında kategori sayılarını hesaplar
     */
    private function getCategoriesWithProductCountsByGenderSupabase($gender_slug, $include_empty, $start_time)
    {
        // Supabase implementasyonu burada olacak
        // Şimdilik boş bir array döndürüyoruz
        $this->performance_metrics['execution_time_ms'] = round((microtime(true) - $start_time) * 1000, 2);
        return [];
    }
}


function category_service()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new CategoryService();
    }
    return $instance;
}
