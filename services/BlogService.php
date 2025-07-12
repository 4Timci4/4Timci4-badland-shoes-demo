<?php


require_once __DIR__ . '/../lib/DatabaseFactory.php';

class BlogService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }


    public function get_posts($page = 1, $perPage = 6, $category = null, $tag = null)
    {
        try {
            $conditions = [];
            if ($category) {
                $conditions['category'] = $category;
            }
            if ($tag) {


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


    public function get_post_by_id($id)
    {
        try {
            $result = $this->db->select('blogs', ['id' => intval($id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Blog yazısı getirme hatası: " . $e->getMessage());
            return null;
        }
    }


    public function get_related_posts($current_id, $category, $limit = 2)
    {
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


    public function getAllBlogs($limit = 100)
    {
        try {
            return $this->db->select('blogs', [], '*', ['order' => 'created_at DESC', 'limit' => $limit]);
        } catch (Exception $e) {
            error_log("Tüm blog yazılarını getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}


function blogService()
{
    return new BlogService();
}
