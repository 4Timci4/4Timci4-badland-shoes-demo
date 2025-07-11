<?php
/**
 * Favori Servisi
 * 
 * Bu dosya, kullanıcıların favori ürün varyantlarını yönetmek için gerekli fonksiyonları içerir.
 * Ekleme, kaldırma, listeleme ve kontrol işlemlerini gerçekleştirir.
 */

require_once __DIR__ . '/../../lib/DatabaseFactory.php';
require_once __DIR__ . '/ProductQueryService.php';

/**
 * Favori servis sınıfı
 */
class FavoriteService {
    private $db;
    private $productQueryService;
    
    /**
     * FavoriteService sınıfını başlatır
     */
    public function __construct($db = null) {
        $this->db = $db ?: database();
        $this->productQueryService = new ProductQueryService($this->db);
    }
    
    /**
     * Bir ürün varyantını favorilere ekler
     * 
     * @param string $userId Kullanıcı ID
     * @param int $variantId Ürün varyant ID
     * @param int|null $colorId Renk ID
     * @return array Sonuç
     */
    public function addFavorite($userId, $variantId, $colorId = null) {
        try {
            // Önce varyantın var olup olmadığını kontrol et
            $variant = $this->productQueryService->getVariantById($variantId);
            if (empty($variant)) {
                return [
                    'success' => false,
                    'message' => 'Belirtilen varyant bulunamadı'
                ];
            }
            
            // Favorinin zaten var olup olmadığını kontrol et
            if ($this->isFavorite($userId, $variantId)) {
                return [
                    'success' => true,
                    'message' => 'Ürün zaten favorilerinizde'
                ];
            }

            // Favorilere ekle
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
    
    /**
     * Bir ürün varyantını favorilerden kaldırır
     * 
     * @param string $userId Kullanıcı ID
     * @param int $variantId Ürün varyant ID
     * @return array Sonuç
     */
    public function removeFavorite($userId, $variantId) {
        try {
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
    
    /**
     * Bir kullanıcının tüm favori ürünlerini getirir
     *
     * @param string $userId Kullanıcı ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Favori ürünler
     */
    public function getFavorites($userId, $limit = 20, $offset = 0) {
        try {
            // Önce görünüm üzerinden performanslı şekilde almayı dene
            $result = $this->getFavoritesFromView($userId, $limit, $offset);
            
            // Eğer görünüm kullanımı başarısız olursa, eski yöntemle devam et
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
    
    /**
     * Görünüm (view) kullanarak favori ürünleri getirir - performanslı yöntem
     *
     * @param string $userId Kullanıcı ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Favori ürünler
     */
    private function getFavoritesFromView($userId, $limit = 20, $offset = 0) {
        try {
            // Favorileri view üzerinden tek sorguda al
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
            
            // Varyantları ve ilgili ürün bilgilerini düzenle
            $variants = [];
            foreach ($favorites_view as $fav) {
                // Ürün verisini ayır
                $product = [
                    'id' => $fav['product_id'],
                    'name' => $fav['product_name'],
                    'description' => $fav['product_description'],
                    'category_name' => $fav['category_name'],
                    'variant_image_url' => $fav['variant_image_url'],
                    'image_url' => $fav['product_image_url']
                ];
                
                // Varyant verisini oluştur
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
            
            // Toplam favori sayısını al
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
    
    /**
     * Eski yöntem ile favori ürünleri getirir (fallback mekanizması)
     *
     * @param string $userId Kullanıcı ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Favori ürünler
     */
    private function getFavoritesLegacy($userId, $limit = 20, $offset = 0) {
        try {
            // Favorileri al
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
            
            // Varyantları ve ilgili ürün bilgilerini al
            $variants = [];
            foreach ($favorites as $favorite) {
                $variant = $this->productQueryService->getVariantById($favorite['variant_id']);
                if (!empty($variant)) {
                    // Ürün modelini al
                    $product = $this->productQueryService->getProductModel($variant['model_id']);
                    if (!empty($product)) {
                        // Varyanta özgü görsel bilgilerini al
                        $color_id = $favorite['color_id'] ?? $variant['color_id'] ?? null;
                        if ($color_id) {
                            $variantImages = $this->productQueryService->getVariantImages($variant['model_id'], $color_id);
                            if (!empty($variantImages)) {
                                // Varyant için ilk görseli al
                                $product['variant_image_url'] = $variantImages[0]['image_url'];
                            }
                        }
                        
                        // Favori, varyant ve ürün bilgilerini birleştir
                        $combined_data = array_merge($variant, $favorite);
                        $combined_data['product'] = $product;
                        $variants[] = $combined_data;
                    }
                }
            }
            
            // Toplam favori sayısını al
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
    
    /**
     * Bir ürün varyantının favori olup olmadığını kontrol eder
     * 
     * @param string $userId Kullanıcı ID
     * @param int $variantId Ürün varyant ID
     * @return bool Favori durumu
     */
    public function isFavorite($userId, $variantId) {
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
    
    /**
     * Bir kullanıcının favori varyant ID'lerini getirir
     * 
     * @param string $userId Kullanıcı ID
     * @return array Favori varyant ID'leri
     */
    public function getFavoriteVariantIds($userId) {
        try {
            $favorites = $this->db->select('favorites', [
                'user_id' => $userId
            ], 'variant_id');
            
            return array_map(function($favorite) {
                return $favorite['variant_id'];
            }, $favorites);
        } catch (Exception $e) {
            error_log("FavoriteService::getFavoriteVariantIds - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bir kullanıcının belirli ürün modellerine ait favori varyantlarını getirir
     * 
     * @param string $userId Kullanıcı ID
     * @param array $modelIds Ürün model ID'leri
     * @return array Favori varyantlar
     */
    public function getFavoriteVariantsByModels($userId, $modelIds) {
        try {
            // Önce kullanıcının tüm favori varyant ID'lerini al
            $favoriteVariantIds = $this->getFavoriteVariantIds($userId);
            if (empty($favoriteVariantIds)) {
                return [];
            }
            
            // Bu varyantlar arasından belirli modellere ait olanları filtrele
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

/**
 * FavoriteService sınıfı singleton örneği
 */
function favorite_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new FavoriteService();
    }
    
    return $instance;
}