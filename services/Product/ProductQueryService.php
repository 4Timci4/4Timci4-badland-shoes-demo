<?php

require_once __DIR__ . '/../../lib/DatabaseFactory.php';

class ProductQueryService
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: database();
    }


    public function getProductModel($model_id)
    {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return [];
            }


            $dbType = DatabaseFactory::getCurrentType();

            if ($dbType === 'supabase') {

                $select_query = '*, categories:product_categories(categories(*)), genders:product_genders(genders(*)), images:product_images(*)';
                $products = $this->db->select('product_models', ['id' => $model_id], $select_query, ['limit' => 1]);
            } else {

                $products = $this->db->select('product_models', ['id' => $model_id], '*', ['limit' => 1]);

                if (!empty($products)) {

                    $categories = $this->db->executeRawSql(
                        "SELECT c.* FROM categories c
                         JOIN product_categories pc ON c.id = pc.category_id
                         WHERE pc.product_id = ?",
                        [$model_id]
                    );
                    $products[0]['categories'] = [];
                    foreach ($categories as $category) {
                        $products[0]['categories'][] = ['categories' => $category];
                    }


                    $genders = $this->db->executeRawSql(
                        "SELECT g.* FROM genders g
                         JOIN product_genders pg ON g.id = pg.gender_id
                         WHERE pg.product_id = ?",
                        [$model_id]
                    );
                    $products[0]['genders'] = [];
                    foreach ($genders as $gender) {
                        $products[0]['genders'][] = ['genders' => $gender];
                    }


                    $images = $this->db->select('product_images', ['model_id' => $model_id]);
                    $products[0]['images'] = $images;
                }
            }

            if (empty($products)) {
                return [];
            }

            $product = $products[0];


            $product['categories'] = !empty($product['categories']) ? array_map(function ($c) {
                return $c['categories'];
            }, $product['categories']) : [];
            $product['genders'] = !empty($product['genders']) ? array_map(function ($g) {
                return $g['genders'];
            }, $product['genders']) : [];


            if (!empty($product['categories'])) {
                $product['category_name'] = $product['categories'][0]['name'];
                $product['category_slug'] = $product['categories'][0]['slug'];
            } else {
                $product['category_name'] = 'Ayakkabı';
                $product['category_slug'] = 'ayakkabi';
            }


            if (!empty($product['images'])) {
                $primary_image = array_filter($product['images'], function ($img) {
                    return $img['is_primary'] == 1;
                });
                if (!empty($primary_image)) {
                    $product['image_url'] = reset($primary_image)['image_url'];
                } else {
                    $product['image_url'] = $product['images'][0]['image_url'];
                }
            }

            return [$product];

        } catch (Exception $e) {
            error_log("Ürün modeli getirme hatası (Optimized): " . $e->getMessage());
            return [];
        }
    }


    public function getProductVariants($model_id, $active_only = true)
    {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return [];
            }

            $conditions = ['model_id' => $model_id];


            if ($active_only) {
                $conditions['is_active'] = true;
            }

            return $this->db->select('product_variants', $conditions, ['*']);

        } catch (Exception $e) {
            error_log("Ürün varyantları getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getProductImages($model_id)
    {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return [];
            }

            return $this->db->select('product_images', ['model_id' => $model_id], ['*'], ['order' => 'is_primary DESC, id ASC']);

        } catch (Exception $e) {
            error_log("Ürün görselleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getProductBasicInfo($model_id)
    {
        try {
            $model_id = intval($model_id);
            if ($model_id <= 0) {
                return null;
            }

            $products = $this->db->select(
                'product_models',
                ['id' => $model_id],
                'id, name, description, is_featured',
                ['limit' => 1]
            );

            return !empty($products) ? $products[0] : null;

        } catch (Exception $e) {
            error_log("Ürün temel bilgi getirme hatası: " . $e->getMessage());
            return null;
        }
    }


    public function getProductModelsByIds($model_ids)
    {
        try {
            if (empty($model_ids)) {
                return [];
            }


            $clean_ids = array_map('intval', $model_ids);
            $clean_ids = array_filter($clean_ids, function ($id) {
                return $id > 0;
            });

            if (empty($clean_ids)) {
                return [];
            }

            $products = $this->db->select('product_models', ['id' => ['IN', $clean_ids]], ['*']);


            foreach ($products as &$product) {


                $images = $this->db->select('product_images', [
                    'model_id' => $product['id'],
                    'is_primary' => 1
                ], 'image_url', ['limit' => 1]);

                if (!empty($images)) {
                    $product['image_url'] = $images[0]['image_url'];
                }
            }

            return $products;

        } catch (Exception $e) {
            error_log("Çoklu ürün getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getVariantById($variant_id)
    {
        try {
            $variant_id = intval($variant_id);
            if ($variant_id <= 0) {
                return [];
            }


            $variants = $this->db->select('product_variants', ['id' => $variant_id], ['*'], ['limit' => 1]);

            if (empty($variants)) {
                return [];
            }

            $variant = $variants[0];


            if (!empty($variant['color_id'])) {
                $colors = $this->db->select('colors', ['id' => $variant['color_id']], ['name', 'hex_code'], ['limit' => 1]);
                if (!empty($colors)) {
                    $variant['color_name'] = $colors[0]['name'];
                    $variant['color_hex'] = $colors[0]['hex_code'];
                }
            }


            if (!empty($variant['size_id'])) {
                $sizes = $this->db->select('sizes', ['id' => $variant['size_id']], ['size_value'], ['limit' => 1]);
                if (!empty($sizes)) {
                    $variant['size_value'] = $sizes[0]['size_value'];
                }
            }

            return $variant;

        } catch (Exception $e) {
            error_log("Varyant getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getVariantImages($model_id, $color_id)
    {
        try {
            $model_id = intval($model_id);
            $color_id = intval($color_id);

            if ($model_id <= 0 || $color_id <= 0) {
                return [];
            }


            $images = $this->db->select('product_images', [
                'model_id' => $model_id,
                'color_id' => $color_id
            ], ['*'], ['order' => 'is_primary DESC, id ASC']);


            if (empty($images)) {
                $images = $this->db->select('product_images', [
                    'model_id' => $model_id,
                    'is_primary' => 1
                ], ['*'], ['limit' => 1]);


                if (empty($images)) {
                    $images = $this->db->select('product_images', [
                        'model_id' => $model_id
                    ], ['*'], ['limit' => 1]);
                }
            }

            return $images;

        } catch (Exception $e) {
            error_log("Varyant görselleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}


function product_query_service()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new ProductQueryService();
    }

    return $instance;
}
