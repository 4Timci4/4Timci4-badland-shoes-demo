<?php

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/ProductQueryService.php';


class FavoriteService
{
    private $db;
    private $productQueryService;


    public function __construct($db = null)
    {
        $this->db = $db ?: database();
        $this->productQueryService = new ProductQueryService($this->db);
    }


    public function addFavorite($userId, $variantId, $colorId = null)
    {
        try {

            $variant = $this->productQueryService->getVariantById($variantId);
            if (empty($variant)) {
                return [
                    'success' => false,
                    'message' => 'Belirtilen varyant bulunamadı'
                ];
            }


            if ($this->isFavorite($userId, $variantId)) {
                return [
                    'success' => true,
                    'message' => 'Ürün zaten favorilerinizde'
                ];
            }


            $userCheck = $this->db->select('users', ['id' => $userId], 'id', ['limit' => 1]);
            if (empty($userCheck)) {
                error_log("FavoriteService::addFavorite - User ID not found in database: $userId");
                return [
                    'success' => false,
                    'message' => 'Kullanıcı bilgileri bulunamadı. Lütfen tekrar giriş yapın.',
                    'error_code' => 'user_not_found',
                    'redirect' => 'logout.php'
                ];
            }


            $result = $this->db->insert('favorites', [
                'user_id' => $userId,
                'variant_id' => $variantId,
                'color_id' => $colorId
            ]);

            if (!empty($result)) {
                return [
                    'success' => true,
                    'message' => 'Ürün favorilere eklendi'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ürün favorilere eklenirken bir hata oluştu'
                ];
            }
        } catch (Exception $e) {
            error_log("FavoriteService::addFavorite - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }


    public function removeFavorite($userId, $variantId)
    {
        try {

            $userCheck = $this->db->select('users', ['id' => $userId], 'id', ['limit' => 1]);
            if (empty($userCheck)) {
                error_log("FavoriteService::removeFavorite - User ID not found in database: $userId");
                return [
                    'success' => false,
                    'message' => 'Kullanıcı bilgileri bulunamadı. Lütfen tekrar giriş yapın.',
                    'error_code' => 'user_not_found',
                    'redirect' => 'logout.php'
                ];
            }

            $result = $this->db->delete('favorites', [
                'user_id' => $userId,
                'variant_id' => $variantId
            ]);

            if (isset($result['affected_rows']) && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Ürün favorilerden kaldırıldı'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Ürün favorilerden kaldırılırken bir hata oluştu'
                ];
            }
        } catch (Exception $e) {
            error_log("FavoriteService::removeFavorite - " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }


    public function getFavorites($userId, $limit = 20, $offset = 0)
    {
        try {

            $result = $this->getFavoritesFromView($userId, $limit, $offset);


            if ($result['error']) {
                return $this->getFavoritesLegacy($userId, $limit, $offset);
            }

            return $result;
        } catch (Exception $e) {
            error_log("FavoriteService::getFavorites - " . $e->getMessage());
            return [
                'favorites' => [],
                'total' => 0,
                'error' => $e->getMessage()
            ];
        }
    }


    private function getFavoritesFromView($userId, $limit = 20, $offset = 0)
    {
        try {

            $favorites_view = $this->db->select('favorites_view', [
                'user_id' => $userId
            ], '*', [
                'limit' => $limit,
                'offset' => $offset,
                'order' => 'favorite_added_at DESC'
            ]);

            if (empty($favorites_view)) {
                return [
                    'favorites' => [],
                    'total' => 0,
                    'error' => false
                ];
            }


            $variants = [];
            foreach ($favorites_view as $fav) {

                $product = [
                    'id' => $fav['product_id'],
                    'name' => $fav['product_name'],
                    'description' => $fav['product_description'],
                    'category_name' => $fav['category_name'] ?? null,
                    'variant_image_url' => $fav['variant_image_url'],
                    'image_url' => $fav['product_image_url']
                ];


                $variant = [
                    'id' => $fav['variant_id'],
                    'model_id' => $fav['model_id'],
                    'size_id' => $fav['size_id'],
                    'color_id' => $fav['color_id'],
                    'stock_quantity' => $fav['stock_quantity'],
                    'color_name' => $fav['color_name'],
                    'color_hex' => $fav['color_hex'],
                    'size_value' => $fav['size_value'],
                    'created_at' => $fav['favorite_added_at'],
                    'product' => $product
                ];

                $variants[] = $variant;
            }


            $total = $this->db->count('favorites', [
                'user_id' => $userId
            ]);

            return [
                'favorites' => $variants,
                'total' => $total,
                'error' => false
            ];
        } catch (Exception $e) {
            error_log("FavoriteService::getFavoritesFromView - " . $e->getMessage());
            return [
                'favorites' => [],
                'total' => 0,
                'error' => true,
                'error_message' => $e->getMessage()
            ];
        }
    }


    private function getFavoritesLegacy($userId, $limit = 20, $offset = 0)
    {
        try {

            $favorites = $this->db->select('favorites', [
                'user_id' => $userId
            ], '*', [
                'limit' => $limit,
                'offset' => $offset,
                'order' => 'created_at DESC'
            ]);

            if (empty($favorites)) {
                return [
                    'favorites' => [],
                    'total' => 0
                ];
            }


            $variants = [];
            foreach ($favorites as $favorite) {
                $variant = $this->productQueryService->getVariantById($favorite['variant_id']);
                if (!empty($variant)) {

                    $product = $this->productQueryService->getProductModel($variant['model_id']);
                    if (!empty($product)) {

                        $color_id = $favorite['color_id'] ?? $variant['color_id'] ?? null;
                        if ($color_id) {
                            $variantImages = $this->productQueryService->getVariantImages($variant['model_id'], $color_id);
                            if (!empty($variantImages)) {

                                $product['variant_image_url'] = $variantImages[0]['image_url'];
                            }
                        }


                        $combined_data = array_merge($variant, $favorite);
                        $combined_data['product'] = $product;
                        $variants[] = $combined_data;
                    }
                }
            }


            $total = $this->db->count('favorites', [
                'user_id' => $userId
            ]);

            return [
                'favorites' => $variants,
                'total' => $total
            ];
        } catch (Exception $e) {
            error_log("FavoriteService::getFavorites - " . $e->getMessage());
            return [
                'favorites' => [],
                'total' => 0,
                'error' => $e->getMessage()
            ];
        }
    }


    public function isFavorite($userId, $variantId)
    {
        try {
            $result = $this->db->select('favorites', [
                'user_id' => $userId,
                'variant_id' => $variantId
            ], 'id');

            return !empty($result);
        } catch (Exception $e) {
            error_log("FavoriteService::isFavorite - " . $e->getMessage());
            return false;
        }
    }


    public function getFavoriteVariantIds($userId)
    {
        try {
            $favorites = $this->db->select('favorites', [
                'user_id' => $userId
            ], 'variant_id');

            return array_map(function ($favorite) {
                return $favorite['variant_id'];
            }, $favorites);
        } catch (Exception $e) {
            error_log("FavoriteService::getFavoriteVariantIds - " . $e->getMessage());
            return [];
        }
    }


    public function getFavoriteVariantsByModels($userId, $modelIds)
    {
        try {

            $favoriteVariantIds = $this->getFavoriteVariantIds($userId);
            if (empty($favoriteVariantIds)) {
                return [];
            }


            $variants = [];
            foreach ($favoriteVariantIds as $variantId) {
                $variant = $this->productQueryService->getVariantById($variantId);
                if (!empty($variant) && in_array($variant['model_id'], $modelIds)) {
                    $variants[] = $variant;
                }
            }

            return $variants;
        } catch (Exception $e) {
            error_log("FavoriteService::getFavoriteVariantsByModels - " . $e->getMessage());
            return [];
        }
    }
}


function favorite_service()
{
    static $instance = null;

    if ($instance === null) {
        $instance = new FavoriteService();
    }

    return $instance;
}