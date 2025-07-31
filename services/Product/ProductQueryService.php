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
        if (!$this->db) {
            return $this->getDemoProductModel($model_id);
        }
        
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
        if (!$this->db) {
            return $this->getDemoProductVariants($model_id, $active_only);
        }
        
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
        if (!$this->db) {
            return $this->getDemoProductImages($model_id);
        }
        
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

    /**
     * Demo ürün detayını döndürür
     */
    private function getDemoProductModel($model_id)
    {
        $model_id = intval($model_id);
        
        $demoProducts = [
            1 => [
                'id' => 1,
                'name' => 'Nike Air Max 270',
                'slug' => 'nike-air-max-270',
                'description' => 'Rahat ve stilin bir arada. Nike Air Max 270 ile her adımda konfor yaşayın. Bu ayakkabı, maksimum hava yastıklama teknolojisi ile donatılmış olup, günlük kullanım için ideal bir seçimdir.',
                'price' => 899.99,
                'is_featured' => true,
                'created_at' => '2024-01-15 10:00:00',
                'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop&crop=center',
                'categories' => [
                    ['id' => 1, 'name' => 'Sneaker', 'slug' => 'sneaker']
                ],
                'genders' => [
                    ['id' => 1, 'name' => 'Erkek', 'slug' => 'erkek'],
                    ['id' => 2, 'name' => 'Kadın', 'slug' => 'kadin']
                ],
                'images' => [
                    [
                        'id' => 1,
                        'model_id' => 1,
                        'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop&crop=center',
                        'is_primary' => 1,
                        'color_id' => null
                    ],
                    [
                        'id' => 2,
                        'model_id' => 1,
                        'image_url' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=400&fit=crop&crop=center',
                        'is_primary' => 0,
                        'color_id' => null
                    ]
                ],
                'category_name' => 'Sneaker',
                'category_slug' => 'sneaker'
            ],
            2 => [
                'id' => 2,
                'name' => 'Adidas Ultraboost 22',
                'slug' => 'adidas-ultraboost-22',
                'description' => 'Yenilikçi teknoloji ile maksimum performans. Adidas Ultraboost 22, koşu ve antrenman için mükemmel bir seçimdir.',
                'price' => 1299.99,
                'is_featured' => true,
                'created_at' => '2024-01-14 09:30:00',
                'image_url' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop&crop=center',
                'categories' => [
                    ['id' => 2, 'name' => 'Koşu Ayakkabısı', 'slug' => 'kosu-ayakkabisi']
                ],
                'genders' => [
                    ['id' => 1, 'name' => 'Erkek', 'slug' => 'erkek']
                ],
                'images' => [
                    [
                        'id' => 3,
                        'model_id' => 2,
                        'image_url' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop&crop=center',
                        'is_primary' => 1,
                        'color_id' => null
                    ]
                ],
                'category_name' => 'Koşu Ayakkabısı',
                'category_slug' => 'kosu-ayakkabisi'
            ]
        ];

        if (isset($demoProducts[$model_id])) {
            return [$demoProducts[$model_id]];
        }

        return [];
    }

    /**
     * Demo ürün varyantlarını döndürür
     */
    private function getDemoProductVariants($model_id, $active_only = true)
    {
        $model_id = intval($model_id);
        
        $demoVariants = [
            1 => [
                [
                    'id' => 1,
                    'model_id' => 1,
                    'color_id' => 1,
                    'size_id' => 1,
                    'sku' => 'NIKE-AM270-BLK-40',
                    'stock_quantity' => 10,
                    'price' => 899.99,
                    'is_active' => true
                ],
                [
                    'id' => 2,
                    'model_id' => 1,
                    'color_id' => 1,
                    'size_id' => 2,
                    'sku' => 'NIKE-AM270-BLK-41',
                    'stock_quantity' => 8,
                    'price' => 899.99,
                    'is_active' => true
                ],
                [
                    'id' => 3,
                    'model_id' => 1,
                    'color_id' => 2,
                    'size_id' => 1,
                    'sku' => 'NIKE-AM270-WHT-40',
                    'stock_quantity' => 5,
                    'price' => 899.99,
                    'is_active' => true
                ]
            ],
            2 => [
                [
                    'id' => 4,
                    'model_id' => 2,
                    'color_id' => 1,
                    'size_id' => 1,
                    'sku' => 'ADIDAS-UB22-BLK-40',
                    'stock_quantity' => 12,
                    'price' => 1299.99,
                    'is_active' => true
                ]
            ]
        ];

        if (isset($demoVariants[$model_id])) {
            $variants = $demoVariants[$model_id];
            
            if ($active_only) {
                $variants = array_filter($variants, function($variant) {
                    return $variant['is_active'];
                });
            }
            
            return $variants;
        }

        return [];
    }

    /**
     * Demo ürün görsellerini döndürür
     */
    private function getDemoProductImages($model_id)
    {
        $model_id = intval($model_id);
        
        $demoImages = [
            1 => [
                [
                    'id' => 1,
                    'model_id' => 1,
                    'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop&crop=center',
                    'is_primary' => 1,
                    'color_id' => null
                ],
                [
                    'id' => 2,
                    'model_id' => 1,
                    'image_url' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=400&fit=crop&crop=center',
                    'is_primary' => 0,
                    'color_id' => null
                ],
                [
                    'id' => 3,
                    'model_id' => 1,
                    'image_url' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=400&h=400&fit=crop&crop=center',
                    'is_primary' => 0,
                    'color_id' => null
                ]
            ],
            2 => [
                [
                    'id' => 4,
                    'model_id' => 2,
                    'image_url' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=400&fit=crop&crop=center',
                    'is_primary' => 1,
                    'color_id' => null
                ],
                [
                    'id' => 5,
                    'model_id' => 2,
                    'image_url' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=400&fit=crop&crop=center',
                    'is_primary' => 0,
                    'color_id' => null
                ]
            ]
        ];

        if (isset($demoImages[$model_id])) {
            return $demoImages[$model_id];
        }

        return [];
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
