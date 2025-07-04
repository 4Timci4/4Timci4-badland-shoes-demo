<?php
/**
 * Blog Servisi
 * 
 * Blog yazılarıyla ilgili veritabanı işlemlerini yönetir.
 */

class BlogService {
    private $supabase;

    public function __construct() {
        $this->supabase = supabase();
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
            $offset = ($page - 1) * $perPage;
            
            $query = '/rest/v1/blogs?select=*,count=exact&order=created_at.desc&limit=' . $perPage . '&offset=' . $offset;

            if ($category) {
                $query .= '&category=eq.' . urlencode($category);
            }

            if ($tag) {
                $query .= '&tags=cs.{"' . urlencode($tag) . '"}';
            }

            $response = $this->supabase->request($query, 'GET', [], ['Prefer: count=exact']);
            
            $countHeader = $response['headers']['content-range'] ?? '0/0';
            $total = (int) explode('/', $countHeader)[1];
            
            return [
                'posts' => $response['body'],
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        } catch (Exception $e) {
            error_log("Blog yazılarını getirme hatası: " . $e->getMessage());
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
            $response = $this->supabase->request('/rest/v1/blogs?id=eq.' . $id . '&select=*', 'GET');
            return $response['body'][0] ?? null;
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
     * @param int $limit Getirilecek yazı sayısı
     * @return array Benzer yazılar
     */
    public function get_related_posts($current_id, $category, $limit = 2) {
        try {
            $query = '/rest/v1/blogs?select=*&category=eq.' . urlencode($category) . '&id=neq.' . $current_id . '&limit=' . $limit . '&order=created_at.desc';
            $response = $this->supabase->request($query);
            return $response['body'];
        } catch (Exception $e) {
            error_log("Benzer yazıları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// Servisi global olarak kullanılabilir hale getirelim
function blogService() {
    return new BlogService();
}
