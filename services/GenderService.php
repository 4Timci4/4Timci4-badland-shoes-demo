<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class GenderService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }

    public function getAllGenders()
    {
        try {
            return $this->db->select('genders', [], '*', ['order' => 'id ASC']);
        } catch (Exception $e) {
            error_log("Tüm cinsiyetleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    public function getGenderBySlug($slug)
    {
        try {
            $result = $this->db->select('genders', ['slug' => $slug], '*', ['limit' => 1]);

            if (!empty($result)) {
                return $result[0];
            }

            return [];
        } catch (Exception $e) {
            error_log("Cinsiyet getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getGenderById($gender_id)
    {
        try {
            $result = $this->db->select('genders', ['id' => intval($gender_id)], '*', ['limit' => 1]);

            if (!empty($result)) {
                return $result[0];
            }

            return [];
        } catch (Exception $e) {
            error_log("Cinsiyet getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    public function getProductGenders($product_id)
    {
        try {
            $genders = $this->db->selectWithJoins('product_genders', [
                [
                    'type' => 'INNER',
                    'table' => 'genders',
                    'condition' => 'product_genders.gender_id = genders.id'
                ]
            ], ['product_genders.product_id' => intval($product_id)], 'genders.id, genders.name, genders.slug');

            return $genders;
        } catch (Exception $e) {
            error_log("Ürün cinsiyetleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getProductGenderIds($product_id)
    {
        try {
            $gender_relations = $this->db->select('product_genders', ['product_id' => intval($product_id)], 'gender_id');
            return array_column($gender_relations, 'gender_id');
        } catch (Exception $e) {
            error_log("Ürün cinsiyet ID'leri getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    public function updateProductGenders($product_id, $gender_ids)
    {
        try {
            $this->db->delete('product_genders', ['product_id' => intval($product_id)]);

            foreach ($gender_ids as $gender_id) {
                $data = [
                    'product_id' => intval($product_id),
                    'gender_id' => intval($gender_id)
                ];

                $this->db->insert('product_genders', $data);
            }

            return true;
        } catch (Exception $e) {
            error_log("Ürün cinsiyetleri güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function createGender($data)
    {
        try {
            $result = $this->db->insert('genders', $data, ['returning' => true]);
            return !empty($result);
        } catch (Exception $e) {
            error_log("GenderService::createGender - Exception: " . $e->getMessage());
            return false;
        }
    }


    public function updateGender($gender_id, $data)
    {
        try {
            $result = $this->db->update('genders', $data, ['id' => intval($gender_id)]);
            return !empty($result);
        } catch (Exception $e) {
            error_log("Cinsiyet güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function deleteGender($gender_id)
    {
        try {

            $product_count = $this->db->count('product_genders', ['gender_id' => intval($gender_id)]);

            if ($product_count > 0) {
                return false;
            }

            $result = $this->db->delete('genders', ['id' => intval($gender_id)]);
            return !empty($result);
        } catch (Exception $e) {
            error_log("Cinsiyet silme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function generateSlug($text)
    {

        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'I', 'O', 'S', 'U'];
        $text = str_replace($turkish, $english, $text);


        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');

        return $text;
    }


    public function getGenderIdsBySlug($slugs)
    {
        if (empty($slugs)) {
            return [];
        }

        if (!is_array($slugs)) {
            $slugs = [$slugs];
        }

        try {
            $genders = $this->db->select(
                'genders',
                ['slug' => ['IN', $slugs]],
                'id, slug'
            );

            return array_column($genders, 'id');
        } catch (Exception $e) {
            error_log("GenderService::getGenderIdsBySlug Error: " . $e->getMessage());
            return [];
        }
    }


    public function getGendersWithProductCounts()
    {
        try {

            return $this->db->select('gender_product_counts', [], '*', ['order' => 'name ASC']);
        } catch (Exception $e) {
            error_log("Error getting genders with product counts from view: " . $e->getMessage());
            return [];
        }
    }
}


function gender_service()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new GenderService();
    }

    return $instance;
}


function get_genders()
{
    return gender_service()->getAllGenders();
}


function get_gender_by_slug($slug)
{
    return gender_service()->getGenderBySlug($slug);
}


function get_product_genders($product_id)
{
    return gender_service()->getProductGenders($product_id);
}
