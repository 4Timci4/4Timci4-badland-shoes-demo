<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class AttributeService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }


    public function getAllColors()
    {
        try {
            return $this->db->select('colors', [], '*', ['order' => 'id ASC']);
        } catch (Exception $e) {
            error_log("Renkleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    public function createColor($data)
    {
        try {
            $result = $this->db->insert('colors', $data);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Renk oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }

    public function updateColor($color_id, $data)
    {
        try {
            $result = $this->db->update('colors', $data, ['id' => intval($color_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Renk güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function deleteColor($color_id)
    {
        try {
            $usage_count = $this->db->count('product_variants', ['color_id' => intval($color_id)]);

            if ($usage_count > 0) {
                return false;
            }

            $result = $this->db->delete('colors', ['id' => intval($color_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Renk silme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function getColorById($color_id)
    {
        try {
            $result = $this->db->select('colors', ['id' => intval($color_id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : [];
        } catch (Exception $e) {
            error_log("Renk getirme hatası: " . $e->getMessage());
            return [];
        }
    }




    public function getAllSizes()
    {
        try {
            return $this->db->select('sizes', [], '*', ['order' => 'display_order ASC, id ASC']);
        } catch (Exception $e) {
            error_log("Bedenleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function createSize($data)
    {
        try {

            if (isset($data['name'])) {
                $data['size_value'] = $data['name'];
                unset($data['name']);
            }


            if (!isset($data['size_type'])) {
                $data['size_type'] = 'EU';
            }

            $result = $this->db->insert('sizes', $data);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Beden oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }


    public function updateSize($size_id, $data)
    {
        try {

            if (isset($data['name'])) {
                $data['size_value'] = $data['name'];
                unset($data['name']);
            }

            $result = $this->db->update('sizes', $data, ['id' => intval($size_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Beden güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function deleteSize($size_id)
    {
        try {

            $usage_count = $this->db->count('product_variants', ['size_id' => intval($size_id)]);

            if ($usage_count > 0) {
                return false;
            }

            $result = $this->db->delete('sizes', ['id' => intval($size_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Beden silme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function getSizeById($size_id)
    {
        try {
            $result = $this->db->select('sizes', ['id' => intval($size_id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : [];
        } catch (Exception $e) {
            error_log("Beden getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function updateSizeOrder($order_data)
    {
        try {
            foreach ($order_data as $size_id => $order) {
                $this->db->update('sizes', ['display_order' => intval($order)], ['id' => intval($size_id)]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Beden sıralama güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }




    public function getColorUsageCount($color_id)
    {
        try {
            return $this->db->count('product_variants', ['color_id' => intval($color_id)]);
        } catch (Exception $e) {
            error_log("Renk kullanım sayısı getirme hatası: " . $e->getMessage());
            return 0;
        }
    }


    public function getSizeUsageCount($size_id)
    {
        try {
            return $this->db->count('product_variants', ['size_id' => intval($size_id)]);
        } catch (Exception $e) {
            error_log("Beden kullanım sayısı getirme hatası: " . $e->getMessage());
            return 0;
        }
    }


    public function getColorsWithUsageCounts()
    {
        try {

            $colors = $this->getAllColors();

            if (empty($colors)) {
                return [];
            }


            $variants = $this->db->select('product_variants', [], 'color_id');


            $usage_counts = [];
            foreach ($variants as $variant) {
                $color_id = $variant['color_id'];
                if (!isset($usage_counts[$color_id])) {
                    $usage_counts[$color_id] = 0;
                }
                $usage_counts[$color_id]++;
            }


            foreach ($colors as &$color) {
                $color['usage_count'] = $usage_counts[$color['id']] ?? 0;
            }

            return $colors;
        } catch (Exception $e) {
            error_log("Renkler ve kullanım sayıları getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getSizesWithUsageCounts()
    {
        try {

            $sizes = $this->getAllSizes();

            if (empty($sizes)) {
                return [];
            }

            $variants = $this->db->select('product_variants', [], 'size_id');

            $usage_counts = [];
            foreach ($variants as $variant) {
                $size_id = $variant['size_id'];
                if (!isset($usage_counts[$size_id])) {
                    $usage_counts[$size_id] = 0;
                }
                $usage_counts[$size_id]++;
            }


            foreach ($sizes as &$size) {
                $size['usage_count'] = $usage_counts[$size['id']] ?? 0;
            }

            return $sizes;
        } catch (Exception $e) {
            error_log("Bedenler ve kullanım sayıları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}


function attribute_service()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new AttributeService();
    }

    return $instance;
}



function get_all_colors()
{
    return attribute_service()->getAllColors();
}


function get_all_sizes()
{
    return attribute_service()->getAllSizes();
}


function create_color($data)
{
    return attribute_service()->createColor($data);
}


function create_size($data)
{
    return attribute_service()->createSize($data);
}


function update_color($color_id, $data)
{
    return attribute_service()->updateColor($color_id, $data);
}


function update_size($size_id, $data)
{
    return attribute_service()->updateSize($size_id, $data);
}


function delete_color($color_id)
{
    return attribute_service()->deleteColor($color_id);
}


function delete_size($size_id)
{
    return attribute_service()->deleteSize($size_id);
}
