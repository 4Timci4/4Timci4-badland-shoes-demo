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
            // Önbelleği temizle
            $this->supabase->clearCache('blogs?select=*');
            
            // Veritabanından blog yazılarını çek
            $query = 'blogs?select=*&order=created_at.desc';
            $response = $this->supabase->request($query);
            
            if (!empty($response['body'])) {
                $allPosts = $response['body'];
                
                // Kategori filtreleme
                if ($category) {
                    $allPosts = array_filter($allPosts, function($post) use ($category) {
                        return $post['category'] === $category;
                    });
                }

                // Etiket filtreleme
                if ($tag) {
                    $allPosts = array_filter($allPosts, function($post) use ($tag) {
                        // Etiketler array formatında olabilir
                        $postTags = $post['tags'] ?? [];
                        if (is_string($postTags)) {
                            // PostgreSQL array formatından PHP array'e çevir
                            $postTags = str_replace(['{', '}'], ['', ''], $postTags);
                            $postTags = array_map('trim', explode(',', $postTags));
                        }
                        return in_array($tag, $postTags);
                    });
                }

                // Dizin anahtarlarını sıfırla
                $allPosts = array_values($allPosts);

                // Sayfalama
                $total = count($allPosts);
                $offset = ($page - 1) * $perPage;
                $pagedPosts = array_slice($allPosts, $offset, $perPage);

                return [
                    'posts' => $pagedPosts,
                    'total' => $total,
                    'pages' => ceil($total / $perPage)
                ];
            } else {
                error_log("Veritabanından blog yazıları alınamadı - boş response body");
            }
        } catch (Exception $e) {
            error_log("Blog yazıları getirme hatası: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
        
        // Veritabanından veri gelmezse boş sonuç döndür
        return [
            'posts' => [],
            'total' => 0,
            'pages' => 0
        ];
    }

    /**
     * ID'ye göre tek bir blog yazısını getirir.
     *
     * @param int $id Blog yazısı ID'si
     * @return array|null Blog yazısı veya bulunamazsa null
     */
    public function get_post_by_id($id) {
        try {
            $response = $this->supabase->request('blogs?id=eq.' . $id . '&select=*', 'GET');
            if (!empty($response['body'])) {
                return $response['body'][0];
            }
        } catch (Exception $e) {
            error_log("Blog yazısı getirme hatası: " . $e->getMessage());
        }
        
        return null;
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
            $query = 'blogs?select=*&category=eq.' . urlencode($category) . '&id=neq.' . $current_id . '&limit=' . $limit . '&order=created_at.desc';
            $response = $this->supabase->request($query);
            if (!empty($response['body'])) {
                return $response['body'];
            }
        } catch (Exception $e) {
            error_log("Benzer yazıları getirme hatası: " . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Tüm blog yazılarını getirir (Admin panel için)
     *
     * @param int $limit Maksimum yazı sayısı
     * @return array Blog yazıları
     */
    public function getAllBlogs($limit = 100) {
        try {
            $response = $this->supabase->request('blogs?select=*&order=created_at.desc&limit=' . $limit);
            if (!empty($response['body'])) {
                return $response['body'];
            }
        } catch (Exception $e) {
            error_log("Tüm blog yazılarını getirme hatası: " . $e->getMessage());
        }
        
        return [];
    }
}

// Servisi global olarak kullanılabilir hale getirelim
function blogService() {
    return new BlogService();
}
