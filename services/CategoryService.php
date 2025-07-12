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
}


function category_service()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new CategoryService();
    }
    return $instance;
}
