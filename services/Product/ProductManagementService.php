<?php

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/../../lib/AutoCache.php';

class ProductManagementService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }


    public function deleteProduct($product_id)
    {
        try {
            $product_id = intval($product_id);

            if ($product_id <= 0) {
                throw new Exception("Geçersiz ürün ID: $product_id");
            }


            $product = $this->db->select('product_models', ['id' => $product_id], 'id', ['limit' => 1]);
            if (empty($product)) {
                throw new Exception("Ürün bulunamadı: $product_id");
            }


            if (method_exists($this->db, 'beginTransaction')) {
                $this->db->beginTransaction();
            }

            try {

                $this->db->delete('product_categories', ['product_id' => $product_id]);


                $this->db->delete('product_genders', ['product_id' => $product_id]);


                $this->db->delete('product_images', ['model_id' => $product_id]);


                $this->db->delete('product_variants', ['model_id' => $product_id]);


                $this->db->delete('product_attributes', ['product_id' => $product_id]);


                $result = $this->db->delete('product_models', ['id' => $product_id]);


                if (method_exists($this->db, 'commit')) {
                    $this->db->commit();
                }


                $this->invalidateProductCaches($product_id);

                return $result !== false;

            } catch (Exception $e) {

                if (method_exists($this->db, 'rollback')) {
                    $this->db->rollback();
                }
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Ürün silme hatası (ID: $product_id): " . $e->getMessage());
            return false;
        }
    }


    public function updateProductStatus($product_id, $is_featured)
    {
        try {
            $product_id = intval($product_id);

            if ($product_id <= 0) {
                return false;
            }

            $data = ['is_featured' => $is_featured ? 1 : 0];
            $result = $this->db->update('product_models', $data, ['id' => $product_id]);
            return $result !== false;

        } catch (Exception $e) {
            error_log("Ürün durumu güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function createProduct($product_data)
    {
        try {

            $required_fields = ['name'];
            foreach ($required_fields as $field) {
                if (empty($product_data[$field])) {
                    throw new Exception("Gerekli alan eksik: $field");
                }
            }



            $insert_data = [
                'name' => trim($product_data['name']),
                'description' => $product_data['description'] ?? '',
                'features' => $product_data['features'] ?? '',
                'is_featured' => isset($product_data['is_featured']) ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s')
            ];


            if (method_exists($this->db, 'beginTransaction')) {
                $this->db->beginTransaction();
            }

            try {

                $result = $this->db->insert('product_models', $insert_data);

                if (!$result || (is_array($result) && empty($result))) {
                    throw new Exception("Ürün oluşturulamadı");
                }


                $product_id = is_array($result) ? (isset($result['id']) ? $result['id'] : $result[0]['id'] ?? false) : $result;

                if (!$product_id) {
                    throw new Exception("Ürün ID'si alınamadı");
                }


                if (!empty($product_data['category_ids'])) {
                    $this->addProductCategories(intval($product_id), $product_data['category_ids']);
                }


                if (!empty($product_data['gender_ids'])) {
                    $this->addProductGenders(intval($product_id), $product_data['gender_ids']);
                }


                if (!empty($product_data['images'])) {
                    $this->addProductImages(intval($product_id), $product_data['images']);
                }


                if (method_exists($this->db, 'commit')) {
                    $this->db->commit();
                }

                return $product_id;

            } catch (Exception $e) {

                if (method_exists($this->db, 'rollback')) {
                    $this->db->rollback();
                }
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Ürün oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }


    public function updateProduct($product_id, $product_data)
    {
        try {
            $product_id = intval($product_id);

            if ($product_id <= 0) {
                return false;
            }


            $update_data = [];

            if (isset($product_data['name'])) {
                $update_data['name'] = trim($product_data['name']);
            }

            if (isset($product_data['description'])) {
                $update_data['description'] = $product_data['description'];
            }

            if (isset($product_data['features'])) {
                $update_data['features'] = $product_data['features'];
            }


            if (isset($product_data['is_featured'])) {
                $update_data['is_featured'] = $product_data['is_featured'] ? 1 : 0;
            }

            if (empty($update_data)) {
                return true;
            }


            if (method_exists($this->db, 'beginTransaction')) {
                $this->db->beginTransaction();
            }

            try {

                $result = $this->db->update('product_models', $update_data, ['id' => $product_id]);


                if (isset($product_data['category_ids'])) {
                    $this->updateProductCategories($product_id, $product_data['category_ids']);
                }


                if (isset($product_data['gender_ids'])) {
                    $this->updateProductGenders($product_id, $product_data['gender_ids']);
                }


                if (method_exists($this->db, 'commit')) {
                    $this->db->commit();
                }

                return $result !== false;

            } catch (Exception $e) {

                if (method_exists($this->db, 'rollback')) {
                    $this->db->rollback();
                }
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Ürün güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    private function addProductCategories($product_id, $category_ids)
    {
        try {
            foreach ($category_ids as $category_id) {
                $this->db->insert('product_categories', [
                    'product_id' => $product_id,
                    'category_id' => intval($category_id)
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Ürün kategorileri ekleme hatası: " . $e->getMessage());
            throw $e;
        }
    }


    private function addProductGenders($product_id, $gender_ids)
    {
        try {
            foreach ($gender_ids as $gender_id) {
                $this->db->insert('product_genders', [
                    'product_id' => $product_id,
                    'gender_id' => intval($gender_id)
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Ürün cinsiyetleri ekleme hatası: " . $e->getMessage());
            throw $e;
        }
    }


    private function addProductImages($product_id, $images)
    {
        try {
            foreach ($images as $index => $image) {
                $this->db->insert('product_images', [
                    'model_id' => $product_id,
                    'image_url' => $image['url'],
                    'is_primary' => ($index === 0) ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Ürün görselleri ekleme hatası: " . $e->getMessage());
            throw $e;
        }
    }


    private function updateProductCategories($product_id, $category_ids)
    {
        try {

            $this->db->delete('product_categories', ['product_id' => $product_id]);


            if (!empty($category_ids)) {
                $this->addProductCategories($product_id, $category_ids);
            }

            return true;
        } catch (Exception $e) {
            error_log("Ürün kategorileri güncelleme hatası: " . $e->getMessage());
            throw $e;
        }
    }


    private function updateProductGenders($product_id, $gender_ids)
    {
        try {

            $this->db->delete('product_genders', ['product_id' => $product_id]);


            if (!empty($gender_ids)) {
                $this->addProductGenders($product_id, $gender_ids);
            }

            return true;
        } catch (Exception $e) {
            error_log("Ürün cinsiyetleri güncelleme hatası: " . $e->getMessage());
            throw $e;
        }
    }


    public function deleteMultipleProducts($product_ids)
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($product_ids as $product_id) {
            if ($this->deleteProduct($product_id)) {
                $results['success'][] = $product_id;
            } else {
                $results['failed'][] = $product_id;
            }
        }

        return $results;
    }


    public function updateMultipleProductStatus($product_ids, $is_featured)
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($product_ids as $product_id) {
            if ($this->updateProductStatus($product_id, $is_featured)) {
                $results['success'][] = $product_id;
            } else {
                $results['failed'][] = $product_id;
            }
        }

        return $results;
    }


    public function addProductVariant($model_id, $variant_data)
    {
        try {

            $insert_data = [
                'model_id' => intval($model_id),
                'size' => $variant_data['size'] ?? '',
                'color' => $variant_data['color'] ?? '',
                'stock_quantity' => intval($variant_data['stock_quantity'] ?? 0),
                'sku' => $variant_data['sku'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->insert('product_variants', $insert_data);


            if (is_array($result)) {
                return isset($result['id']) ? $result['id'] : (isset($result[0]['id']) ? $result[0]['id'] : false);
            }

            return $result;

        } catch (Exception $e) {
            error_log("Ürün varyantı ekleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function updateProductVariant($variant_id, $variant_data)
    {
        try {
            $update_data = [];

            if (isset($variant_data['size'])) {
                $update_data['size'] = $variant_data['size'];
            }

            if (isset($variant_data['color'])) {
                $update_data['color'] = $variant_data['color'];
            }


            if (isset($variant_data['stock_quantity'])) {
                $update_data['stock_quantity'] = intval($variant_data['stock_quantity']);
            }

            if (isset($variant_data['sku'])) {
                $update_data['sku'] = $variant_data['sku'];
            }

            if (empty($update_data)) {
                return true;
            }

            $result = $this->db->update('product_variants', $update_data, ['id' => intval($variant_id)]);

            return $result !== false;

        } catch (Exception $e) {
            error_log("Ürün varyantı güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function deleteProductVariant($variant_id)
    {
        try {
            $result = $this->db->delete('product_variants', ['id' => intval($variant_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Ürün varyantı silme hatası: " . $e->getMessage());
            return false;
        }
    }


    private function invalidateProductCaches($product_id = null)
    {
        try {

            autoCache()->autoInvalidate('admin_products_*');


            if ($product_id) {
                autoCache()->autoInvalidate("product_detail_{$product_id}_*");
                autoCache()->autoInvalidate("product_variants_{$product_id}");
                autoCache()->autoInvalidate("product_images_{$product_id}");
            }


            autoCache()->autoInvalidate('product_stats_*');
            autoCache()->autoInvalidate('category_product_counts_*');
            autoCache()->autoInvalidate('recent_products_*');


            autoCache()->autoInvalidate('api_products_*');
            autoCache()->autoInvalidate('products_for_api_*');

            error_log("Cache invalidation tamamlandı - Product ID: " . ($product_id ?: 'ALL'));

        } catch (Exception $e) {
            error_log("Cache invalidation hatası: " . $e->getMessage());
        }
    }


    public function clearAllProductCaches()
    {
        $this->invalidateProductCaches();
    }

    public function refreshMaterializedViews()
    {
        try {
            $dbType = DatabaseFactory::getCurrentType();

            if ($dbType === 'supabase') {

                return $this->db->rpc('refresh_materialized_views');
            } else {


                $this->db->executeRawSql('CALL refresh_materialized_views();');
                return true;
            }
        } catch (Exception $e) {
            error_log("Materialized view yenileme hatası: " . $e->getMessage());
            return false;
        }
    }
}


function product_management_service()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new ProductManagementService();
    }

    return $instance;
}
