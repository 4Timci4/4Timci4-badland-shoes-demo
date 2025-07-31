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
        if (!$this->db) {
            return $this->getDemoPosts($page, $perPage, $category, $tag);
        }
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
        if (!$this->db) {
            return $this->getDemoPostById($id);
        }
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
        if (!$this->db) {
            return $this->getDemoRelatedPosts($current_id, $category, $limit);
        }
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
        if (!$this->db) {
            return $this->getDemoAllBlogs($limit);
        }
        try {
            return $this->db->select('blogs', [], '*', ['order' => 'created_at DESC', 'limit' => $limit]);
        } catch (Exception $e) {
            error_log("Tüm blog yazılarını getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Demo blog yazıları verilerini döndürür
     */
    private function getDemoBlogPosts()
    {
        return [
            [
                'id' => 1,
                'title' => '2024 Yılının En Trend Ayakkabı Modelleri',
                'slug' => '2024-yilinin-en-trend-ayakkabi-modelleri',
                'content' => '<p>2024 yılında ayakkabı modasında hangi trendler öne çıkıyor? Bu yazımızda sezonun en gözde modellerini ve stil önerilerini sizler için derledik.</p>

<h3>Chunky Sneaker Trendi</h3>
<p>Bu yıl da devam eden chunky sneaker trendi, sokak modasının vazgeçilmezi olmaya devam ediyor. Kalın tabanları ve sportif tasarımları ile hem konfor hem de stil sunuyor.</p>

<h3>Minimalist Tasarımlar</h3>
<p>Sadelik ve zarafeti bir arada sunan minimalist ayakkabılar, özellikle iş hayatında tercih ediliyor. Temiz çizgileri ve kaliteli malzemeleri ile dikkat çekiyor.</p>

<h3>Sürdürülebilir Malzemeler</h3>
<p>Çevre bilinci artan tüketiciler, geri dönüştürülmüş malzemelerden üretilen ayakkabıları tercih ediyor.</p>',
                'excerpt' => '2024 yılında ayakkabı modasında hangi trendler öne çıkıyor? Sezonun en gözde modellerini keşfedin.',
                'featured_image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'category' => 'Moda Trendleri',
                'tags' => 'trend,2024,moda,ayakkabı',
                'author' => 'Bandland Shoes Editörü',
                'published_at' => '2024-01-15 10:00:00',
                'created_at' => '2024-01-15 10:00:00',
                'is_published' => 1,
                'meta_title' => '2024 Yılının En Trend Ayakkabı Modelleri | Bandland Shoes Blog',
                'meta_description' => '2024 yılının en trend ayakkabı modellerini keşfedin. Chunky sneaker, minimalist tasarımlar ve sürdürülebilir malzemeler.'
            ],
            [
                'id' => 2,
                'title' => 'Ayakkabı Bakımı: Uzun Ömürlü Kullanım İçin İpuçları',
                'slug' => 'ayakkabi-bakimi-uzun-omurlu-kullanim-icin-ipuclari',
                'content' => '<p>Kaliteli ayakkabılarınızın uzun yıllar dayanmasını istiyorsanız, doğru bakım teknikleri çok önemli. Bu rehberde en etkili bakım yöntemlerini sizlerle paylaşıyoruz.</p>

<h3>Günlük Temizlik</h3>
<p>Her kullanımdan sonra ayakkabılarınızı nemli bir bezle silin ve havalandırın. Bu basit adım, ayakkabılarınızın ömrünü önemli ölçüde uzatır.</p>

<h3>Uygun Saklama</h3>
<p>Ayakkabılarınızı doğru şekilde saklamak için ayakkabı kalıpları kullanın ve havadar bir ortamda muhafaza edin.</p>

<h3>Mevsimsel Bakım</h3>
<p>Farklı mevsimler farklı bakım teknikleri gerektirir. Kış ayları için su geçirmez koruma, yaz ayları için ise havalandırma önemlidir.</p>',
                'excerpt' => 'Ayakkabılarınızın uzun yıllar dayanması için doğru bakım tekniklerini öğrenin.',
                'featured_image' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'category' => 'Bakım Rehberi',
                'tags' => 'bakım,temizlik,ayakkabı,ipuçları',
                'author' => 'Bakım Uzmanı',
                'published_at' => '2024-01-10 14:30:00',
                'created_at' => '2024-01-10 14:30:00',
                'is_published' => 1,
                'meta_title' => 'Ayakkabı Bakımı Rehberi | Bandland Shoes',
                'meta_description' => 'Ayakkabılarınızın uzun ömürlü olması için pratik bakım ipuçları ve doğru temizlik teknikleri.'
            ],
            [
                'id' => 3,
                'title' => 'Doğru Ayakkabı Numarası Nasıl Belirlenir?',
                'slug' => 'dogru-ayakkabi-numarasi-nasil-belirlenir',
                'content' => '<p>Ayakkabı alırken en önemli faktörlerden biri doğru numara seçimi. Yanlış numara seçimi hem konforsuzluğa hem de ayak sağlığı sorunlarına yol açabilir.</p>

<h3>Ayak Ölçümü</h3>
<p>Ayağınızı akşam saatlerinde, günün en şişkin halindeyken ölçmek en doğru sonucu verir. Hem uzunluk hem de genişlik ölçümü önemlidir.</p>

<h3>Markalar Arası Farklar</h3>
<p>Her markanın kendi kalıp sistemı vardır. Bir markada 40 numara giyiyorsanız, başka bir markada 39 veya 41 giyebilirsiniz.</p>

<h3>Ayakkabı Türüne Göre Seçim</h3>
<p>Spor ayakkabılarda biraz daha bol, klasik ayakkabılarda ise tam oturan numaraları tercih etmek daha doğrudur.</p>',
                'excerpt' => 'Doğru ayakkabı numarası seçimi için profesyonel öneriler ve ölçüm teknikleri.',
                'featured_image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'category' => 'Rehber',
                'tags' => 'numara,ölçüm,ayakkabı,rehber',
                'author' => 'Satış Uzmanı',
                'published_at' => '2024-01-05 09:15:00',
                'created_at' => '2024-01-05 09:15:00',
                'is_published' => 1,
                'meta_title' => 'Doğru Ayakkabı Numarası Belirleme Rehberi',
                'meta_description' => 'Ayakkabı numarası seçerken dikkat edilmesi gerekenler ve doğru ölçüm teknikleri.'
            ],
            [
                'id' => 4,
                'title' => 'Spor Ayakkabısı Seçerken Dikkat Edilmesi Gerekenler',
                'slug' => 'spor-ayakkabisi-secerken-dikkat-edilmesi-gerekenler',
                'content' => '<p>Spor ayakkabısı seçimi, sadece görünüm değil aynı zamanda performans ve konfor açısından da çok önemlidir. Doğru seçim yapmak için nelere dikkat etmelisiniz?</p>

<h3>Spor Dalına Uygunluk</h3>
<p>Her spor dalının kendine özgü gereksinimleri vardır. Koşu, basketbol, tenis gibi farklı sporlar için özel tasarlanmış ayakkabılar bulunur.</p>

<h3>Taban Teknolojisi</h3>
<p>Şok emilimi, esneklik ve tutuş gibi özellikler taban teknolojisine bağlıdır. Kaliteli taban, hem performansı hem de ayak sağlığını etkiler.</p>

<h3>Üst Malzeme</h3>
<p>Nefes alabilir malzemeler, ayağın terlememesi ve koku oluşmaması için önemlidir. Mesh ve özel kumaşlar bu konuda tercih edilir.</p>',
                'excerpt' => 'Spor ayakkabısı seçerken performans ve konfor için dikkat edilmesi gereken önemli faktörler.',
                'featured_image' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'category' => 'Spor',
                'tags' => 'spor,ayakkabı,seçim,performans',
                'author' => 'Spor Uzmanı',
                'published_at' => '2024-01-01 11:00:00',
                'created_at' => '2024-01-01 11:00:00',
                'is_published' => 1,
                'meta_title' => 'Spor Ayakkabısı Seçimi Rehberi | Bandland Shoes',
                'meta_description' => 'Spor ayakkabısı seçerken dikkat edilmesi gereken faktörler: taban teknolojisi, malzeme ve spor dalına uygunluk.'
            ],
            [
                'id' => 5,
                'title' => 'Klasik Ayakkabılar: Zarafet ve Şıklığın Simgesi',
                'slug' => 'klasik-ayakkabilar-zarafet-ve-sikligin-simgesi',
                'content' => '<p>Klasik ayakkabılar, iş hayatından özel davetlere kadar birçok ortamda vazgeçilmez parçalardır. Kaliteli klasik ayakkabı nasıl seçilir?</p>

<h3>Deri Kalitesi</h3>
<p>Kaliteli deri, ayakkabının hem dayanıklılığını hem de görünümünü etkiler. Tam deri ayakkabılar, seneler boyunca güzelliğini korur.</p>

<h3>El İşçiliği</h3>
<p>Klasik ayakkabılarda dikişlerin düzgünlüğü, tabanın yapıştırılması gibi detaylar, kaliteyi gösteren önemli faktörlerdir.</p>

<h3>Renk ve Model Seçimi</h3>
<p>Siyah ve kahverengi klasik renkler, her gardıropla uyumludur. Oxford, Derby ve Monk modelleri en popüler seçeneklerdir.</p>',
                'excerpt' => 'Klasik ayakkabı seçiminde dikkat edilmesi gereken faktörler ve stil önerileri.',
                'featured_image' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'category' => 'Klasik',
                'tags' => 'klasik,deri,ayakkabı,şıklık',
                'author' => 'Stil Uzmanı',
                'published_at' => '2023-12-25 16:45:00',
                'created_at' => '2023-12-25 16:45:00',
                'is_published' => 1,
                'meta_title' => 'Klasik Ayakkabı Rehberi | Zarafet ve Şıklık',
                'meta_description' => 'Klasik ayakkabı seçimi, deri kalitesi ve model özellikleri hakkında detaylı rehber.'
            ],
            [
                'id' => 6,
                'title' => 'Kadın Ayakkabı Modasında Yenilikler',
                'slug' => 'kadin-ayakkabi-modasinda-yenilikler',
                'content' => '<p>Kadın ayakkabı modasında sürekli değişen trendler, her sezon yeni tarzlar ortaya çıkarıyor. 2024 sezonunda öne çıkan yenilikler neler?</p>

<h3>Platform Topuklar</h3>
<p>Hem konfor hem de şıklık sunan platform topuklar, bu sezonun favori modelleri arasında yer alıyor.</p>

<h3>Renkli Tasarımlar</h3>
<p>Siyah ve kahverengi klasik renklerin yanında, canlı renkler ve desenli modeller dikkat çekiyor.</p>

<h3>Sürdürülebilir Moda</h3>
<p>Çevre dostu malzemelerle üretilen ayakkabılar, bilinçli tüketicilerin tercihi oluyor.</p>',
                'excerpt' => 'Kadın ayakkabı modasındaki en yeni trendler ve 2024 sezonunun öne çıkan modelleri.',
                'featured_image' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
                'category' => 'Kadın Modasında',
                'tags' => 'kadın,moda,trend,ayakkabı',
                'author' => 'Moda Editörü',
                'published_at' => '2023-12-20 13:20:00',
                'created_at' => '2023-12-20 13:20:00',
                'is_published' => 1,
                'meta_title' => 'Kadın Ayakkabı Modasında Yenilikler | 2024 Trendleri',
                'meta_description' => 'Kadın ayakkabı modasındaki yeni trendler, platform topuklar ve sürdürülebilir moda anlayışı.'
            ]
        ];
    }

    /**
     * Demo sayfalama ile blog yazıları
     */
    private function getDemoPosts($page = 1, $perPage = 6, $category = null, $tag = null)
    {
        $allPosts = $this->getDemoBlogPosts();
        
        // Kategori filtresi
        if ($category) {
            $allPosts = array_filter($allPosts, function($post) use ($category) {
                return stripos($post['category'], $category) !== false;
            });
        }
        
        // Tag filtresi
        if ($tag) {
            $allPosts = array_filter($allPosts, function($post) use ($tag) {
                return stripos($post['tags'], $tag) !== false;
            });
        }
        
        $total = count($allPosts);
        $offset = ($page - 1) * $perPage;
        $posts = array_slice($allPosts, $offset, $perPage);
        
        return [
            'posts' => $posts,
            'total' => $total,
            'pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Demo blog yazısı detayı
     */
    private function getDemoPostById($id)
    {
        $posts = $this->getDemoBlogPosts();
        foreach ($posts as $post) {
            if ($post['id'] == $id) {
                return $post;
            }
        }
        return null;
    }

    /**
     * Demo benzer yazılar
     */
    private function getDemoRelatedPosts($current_id, $category, $limit = 2)
    {
        $posts = $this->getDemoBlogPosts();
        $related = [];
        
        foreach ($posts as $post) {
            if ($post['id'] != $current_id && stripos($post['category'], $category) !== false) {
                $related[] = $post;
                if (count($related) >= $limit) {
                    break;
                }
            }
        }
        
        return $related;
    }

    /**
     * Demo tüm blog yazıları
     */
    private function getDemoAllBlogs($limit = 100)
    {
        $posts = $this->getDemoBlogPosts();
        return array_slice($posts, 0, $limit);
    }
}


function blogService()
{
    return new BlogService();
}
