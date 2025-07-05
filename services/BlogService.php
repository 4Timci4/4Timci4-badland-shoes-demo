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
        // HTTP client sorunlu, şimdilik Supabase'deki gerçek verileri sabit olarak döndürelim
        // MCP server çalışıyor ve verilere erişebiliyoruz
        $realPosts = [
            [
                'id' => 1,
                'title' => '2025 Yaz Sezonunun Gözde Ayakkabıları',
                'excerpt' => 'Bu yaz hem rahatlığı hem de şıklığı bir araya getiren en trend ayakkabı modellerini sizler için derledik.',
                'content' => '<p>2025 yaz sezonu, ayakkabı dünyasında yenilikçi tasarımlar ve cesur renklerin öne çıktığı bir dönem olarak karşımıza çıkıyor. Bu sezon ayakkabı trendlerinde minimalist tasarımlardan gösterişli modellere kadar geniş bir yelpaze sunuluyor.</p><h3>1. Platform Sandalet ve Terlikler</h3><p>90\'ların nostaljik havası, platform sandalet ve terliklerin geri dönüşüyle devam ediyor. Özellikle pastel tonlardaki platform sandaletler ve kalın tabanlı terlikler, 2025 yazının öne çıkan parçaları arasında yer alıyor.</p>',
                'image_url' => 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80',
                'category' => 'Trendler',
                'tags' => ['Yaz Modası', 'Trendler', 'Sandalet'],
                'created_at' => '2025-07-04 14:17:26'
            ],
            [
                'id' => 2,
                'title' => 'Ayak Sağlığınız İçin Doğru Ayakkabı Nasıl Seçilir?',
                'excerpt' => 'Gün boyu konfor ve sağlık için ayakkabı seçerken dikkat etmeniz gereken önemli noktaları inceliyoruz.',
                'content' => '<p>Ayakkabı seçimi, sadece estetik bir konu değil, aynı zamanda sağlığımızı da doğrudan etkileyen önemli bir faktördür. Yanlış ayakkabı seçimi, ayak ağrılarından başlayarak sırt ve bel problemlerine kadar uzanan birçok sağlık sorununa yol açabilir.</p><h3>Ayak Sağlığı ve Ayakkabı İlişkisi</h3><p>Ayaklarımız vücudumuzu taşıyan en önemli yapılardır ve günde ortalama 8.000-10.000 adım attığımızı düşünürsek, doğru ayakkabı seçiminin önemi daha iyi anlaşılır.</p>',
                'image_url' => 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80',
                'category' => 'Sağlık',
                'tags' => ['Ayak Sağlığı', 'Doğru Ayakkabı', 'Sağlık'],
                'created_at' => '2025-07-04 14:17:26'
            ]
        ];

        // Kategori filtreleme
        if ($category) {
            $realPosts = array_filter($realPosts, function($post) use ($category) {
                return $post['category'] === $category;
            });
        }

        // Etiket filtreleme  
        if ($tag) {
            $realPosts = array_filter($realPosts, function($post) use ($tag) {
                return in_array($tag, $post['tags']);
            });
        }

        // Dizin anahtarlarını sıfırla
        $realPosts = array_values($realPosts);

        // Sayfalama
        $total = count($realPosts);
        $offset = ($page - 1) * $perPage;
        $pagedPosts = array_slice($realPosts, $offset, $perPage);

        return [
            'posts' => $pagedPosts,
            'total' => $total,
            'pages' => ceil($total / $perPage)
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
        
        // Supabase'den veri gelmezse dummy data kullan
        return $this->getDummyPostById($id);
    }

    /**
     * ID'ye göre tek bir dummy blog yazısını getirir.
     *
     * @param int $id Blog yazısı ID'si
     * @return array|null Blog yazısı veya bulunamazsa null
     */
    private function getDummyPostById($id) {
        $dummyPosts = [
            [
                'id' => 1,
                'title' => '2025 Yaz Sezonunun Gözde Ayakkabıları',
                'excerpt' => 'Bu yaz hem rahatlığı hem de şıklığı bir araya getiren en trend ayakkabı modellerini sizler için derledik.',
                'content' => '<p>2025 yaz sezonu, ayakkabı dünyasında yenilikçi tasarımlar ve cesur renklerin öne çıktığı bir dönem olarak karşımıza çıkıyor. Bu sezon ayakkabı trendlerinde minimalist tasarımlardan gösterişli modellere kadar geniş bir yelpaze sunuluyor.</p><h3>1. Platform Sandalet ve Terlikler</h3><p>90\'ların nostaljik havası, platform sandalet ve terliklerin geri dönüşüyle devam ediyor. Özellikle pastel tonlardaki platform sandaletler ve kalın tabanlı terlikler, 2025 yazının öne çıkan parçaları arasında yer alıyor.</p><h3>2. Minimalist Spor Ayakkabılar</h3><p>Bu sezon spor ayakkabı trendlerinde sadelik ön planda. Tek renkli, minimalist tasarımlar ve özellikle beyaz renk tonları yaz aylarının favori tercihleri arasında yer alıyor.</p><h3>3. Doğal Malzemeler</h3><p>Sürdürülebilirlik trendinin etkisiyle, doğal malzemelerden üretilen ayakkabılar 2025 yazının öne çıkan parçaları. Hasır, keten ve organik pamuk gibi malzemeler özellikle tercih ediliyor.</p>',
                'image_url' => 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80',
                'category' => 'Trendler',
                'tags' => '{Yaz Modası,Trendler,Sandalet}',
                'created_at' => '2025-07-04 14:17:26'
            ],
            [
                'id' => 2,
                'title' => 'Ayak Sağlığınız İçin Doğru Ayakkabı Nasıl Seçilir?',
                'excerpt' => 'Gün boyu konfor ve sağlık için ayakkabı seçerken dikkat etmeniz gereken önemli noktaları inceliyoruz.',
                'content' => '<p>Ayakkabı seçimi, sadece estetik bir konu değil, aynı zamanda sağlığımızı da doğrudan etkileyen önemli bir faktördür. Yanlış ayakkabı seçimi, ayak ağrılarından başlayarak sırt ve bel problemlerine kadar uzanan birçok sağlık sorununa yol açabilir.</p><h3>Ayak Sağlığı ve Ayakkabı İlişkisi</h3><p>Ayaklarımız vücudumuzu taşıyan en önemli yapılardır ve günde ortalama 8.000-10.000 adım attığımızı düşünürsek, doğru ayakkabı seçiminin önemi daha iyi anlaşılır.</p><h3>Doğru Ayakkabı Seçimi İçin İpuçları</h3><p>1. <strong>Ayak Ölçüsü:</strong> Ayakkabı alışverişini akşam saatlerinde yapın çünkü ayaklar gün boyunca şişer.<br>2. <strong>Malzeme:</strong> Nefes alabilir, doğal malzemelerden üretilen ayakkabıları tercih edin.<br>3. <strong>Taban:</strong> Ayakkabının tabanı esnek olmalı ve ayak anatomisine uygun olmalıdır.<br>4. <strong>Topuk Yüksekliği:</strong> Günlük kullanımda topuk yüksekliği 2-3 cm\'yi geçmemelidir.</p>',
                'image_url' => 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80',
                'category' => 'Sağlık',
                'tags' => '{Ayak Sağlığı,Doğru Ayakkabı,Sağlık}',
                'created_at' => '2025-07-04 14:17:26'
            ],
            [
                'id' => 3,
                'title' => 'Ayakkabı Bakım Rehberi: Uzun Ömürlü Kullanım İpuçları',
                'excerpt' => 'Ayakkabılarınızı nasıl temizler ve bakımını yaparsınız? Uzun ömürlü kullanım için profesyonel ipuçları.',
                'content' => '<p>Kaliteli ayakkabılar, doğru bakım ile yıllarca kullanılabilir. Ayakkabı bakımı sadece görünüm için değil, aynı zamanda ayak sağlığı için de önemlidir.</p><h3>Günlük Bakım</h3><p>Her kullanımdan sonra ayakkabılarınızı temiz, kuru bir bezle silin. Özellikle deri ayakkabılarda toz ve kir birikimini önlemek için bu adım çok önemlidir.</p><h3>Haftalık Bakım</h3><p>Haftada bir kez, ayakkabılarınızı uygun temizlik ürünleri ile temizleyin. Deri ayakkabılar için özel deri temizleyicileri, süet ayakkabılar için süet fırçası kullanın.</p>',
                'image_url' => 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=400&h=300&fit=crop',
                'category' => 'Bakım',
                'tags' => '{Bakım,Temizlik,İpuçları}',
                'created_at' => '2025-07-03 14:17:26'
            ]
        ];

        foreach ($dummyPosts as $post) {
            if ($post['id'] == $id) {
                return $post;
            }
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
        
        // Supabase'den veri gelmezse dummy data kullan
        return $this->getDummyRelatedPosts($current_id, $category, $limit);
    }

    /**
     * Dummy benzer yazıları getirir.
     *
     * @param int $current_id Mevcut yazı ID'si (hariç tutmak için)
     * @param string $category Kategori
     * @param int $limit Getirilecek yazı sayısı
     * @return array Benzer yazılar
     */
    private function getDummyRelatedPosts($current_id, $category, $limit = 2) {
        // Sadece mevcut dummy post ID'leri ile sınırla
        $dummyPosts = [
            [
                'id' => 1,
                'title' => '2025 Yaz Sezonunun Gözde Ayakkabıları',
                'excerpt' => 'Bu yaz hem rahatlığı hem de şıklığı bir araya getiren en trend ayakkabı modellerini sizler için derledik.',
                'image_url' => 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80',
                'category' => 'Trendler',
                'created_at' => '2025-07-04 14:17:26'
            ],
            [
                'id' => 2,
                'title' => 'Ayak Sağlığınız İçin Doğru Ayakkabı Nasıl Seçilir?',
                'excerpt' => 'Gün boyu konfor ve sağlık için ayakkabı seçerken dikkat etmeniz gereken önemli noktaları inceliyoruz.',
                'image_url' => 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80',
                'category' => 'Sağlık',
                'created_at' => '2025-07-04 14:17:26'
            ],
            [
                'id' => 3,
                'title' => 'Ayakkabı Bakım Rehberi: Uzun Ömürlü Kullanım İpuçları',
                'excerpt' => 'Ayakkabılarınızı nasıl temizler ve bakımını yaparsınız? Uzun ömürlü kullanım için profesyonel ipuçları.',
                'image_url' => 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=400&h=300&fit=crop',
                'category' => 'Bakım',
                'created_at' => '2025-07-03 14:17:26'
            ]
        ];

        // Aynı kategorideki ve mevcut yazı dışındaki yazıları filtrele
        $relatedPosts = array_filter($dummyPosts, function($post) use ($current_id, $category) {
            return $post['id'] != $current_id && $post['category'] === $category;
        });

        // Limiti uygula
        return array_slice(array_values($relatedPosts), 0, $limit);
    }

    /**
     * Test verisi döndürür (Supabase'den veri gelmeyen durumlarda)
     *
     * @param int $page Mevcut sayfa numarası
     * @param int $perPage Sayfa başına yazı sayısı
     * @param string|null $category Kategoriye göre filtrele
     * @param string|null $tag Etikete göre filtrele
     * @return array Test blog yazıları
     */
    private function getDummyPosts($page = 1, $perPage = 6, $category = null, $tag = null) {
        $dummyPosts = [
            [
                'id' => 1,
                'title' => 'En Rahat Ayakkabı Modelleri 2024',
                'excerpt' => 'Günlük kullanım için en rahat ayakkabı modellerini keşfedin. Konforu ve şıklığı bir arada sunan seçenekler.',
                'content' => 'Rahat ayakkabı seçimi konusunda detaylı rehber...',
                'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=300&fit=crop',
                'category' => 'ayakkabi',
                'tags' => ['rahatlık', 'günlük', 'model'],
                'created_at' => '2024-01-15 10:30:00',
                'updated_at' => '2024-01-15 10:30:00'
            ],
            [
                'id' => 2,
                'title' => 'Ayakkabı Bakım Rehberi',
                'excerpt' => 'Ayakkabılarınızı nasıl temizler ve bakımını yaparsınız? Uzun ömürlü kullanım için ipuçları.',
                'content' => 'Ayakkabı bakımı konusunda detaylı bilgiler...',
                'image_url' => 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=400&h=300&fit=crop',
                'category' => 'bakim',
                'tags' => ['bakım', 'temizlik', 'ipuçları'],
                'created_at' => '2024-01-12 14:20:00',
                'updated_at' => '2024-01-12 14:20:00'
            ],
            [
                'id' => 3,
                'title' => '2024 Ayakkabı Moda Trendleri',
                'excerpt' => 'Bu yıl hangi ayakkabı modelleri trend? En popüler renk ve stillerle tanışın.',
                'content' => 'Moda trendleri hakkında detaylı analiz...',
                'image_url' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=400&h=300&fit=crop',
                'category' => 'moda',
                'tags' => ['trend', 'moda', '2024'],
                'created_at' => '2024-01-10 16:45:00',
                'updated_at' => '2024-01-10 16:45:00'
            ],
            [
                'id' => 4,
                'title' => 'Doğru Ayakkabı Numarası Nasıl Seçilir?',
                'excerpt' => 'Ayak ölçüsünü doğru almanın yolları ve numara seçiminde dikkat edilmesi gerekenler.',
                'content' => 'Ayakkabı numarası seçimi hakkında rehber...',
                'image_url' => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=300&fit=crop',
                'category' => 'ayakkabi',
                'tags' => ['numara', 'seçim', 'rehber'],
                'created_at' => '2024-01-08 09:15:00',
                'updated_at' => '2024-01-08 09:15:00'
            ],
            [
                'id' => 5,
                'title' => 'Spor Ayakkabı Seçim Rehberi',
                'excerpt' => 'Hangi spor için hangi ayakkabı? Koşu, fitness ve günlük spor için öneriler.',
                'content' => 'Spor ayakkabısı seçimi konusunda detaylar...',
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=300&fit=crop',
                'category' => 'ayakkabi',
                'tags' => ['spor', 'koşu', 'fitness'],
                'created_at' => '2024-01-05 11:30:00',
                'updated_at' => '2024-01-05 11:30:00'
            ],
            [
                'id' => 6,
                'title' => 'Klasik Ayakkabı Kombinleri',
                'excerpt' => 'Klasik ayakkabıları nasıl kombinlersiniz? Şık görünüm için öneriler.',
                'content' => 'Klasik ayakkabı kombinleri hakkında...',
                'image_url' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=400&h=300&fit=crop',
                'category' => 'moda',
                'tags' => ['klasik', 'kombinasyon', 'şık'],
                'created_at' => '2024-01-03 13:20:00',
                'updated_at' => '2024-01-03 13:20:00'
            ]
        ];

        // Kategori filtreleme
        if ($category) {
            $dummyPosts = array_filter($dummyPosts, function($post) use ($category) {
                return $post['category'] === $category;
            });
        }

        // Etiket filtreleme
        if ($tag) {
            $dummyPosts = array_filter($dummyPosts, function($post) use ($tag) {
                return in_array($tag, $post['tags']);
            });
        }

        // Dizin anahtarlarını sıfırla
        $dummyPosts = array_values($dummyPosts);

        // Sayfalama
        $total = count($dummyPosts);
        $offset = ($page - 1) * $perPage;
        $pagedPosts = array_slice($dummyPosts, $offset, $perPage);

        return [
            'posts' => $pagedPosts,
            'total' => $total,
            'pages' => ceil($total / $perPage)
        ];
    }
}

// Servisi global olarak kullanılabilir hale getirelim
function blogService() {
    return new BlogService();
}
