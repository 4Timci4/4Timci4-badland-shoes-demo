<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class SeasonalCollectionsService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }


    public function getActiveCollections()
    {
        if (!$this->db) {
            return $this->getDemoActiveCollections();
        }
        try {
            return $this->db->select('seasonal_collections', [], '*', ['order' => 'sort_order ASC']);
        } catch (Exception $e) {
            error_log("Sezonluk koleksiyonlar getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function getCollectionById($id)
    {
        if (!$this->db) {
            return $this->getDemoCollectionById($id);
        }
        try {
            $result = $this->db->select('seasonal_collections', ['id' => intval($id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Koleksiyon getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Demo sezonluk koleksiyonlar
     */
    private function getDemoSeasonalCollections()
    {
        return [
            [
                'id' => 1,
                'name' => 'Bahar 2024 Koleksiyonu',
                'title' => 'Bahar 2024 Koleksiyonu',
                'slug' => 'bahar-2024-koleksiyonu',
                'layout_type' => 'left',
                'button_url' => '/products?collection=bahar-2024',
                'description' => 'Baharın taze enerjisini ayaklarınıza taşıyan renkli ve canlı tasarımlar. Pastel tonlar ve çiçek motifleriyle bezeli özel koleksiyon.',
                'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=800&q=80',
                'start_date' => '2024-03-01',
                'end_date' => '2024-05-31',
                'is_active' => 1,
                'sort_order' => 1,
                'featured_products' => 'Nike Air Max,Adidas Stan Smith,Converse Chuck Taylor',
                'background_color' => '#E8F5E8',
                'text_color' => '#2E7D32'
            ],
            [
                'id' => 2,
                'name' => 'Yaz 2024 Koleksiyonu',
                'title' => 'Yaz 2024 Koleksiyonu',
                'slug' => 'yaz-2024-koleksiyonu',
                'layout_type' => 'right',
                'button_url' => '/products?collection=yaz-2024',
                'description' => 'Sıcak yaz günlerinde ferahlık sunan nefes alabilir malzemeler ve açık renk paleti. Plaj tatilinden şehir gezisine kadar her anınız için.',
                'image_url' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=800&q=80',
                'start_date' => '2024-06-01',
                'end_date' => '2024-08-31',
                'is_active' => 1,
                'sort_order' => 2,
                'featured_products' => 'Sandal Modelleri,Canvas Ayakkabılar,Spor Ayakkabılar',
                'background_color' => '#FFF3E0',
                'text_color' => '#E65100'
            ],
            [
                'id' => 3,
                'name' => 'Sonbahar 2024 Koleksiyonu',
                'title' => 'Sonbahar 2024 Koleksiyonu',
                'slug' => 'sonbahar-2024-koleksiyonu',
                'layout_type' => 'left',
                'button_url' => '/products?collection=sonbahar-2024',
                'description' => 'Sonbaharın sıcak tonlarında tasarlanan klasik ve modern karışımı. Kahverengi, bordo ve turuncu renkleriyle sofistike tasarımlar.',
                'image_url' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=800&q=80',
                'start_date' => '2024-09-01',
                'end_date' => '2024-11-30',
                'is_active' => 1,
                'sort_order' => 3,
                'featured_products' => 'Bot Modelleri,Klasik Ayakkabılar,Deri Ayakkabılar',
                'background_color' => '#FFF8E1',
                'text_color' => '#F57F17'
            ],
            [
                'id' => 4,
                'name' => 'Kış 2024 Koleksiyonu',
                'title' => 'Kış 2024 Koleksiyonu',
                'slug' => 'kis-2024-koleksiyonu',
                'layout_type' => 'right',
                'button_url' => '/products?collection=kis-2024',
                'description' => 'Soğuk kış günlerinde ayaklarınızı sıcak tutacak su geçirmez ve termal özelliklere sahip özel tasarımlar. Hem şık hem fonksiyonel.',
                'image_url' => 'https://images.unsplash.com/photo-1584464491033-06628f3a6b7b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=800&q=80',
                'start_date' => '2024-12-01',
                'end_date' => '2025-02-28',
                'is_active' => 1,
                'sort_order' => 4,
                'featured_products' => 'Kışlık Botlar,Su Geçirmez Ayakkabılar,Termal Astarlar',
                'background_color' => '#E3F2FD',
                'text_color' => '#0D47A1'
            ],
            [
                'id' => 5,
                'name' => 'Spor Koleksiyonu',
                'title' => 'Spor Koleksiyonu',
                'slug' => 'spor-koleksiyonu',
                'layout_type' => 'left',
                'button_url' => '/products?collection=spor',
                'description' => 'Aktif yaşam tarzı için tasarlanan yüksek performanslı spor ayakkabıları. Koşu, fitness, basketbol ve tenis için özel modeller.',
                'image_url' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=800&q=80',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'is_active' => 1,
                'sort_order' => 5,
                'featured_products' => 'Nike Running,Adidas Performance,Puma Training',
                'background_color' => '#F3E5F5',
                'text_color' => '#7B1FA2'
            ],
            [
                'id' => 6,
                'name' => 'Klasik Koleksiyonu',
                'title' => 'Klasik Koleksiyonu',
                'slug' => 'klasik-koleksiyonu',
                'layout_type' => 'right',
                'button_url' => '/products?collection=klasik',
                'description' => 'Zamansız elegansın adresi. İş hayatından özel davetlere kadar her ortamda şıklığınızı tamamlayacak klasik modeller.',
                'image_url' => 'https://images.unsplash.com/photo-1584464491033-06628f3a6b7b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=800&q=80',
                'start_date' => '2024-01-01',
                'end_date' => '2024-12-31',
                'is_active' => 1,
                'sort_order' => 6,
                'featured_products' => 'Oxford Modelleri,Derby Ayakkabılar,Loafer Modelleri',
                'background_color' => '#EFEBE9',
                'text_color' => '#5D4037'
            ]
        ];
    }

    /**
     * Demo aktif koleksiyonlar
     */
    private function getDemoActiveCollections()
    {
        $collections = $this->getDemoSeasonalCollections();
        return array_filter($collections, function($collection) {
            return $collection['is_active'] == 1;
        });
    }

    /**
     * Demo koleksiyon detayı
     */
    private function getDemoCollectionById($id)
    {
        $collections = $this->getDemoSeasonalCollections();
        foreach ($collections as $collection) {
            if ($collection['id'] == $id) {
                return $collection;
            }
        }
        return null;
    }
}
