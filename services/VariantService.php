<?php



require_once __DIR__ . '/../lib/DatabaseFactory.php';


class VariantService
{
    private $db;


    public function __construct()
    {
        $this->db = database();
    }


    public function getAllColors()
    {
        if (!$this->db) {
            return $this->getDemoColors();
        }
        try {
            return $this->db->select('colors', [], '*', ['order' => 'name ASC']);
        } catch (Exception $e) {
            error_log("Renkleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getAllSizes()
    {
        if (!$this->db) {
            return $this->getDemoSizes();
        }
        try {
            return $this->db->select('sizes', [], '*', ['order' => 'size_value ASC']);
        } catch (Exception $e) {
            error_log("Bedenleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getProductVariants($model_id)
    {
        if (!$this->db) {
            return $this->getDemoProductVariants($model_id);
        }
        try {

            $variants = $this->db->select(
                'product_variants',
                ['model_id' => intval($model_id)],
                '*',
                ['order' => 'id ASC']
            );


            foreach ($variants as &$variant) {

                if (!empty($variant['color_id'])) {
                    $colors = $this->db->select('colors', ['id' => $variant['color_id']], '*', ['limit' => 1]);
                    if (!empty($colors)) {
                        $variant['color_name'] = $colors[0]['name'];
                        $variant['color_hex'] = $colors[0]['hex_code'];
                    }
                }


                if (!empty($variant['size_id'])) {
                    $sizes = $this->db->select('sizes', ['id' => $variant['size_id']], '*', ['limit' => 1]);
                    if (!empty($sizes)) {
                        $variant['size_value'] = $sizes[0]['size_value'];
                        $variant['size_type'] = $sizes[0]['size_type'];
                    }
                }
            }

            return $variants;
        } catch (Exception $e) {
            error_log("Ürün varyantları getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function createVariant($data)
    {
        if (!$this->db) {
            return false; // Demo modunda varyant oluşturma devre dışı
        }
        try {

            if (!empty($data['model_id']) && !empty($data['color_id']) && !empty($data['size_id'])) {
                $existing = $this->db->select('product_variants', [
                    'model_id' => intval($data['model_id']),
                    'color_id' => intval($data['color_id']),
                    'size_id' => intval($data['size_id'])
                ], 'id', ['limit' => 1]);

                if (!empty($existing)) {
                    throw new Exception("Bu model, renk ve beden kombinasyonu zaten mevcut");
                }
            }


            if (empty($data['sku'])) {
                $data['sku'] = $this->generateSKU($data['model_id'], $data['color_id'], $data['size_id']);
            }


            $new_variant_array = $this->db->insert('product_variants', $data, ['returning' => 'representation']);

            if ($new_variant_array && !empty($new_variant_array)) {
                $new_variant_id = $new_variant_array[0]['id'] ?? null;
                if ($new_variant_id) {

                    return $this->getVariantById($new_variant_id);
                }
            }

            return false;

        } catch (Exception $e) {
            $error_message = $e->getMessage();


            if (strpos($error_message, 'duplicate key') !== false || strpos($error_message, '23505') !== false) {
                throw new Exception("Bu model, renk ve beden kombinasyonu zaten mevcut");
            }

            error_log("Varyant oluşturma hatası: " . $error_message);
            throw $e;
        }
    }


    public function updateVariant($variant_id, $data)
    {
        if (!$this->db) {
            return false; // Demo modunda güncelleme devre dışı
        }
        try {
            $result = $this->db->update('product_variants', $data, ['id' => intval($variant_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Varyant güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function deleteVariant($variant_id)
    {
        if (!$this->db) {
            return false; // Demo modunda silme devre dışı
        }
        try {
            $result = $this->db->delete('product_variants', ['id' => intval($variant_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Varyant silme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function createBulkVariants($model_id, $variants)
    {
        if (!$this->db) {
            return []; // Demo modunda bulk işlem devre dışı
        }
        $results = [];

        foreach ($variants as $variant) {
            $variant['model_id'] = $model_id;
            $result = $this->createVariant($variant);
            $results[] = [
                'variant' => $variant,
                'success' => $result
            ];
        }

        return $results;
    }


    private function generateSKU($model_id, $color_id, $size_id)
    {
        $timestamp = time();
        return "PRD{$model_id}-C{$color_id}-S{$size_id}-{$timestamp}";
    }


    public function updateStock($variant_id, $quantity)
    {
        if (!$this->db) {
            return false; // Demo modunda stok güncelleme devre dışı
        }
        try {
            $data = [
                'stock_quantity' => intval($quantity),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->updateVariant($variant_id, $data);
        } catch (Exception $e) {
            error_log("Stok güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function getVariantById($variant_id)
    {
        if (!$this->db) {
            return $this->getDemoVariantById($variant_id);
        }
        try {
            $dbType = DatabaseFactory::getCurrentType();

            if ($dbType === 'supabase') {

                $select_query = '*,colors(name,hex_code),sizes(size_value,size_type)';
                $variants = $this->db->select(
                    'product_variants',
                    ['id' => intval($variant_id)],
                    $select_query,
                    ['limit' => 1]
                );

                if (!empty($variants)) {
                    $variant = $variants[0];
                    if (isset($variant['colors'])) {
                        $variant['color_name'] = $variant['colors']['name'];
                        $variant['color_hex'] = $variant['colors']['hex_code'];
                        unset($variant['colors']);
                    }
                    if (isset($variant['sizes'])) {
                        $variant['size_value'] = $variant['sizes']['size_value'];
                        $variant['size_type'] = $variant['sizes']['size_type'];
                        unset($variant['sizes']);
                    }
                    return $variant;
                }
            } else {

                $sql = "SELECT pv.*, c.name as color_name, c.hex_code as color_hex, s.size_value, s.size_type
                        FROM product_variants pv
                        LEFT JOIN colors c ON pv.color_id = c.id
                        LEFT JOIN sizes s ON pv.size_id = s.id
                        WHERE pv.id = :variant_id
                        LIMIT 1";

                $result = $this->db->executeRawSql($sql, ['variant_id' => intval($variant_id)]);
                if (!empty($result)) {
                    return $result[0];
                }
            }

            return null;
        } catch (Exception $e) {
            error_log("Varyant getirme hatası: " . $e->getMessage());
            return null;
        }
    }


    public function getTotalStock($model_id)
    {
        if (!$this->db) {
            return $this->getDemoTotalStock($model_id);
        }
        try {
            $variants = $this->db->select(
                'product_variants',
                ['model_id' => intval($model_id), 'is_active' => 1],
                'stock_quantity'
            );

            $total_stock = 0;
            foreach ($variants as $variant) {
                $total_stock += intval($variant['stock_quantity'] ?? 0);
            }

            return $total_stock;
        } catch (Exception $e) {
            error_log("Toplam stok getirme hatası: " . $e->getMessage());
            return 0;
        }
    }


    public function getProductColors($model_id)
    {
        if (!$this->db) {
            return $this->getDemoProductColors($model_id);
        }
        try {
            $variants = $this->db->select(
                'product_variants',
                ['model_id' => intval($model_id), 'is_active' => 1],
                'color_id'
            );

            $color_ids = array_unique(array_column($variants, 'color_id'));

            if (empty($color_ids)) {
                return [];
            }

            $colors = $this->db->select('colors', ['id' => ['IN', $color_ids]], '*', ['order' => 'name ASC']);

            return $colors;
        } catch (Exception $e) {
            error_log("Ürün renkleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getProductSizes($model_id)
    {
        if (!$this->db) {
            return $this->getDemoProductSizes($model_id);
        }
        try {
            $variants = $this->db->select(
                'product_variants',
                ['model_id' => intval($model_id), 'is_active' => 1],
                'size_id'
            );

            $size_ids = array_unique(array_column($variants, 'size_id'));

            if (empty($size_ids)) {
                return [];
            }

            $sizes = $this->db->select('sizes', ['id' => ['IN', $size_ids]], '*', ['order' => 'size_value ASC']);

            return $sizes;
        } catch (Exception $e) {
            error_log("Ürün bedenleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function addVariant($model_id, $color_id, $size_id, $stock_quantity, $is_active)
    {
        if (!$this->db) {
            return false; // Demo modunda ekleme devre dışı
        }
        $data = [
            'model_id' => $model_id,
            'color_id' => $color_id,
            'size_id' => $size_id,
            'stock_quantity' => $stock_quantity,
            'is_active' => $is_active
        ];
        return $this->createVariant($data);
    }


    public function getVariantsByProductAndColor($model_id, $color_id)
    {
        if (!$this->db) {
            return $this->getDemoVariantsByProductAndColor($model_id, $color_id);
        }
        try {
            $dbType = DatabaseFactory::getCurrentType();

            if ($dbType === 'supabase') {
                $select_query = '*,sizes(id,size_value,size_type)';
                $variants = $this->db->select(
                    'product_variants',
                    ['model_id' => intval($model_id), 'color_id' => intval($color_id)],
                    $select_query
                );

                if (!empty($variants)) {
                    foreach ($variants as &$variant) {
                        if (isset($variant['sizes'])) {
                            $variant['size_id'] = $variant['sizes']['id'];
                            $variant['size_value'] = $variant['sizes']['size_value'];
                            $variant['size_type'] = $variant['sizes']['size_type'];
                            unset($variant['sizes']);
                        }
                    }
                }
                return $variants;

            } else {

                $sql = "SELECT pv.*, s.id as size_id, s.size_value, s.size_type
                        FROM product_variants pv
                        LEFT JOIN sizes s ON pv.size_id = s.id
                        WHERE pv.model_id = :model_id AND pv.color_id = :color_id";

                $params = [
                    'model_id' => intval($model_id),
                    'color_id' => intval($color_id)
                ];

                return $this->db->executeRawSql($sql, $params);
            }
        } catch (Exception $e) {
            error_log("Ürün ve renk varyantları getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Demo renkler
     */
    private function getDemoColors()
    {
        return [
            [
                'id' => 1,
                'name' => 'Siyah',
                'hex_code' => '#000000'
            ],
            [
                'id' => 2,
                'name' => 'Beyaz',
                'hex_code' => '#FFFFFF'
            ],
            [
                'id' => 3,
                'name' => 'Kırmızı',
                'hex_code' => '#FF0000'
            ],
            [
                'id' => 4,
                'name' => 'Mavi',
                'hex_code' => '#0000FF'
            ],
            [
                'id' => 5,
                'name' => 'Yeşil',
                'hex_code' => '#008000'
            ],
            [
                'id' => 6,
                'name' => 'Gri',
                'hex_code' => '#808080'
            ],
            [
                'id' => 7,
                'name' => 'Kahverengi',
                'hex_code' => '#8B4513'
            ],
            [
                'id' => 8,
                'name' => 'Lacivert',
                'hex_code' => '#000080'
            ]
        ];
    }

    /**
     * Demo bedenler
     */
    private function getDemoSizes()
    {
        return [
            [
                'id' => 1,
                'size_value' => '36',
                'size_type' => 'EU'
            ],
            [
                'id' => 2,
                'size_value' => '37',
                'size_type' => 'EU'
            ],
            [
                'id' => 3,
                'size_value' => '38',
                'size_type' => 'EU'
            ],
            [
                'id' => 4,
                'size_value' => '39',
                'size_type' => 'EU'
            ],
            [
                'id' => 5,
                'size_value' => '40',
                'size_type' => 'EU'
            ],
            [
                'id' => 6,
                'size_value' => '41',
                'size_type' => 'EU'
            ],
            [
                'id' => 7,
                'size_value' => '42',
                'size_type' => 'EU'
            ],
            [
                'id' => 8,
                'size_value' => '43',
                'size_type' => 'EU'
            ],
            [
                'id' => 9,
                'size_value' => '44',
                'size_type' => 'EU'
            ],
            [
                'id' => 10,
                'size_value' => '45',
                'size_type' => 'EU'
            ]
        ];
    }

    /**
     * Demo ürün varyantları
     */
    private function getDemoProductVariants($model_id)
    {
        // Basit demo varyantları - gerçek uygulamada her ürün için farklı olurdu
        $variants = [
            [
                'id' => ($model_id * 10) + 1,
                'model_id' => $model_id,
                'color_id' => 1,
                'size_id' => 4,
                'color_name' => 'Siyah',
                'color_hex' => '#000000',
                'size_value' => '39',
                'size_type' => 'EU',
                'stock_quantity' => 15,
                'price' => 299.99,
                'is_active' => 1,
                'sku' => "PRD{$model_id}-C1-S4"
            ],
            [
                'id' => ($model_id * 10) + 2,
                'model_id' => $model_id,
                'color_id' => 1,
                'size_id' => 5,
                'color_name' => 'Siyah',
                'color_hex' => '#000000',
                'size_value' => '40',
                'size_type' => 'EU',
                'stock_quantity' => 20,
                'price' => 299.99,
                'is_active' => 1,
                'sku' => "PRD{$model_id}-C1-S5"
            ],
            [
                'id' => ($model_id * 10) + 3,
                'model_id' => $model_id,
                'color_id' => 2,
                'size_id' => 4,
                'color_name' => 'Beyaz',
                'color_hex' => '#FFFFFF',
                'size_value' => '39',
                'size_type' => 'EU',
                'stock_quantity' => 12,
                'price' => 299.99,
                'is_active' => 1,
                'sku' => "PRD{$model_id}-C2-S4"
            ],
            [
                'id' => ($model_id * 10) + 4,
                'model_id' => $model_id,
                'color_id' => 2,
                'size_id' => 5,
                'color_name' => 'Beyaz',
                'color_hex' => '#FFFFFF',
                'size_value' => '40',
                'size_type' => 'EU',
                'stock_quantity' => 18,
                'price' => 299.99,
                'is_active' => 1,
                'sku' => "PRD{$model_id}-C2-S5"
            ]
        ];

        return $variants;
    }

    /**
     * Demo varyant ID ile getir
     */
    private function getDemoVariantById($variant_id)
    {
        // Basit demo - gerçek uygulamada varyant ID'ye göre arama yapılırdu
        return [
            'id' => $variant_id,
            'model_id' => 1,
            'color_id' => 1,
            'size_id' => 4,
            'color_name' => 'Siyah',
            'color_hex' => '#000000',
            'size_value' => '39',
            'size_type' => 'EU',
            'stock_quantity' => 15,
            'price' => 299.99,
            'is_active' => 1,
            'sku' => "PRD1-C1-S4"
        ];
    }

    /**
     * Demo toplam stok
     */
    private function getDemoTotalStock($model_id)
    {
        $variants = $this->getDemoProductVariants($model_id);
        $total = 0;
        foreach ($variants as $variant) {
            if ($variant['is_active']) {
                $total += $variant['stock_quantity'];
            }
        }
        return $total;
    }

    /**
     * Demo ürün renkleri
     */
    private function getDemoProductColors($model_id)
    {
        return [
            [
                'id' => 1,
                'name' => 'Siyah',
                'hex_code' => '#000000'
            ],
            [
                'id' => 2,
                'name' => 'Beyaz',
                'hex_code' => '#FFFFFF'
            ]
        ];
    }

    /**
     * Demo ürün bedenleri
     */
    private function getDemoProductSizes($model_id)
    {
        return [
            [
                'id' => 4,
                'size_value' => '39',
                'size_type' => 'EU'
            ],
            [
                'id' => 5,
                'size_value' => '40',
                'size_type' => 'EU'
            ],
            [
                'id' => 6,
                'size_value' => '41',
                'size_type' => 'EU'
            ],
            [
                'id' => 7,
                'size_value' => '42',
                'size_type' => 'EU'
            ]
        ];
    }

    /**
     * Demo ürün ve renk varyantları
     */
    private function getDemoVariantsByProductAndColor($model_id, $color_id)
    {
        $all_variants = $this->getDemoProductVariants($model_id);
        return array_filter($all_variants, function($variant) use ($color_id) {
            return $variant['color_id'] == $color_id;
        });
    }
}


function variant_service()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new VariantService();
    }

    return $instance;
}



function get_all_colors()
{
    return variant_service()->getAllColors();
}


function get_all_sizes()
{
    return variant_service()->getAllSizes();
}


function get_product_variants_with_details($model_id)
{
    return variant_service()->getProductVariants($model_id);
}


function get_product_total_stock($model_id)
{
    return variant_service()->getTotalStock($model_id);
}


function get_product_colors($model_id)
{
    return variant_service()->getProductColors($model_id);
}


function get_product_sizes($model_id)
{
    return variant_service()->getProductSizes($model_id);
}
