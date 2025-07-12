<?php



require_once __DIR__ . '/../lib/DatabaseFactory.php';
require_once __DIR__ . '/Product/ProductQueryService.php';
require_once __DIR__ . '/Product/ProductFilterService.php';
require_once __DIR__ . '/Product/ProductApiService.php';
require_once __DIR__ . '/Product/ProductAdminService.php';
require_once __DIR__ . '/Product/ProductManagementService.php';


class ProductService
{
    private $db;
    private $queryService;
    private $filterService;
    private $apiService;
    private $adminService;
    private $managementService;


    public function __construct()
    {
        $this->db = database();
        $this->queryService = new ProductQueryService();
        $this->filterService = new ProductFilterService();
        $this->apiService = new ProductApiService();
        $this->adminService = new ProductAdminService();
        $this->managementService = new ProductManagementService();
    }


    public function getProductModels($limit = 20, $offset = 0, $filters = [])
    {

        $params = [
            'page' => 1,
            'limit' => $limit
        ];


        if (!empty($filters['category_slug'])) {
            $params['categories'] = [$filters['category_slug']];
        }
        if (!empty($filters['gender_slug'])) {
            $params['genders'] = [$filters['gender_slug']];
        }

        $result = $this->apiService->getProductsForApi($params);
        return $result['products'] ?? [];
    }


    public function getProductModelsWithMultiCategory($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null)
    {
        $page = floor($offset / $limit) + 1;

        $params = [
            'page' => $page,
            'limit' => $limit,
            'sort' => $sort,
            'categories' => $category_slugs,
            'featured' => $featured
        ];


        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== [];
        });

        $result = $this->apiService->getProductsForApi($params);
        return $result['products'] ?? [];
    }


    public function getProductsForApi($params = [])
    {
        return $this->apiService->getProductsForApi($params);
    }



    public function getProductModel($model_id)
    {
        return $this->queryService->getProductModel($model_id);
    }


    public function getProductVariants($model_id, $active_only = true)
    {
        return $this->queryService->getProductVariants($model_id, $active_only);
    }


    public function getProductImages($model_id)
    {
        return $this->queryService->getProductImages($model_id);
    }


    public function getTotalProductCount($category_slugs = null, $featured = null)
    {
        $params = [
            'limit' => 1,
            'categories' => $category_slugs,
            'featured' => $featured
        ];


        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== [];
        });

        $result = $this->apiService->getProductsForApi($params);
        return $result['total'] ?? 0;
    }


    public function getAdminProducts($limit = 20, $offset = 0, $filters = [])
    {
        return $this->adminService->getAdminProducts($limit, $offset, $filters);
    }


    public function getAdminProductsOptimized($limit = 20, $offset = 0)
    {
        return $this->adminService->getAdminProducts($limit, $offset);
    }


    public function deleteProduct($product_id)
    {
        return $this->managementService->deleteProduct($product_id);
    }


    public function updateProductStatus($product_id, $is_featured)
    {
        return $this->managementService->updateProductStatus($product_id, $is_featured);
    }
}


function product_service()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new ProductService();
    }

    return $instance;
}



function get_product_models($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null)
{
    return product_service()->getProductModelsWithMultiCategory($limit, $offset, $category_slugs, $featured, $sort);
}


function get_product_model($model_id)
{
    return product_service()->getProductModel($model_id);
}


function get_product_variants($model_id, $active_only = true)
{
    return product_service()->getProductVariants($model_id, $active_only);
}


function get_product_images($model_id)
{
    return product_service()->getProductImages($model_id);
}


function get_total_product_count($category_slugs = null, $featured = null)
{
    return product_service()->getTotalProductCount($category_slugs, $featured);
}


function get_admin_products($limit = 20, $offset = 0)
{
    return product_service()->getAdminProducts($limit, $offset);
}


function delete_product($product_id)
{
    return product_service()->deleteProduct($product_id);
}


function update_product_status($product_id, $is_featured)
{
    return product_service()->updateProductStatus($product_id, $is_featured);
}


function get_products_for_api($params = [])
{
    return product_service()->getProductsForApi($params);
}
