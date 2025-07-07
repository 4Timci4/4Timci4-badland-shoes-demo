<?php
/**
 * Blog Servisi
 * 
 * Blog yazılarıyla ilgili veritabanı işlemlerini yönetir.
 */

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class BlogService {
    private $db;

    public function __construct() {
        $this->db = database();
    }

    /**
     * Tüm blog yazılarını sayfalama ile getirir.
     *
     * @param int $page Mevcut sayfa numarası
     * @param int $perPage Sayfa başına yazı sayısı
     * @param string|null $category Kategoriye göre filtrele
     * @param string|null $tag Etikete göre filtrele
     * @return array Blog yazıları ve toplam sayfa sayısı
     */
    public function get_posts($page = 1, $perPage = 6, $category = null, $tag = null) {
        try {
            $conditions = [];
            if ($category) {
                $conditions['category'] = $category;
            }
            if ($tag) {
                // Bu kısım database'e göre uyarlanmalı (örn: JSONB, text array)
                // Şimdilik basit bir LIKE ile arama yapalım
                $conditions['tags'] = ['LIKE', "%{$tag}%"];
            }

            $total = $this->db->count('blogs', $conditions);
            $offset = ($page - 1) * $perPage;
            
            $posts = $this->db->select('blogs', $conditions, '*', [
                'order' => 'created_at DESC',
                'limit' => $perPage,
                'offset' => $offset
            ]);

            return [
                'posts' => $posts,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (Exception $e) {
            error_log("Blog yazıları getirme hatası: " . $e->getMessage());
            return ['posts' => [], 'total' => 0, 'pages' => 0];
        }
    }

    /**
     * ID'ye göre tek bir blog yazısını getirir.
     *
     * @param int $id Blog yazısı ID'si
     * @return array|null Blog yazısı veya bulunamazsa null
     */
    public function get_post_by_id($id) {
        try {
            $result = $this->db->select('blogs', ['id' => intval($id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Blog yazısı getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Benzer yazıları getirir (aynı kategorideki).
     *
     * @param int $current_id Mevcut yazı ID'si (hariç tutmak için)
     * @param string $category Kategori
     * @param int $limit Getirilecek yaz sayısı
     * @return array Benzer yazılar
     */
    public function get_related_posts($current_id, $category, $limit = 2) {
        try {
            return $this->db->select('blogs', [
                'category' => $category,
                'id' => ['!=', intval($current_id)]
            ], '*', ['order' => 'created_at DESC', 'limit' => $limit]);
        } catch (Exception $e) {
            error_log("Benzer yazıları getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tüm blog yazılarını getirir (Admin panel için)
     *
     * @param int $limit Maksimum yazı sayısı
     * @return array Blog yazıları
     */
    public function getAllBlogs($limit = 100) {
        try {
            return $this->db->select('blogs', [], '*', ['order' => 'created_at DESC', 'limit' => $limit]);
        } catch (Exception $e) {
            error_log("Tüm blog yazılarını getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// Servisi global olarak kullanılabilir hale getirelim
function blogService() {
    return new BlogService();
}
