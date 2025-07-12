<?php

require_once __DIR__ . '/../../lib/DatabaseFactory.php';

class ProductFilterService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }


    public function getCategoryIdsBySlugs($category_slugs)
    {
        try {
            $slugs = is_array($category_slugs) ? $category_slugs : [$category_slugs];
            $category_ids = [];

            foreach ($slugs as $slug) {
                $categories = $this->db->select('categories', ['slug' => $slug], 'id');
                if (!empty($categories)) {
                    $category_ids[] = $categories[0]['id'];
                }
            }

            return $category_ids;
        } catch (Exception $e) {
            error_log("Kategori ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getGenderIdsBySlugs($gender_slugs)
    {
        try {
            $slugs = is_array($gender_slugs) ? $gender_slugs : [$gender_slugs];
            $gender_ids = [];

            foreach ($slugs as $slug) {
                $genders = $this->db->select('genders', ['slug' => $slug], 'id');
                if (!empty($genders)) {
                    $gender_ids[] = $genders[0]['id'];
                }
            }

            return $gender_ids;
        } catch (Exception $e) {
            error_log("Cinsiyet ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getProductIdsByCategories($category_ids)
    {
        try {
            if (empty($category_ids)) {
                return [];
            }

            $relations = $this->db->select('product_categories', ['category_id' => ['IN', $category_ids]], 'product_id');
            return array_unique(array_column($relations, 'product_id'));
        } catch (Exception $e) {
            error_log("Kategori ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getProductIdsByGenders($gender_ids)
    {
        try {
            if (empty($gender_ids)) {
                return [];
            }

            $relations = $this->db->select('product_genders', ['gender_id' => ['IN', $gender_ids]], 'product_id');
            return array_unique(array_column($relations, 'product_id'));
        } catch (Exception $e) {
            error_log("Cinsiyet ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getFilteredProductIds($filters)
    {
        try {
            $product_ids = null;


            if (!empty($filters['categories'])) {
                $category_slugs = is_array($filters['categories']) ? $filters['categories'] : [$filters['categories']];
                $category_ids = $this->getCategoryIdsBySlugs($category_slugs);

                if (!empty($category_ids)) {
                    $category_product_ids = $this->getProductIdsByCategories($category_ids);
                    $product_ids = $this->intersectProductIds($product_ids, $category_product_ids);

                    if (empty($product_ids)) {
                        return [];
                    }
                } else {
                    return [];
                }
            }


            if (!empty($filters['genders'])) {
                $gender_slugs = is_array($filters['genders']) ? $filters['genders'] : [$filters['genders']];
                $gender_ids = $this->getGenderIdsBySlugs($gender_slugs);

                if (!empty($gender_ids)) {
                    $gender_product_ids = $this->getProductIdsByGenders($gender_ids);
                    $product_ids = $this->intersectProductIds($product_ids, $gender_product_ids);

                    if (empty($product_ids)) {
                        return [];
                    }
                } else {
                    return [];
                }
            }


            return $product_ids;

        } catch (Exception $e) {
            error_log("Filtrelenmiş ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getFeaturedProductIds()
    {
        try {
            $products = $this->db->select('product_models', ['is_featured' => 1], 'id');
            return array_column($products, 'id');
        } catch (Exception $e) {
            error_log("Öne çıkan ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }



    private function intersectProductIds($existing_ids, $new_ids)
    {
        if ($existing_ids === null) {
            return $new_ids;
        }

        return array_intersect($existing_ids, $new_ids);
    }


    public function getCategoryBySlug($category_slug)
    {
        try {
            $categories = $this->db->select('categories', ['slug' => $category_slug], '*', ['limit' => 1]);
            return !empty($categories) ? $categories[0] : null;
        } catch (Exception $e) {
            error_log("Kategori slug getirme hatası: " . $e->getMessage());
            return null;
        }
    }


    public function getGenderBySlug($gender_slug)
    {
        try {
            $genders = $this->db->select('genders', ['slug' => $gender_slug], '*', ['limit' => 1]);
            return !empty($genders) ? $genders[0] : null;
        } catch (Exception $e) {
            error_log("Cinsiyet slug getirme hatası: " . $e->getMessage());
            return null;
        }
    }


    public function getActiveCategories()
    {
        try {
            return $this->db->select('categories', [], '*', ['order' => 'name ASC']);
        } catch (Exception $e) {
            error_log("Aktif kategoriler getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getActiveGenders()
    {
        try {
            return $this->db->select('genders', [], '*', ['order' => 'name ASC']);
        } catch (Exception $e) {
            error_log("Aktif cinsiyetler getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}


function product_filter_service()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new ProductFilterService();
    }

    return $instance;
}
