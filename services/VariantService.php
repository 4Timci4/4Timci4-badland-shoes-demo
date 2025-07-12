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
        try {
            return $this->db->select('colors', [], '*', ['order' => 'name ASC']);
        } catch (Exception $e) {
            error_log("Renkleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getAllSizes()
    {
        try {
            return $this->db->select('sizes', [], '*', ['order' => 'size_value ASC']);
        } catch (Exception $e) {
            error_log("Bedenleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getProductVariants($model_id)
    {
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
