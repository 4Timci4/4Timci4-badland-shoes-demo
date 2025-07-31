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
        if (!$this->db) {
            return $this->getDemoColors();
        }
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
        if (!$this->db) {
            return $this->getDemoColorById($color_id);
        }
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
        if (!$this->db) {
            return $this->getDemoSizes();
        }
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
        if (!$this->db) {
            return $this->getDemoSizeById($size_id);
        }
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
        if (!$this->db) {
            return $this->getDemoColorsWithUsageCounts();
        }
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
        if (!$this->db) {
            return $this->getDemoSizesWithUsageCounts();
        }
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

    /**
     * Demo renkler
     */
    private function getDemoColors()
    {
        return [
            [
                'id' => 1,
                'name' => 'Siyah',
                'hex_code' => '#000000',
                'is_active' => 1
            ],
            [
                'id' => 2,
                'name' => 'Beyaz',
                'hex_code' => '#FFFFFF',
                'is_active' => 1
            ],
            [
                'id' => 3,
                'name' => 'Kırmızı',
                'hex_code' => '#FF0000',
                'is_active' => 1
            ],
            [
                'id' => 4,
                'name' => 'Mavi',
                'hex_code' => '#0000FF',
                'is_active' => 1
            ],
            [
                'id' => 5,
                'name' => 'Yeşil',
                'hex_code' => '#008000',
                'is_active' => 1
            ],
            [
                'id' => 6,
                'name' => 'Gri',
                'hex_code' => '#808080',
                'is_active' => 1
            ],
            [
                'id' => 7,
                'name' => 'Kahverengi',
                'hex_code' => '#8B4513',
                'is_active' => 1
            ],
            [
                'id' => 8,
                'name' => 'Lacivert',
                'hex_code' => '#000080',
                'is_active' => 1
            ],
            [
                'id' => 9,
                'name' => 'Pembe',
                'hex_code' => '#FFC0CB',
                'is_active' => 1
            ],
            [
                'id' => 10,
                'name' => 'Turuncu',
                'hex_code' => '#FFA500',
                'is_active' => 1
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
                'size_type' => 'EU',
                'display_order' => 1,
                'is_active' => 1
            ],
            [
                'id' => 2,
                'size_value' => '37',
                'size_type' => 'EU',
                'display_order' => 2,
                'is_active' => 1
            ],
            [
                'id' => 3,
                'size_value' => '38',
                'size_type' => 'EU',
                'display_order' => 3,
                'is_active' => 1
            ],
            [
                'id' => 4,
                'size_value' => '39',
                'size_type' => 'EU',
                'display_order' => 4,
                'is_active' => 1
            ],
            [
                'id' => 5,
                'size_value' => '40',
                'size_type' => 'EU',
                'display_order' => 5,
                'is_active' => 1
            ],
            [
                'id' => 6,
                'size_value' => '41',
                'size_type' => 'EU',
                'display_order' => 6,
                'is_active' => 1
            ],
            [
                'id' => 7,
                'size_value' => '42',
                'size_type' => 'EU',
                'display_order' => 7,
                'is_active' => 1
            ],
            [
                'id' => 8,
                'size_value' => '43',
                'size_type' => 'EU',
                'display_order' => 8,
                'is_active' => 1
            ],
            [
                'id' => 9,
                'size_value' => '44',
                'size_type' => 'EU',
                'display_order' => 9,
                'is_active' => 1
            ],
            [
                'id' => 10,
                'size_value' => '45',
                'size_type' => 'EU',
                'display_order' => 10,
                'is_active' => 1
            ]
        ];
    }

    /**
     * Demo renk detayı
     */
    private function getDemoColorById($color_id)
    {
        $colors = $this->getDemoColors();
        foreach ($colors as $color) {
            if ($color['id'] == $color_id) {
                return $color;
            }
        }
        return [];
    }

    /**
     * Demo beden detayı
     */
    private function getDemoSizeById($size_id)
    {
        $sizes = $this->getDemoSizes();
        foreach ($sizes as $size) {
            if ($size['id'] == $size_id) {
                return $size;
            }
        }
        return [];
    }

    /**
     * Demo renkler kullanım sayılarıyla
     */
    private function getDemoColorsWithUsageCounts()
    {
        $colors = $this->getDemoColors();
        $usage_counts = [
            1 => 5, // Siyah
            2 => 4, // Beyaz
            3 => 3, // Kırmızı
            4 => 3, // Mavi
            5 => 2, // Yeşil
            6 => 2, // Gri
            7 => 1, // Kahverengi
            8 => 2, // Lacivert
            9 => 1, // Pembe
            10 => 1  // Turuncu
        ];

        foreach ($colors as &$color) {
            $color['usage_count'] = $usage_counts[$color['id']] ?? 0;
        }

        return $colors;
    }

    /**
     * Demo bedenler kullanım sayılarıyla
     */
    private function getDemoSizesWithUsageCounts()
    {
        $sizes = $this->getDemoSizes();
        $usage_counts = [
            1 => 2,  // 36
            2 => 3,  // 37
            3 => 4,  // 38
            4 => 5,  // 39
            5 => 6,  // 40
            6 => 5,  // 41
            7 => 4,  // 42
            8 => 3,  // 43
            9 => 2,  // 44
            10 => 1  // 45
        ];

        foreach ($sizes as &$size) {
            $size['usage_count'] = $usage_counts[$size['id']] ?? 0;
        }

        return $sizes;
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
