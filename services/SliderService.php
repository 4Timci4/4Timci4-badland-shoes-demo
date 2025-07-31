<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class SliderService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }

    public function getActiveSliders()
    {
        if (!$this->db) {
            return $this->getDemoActiveSliders();
        }
        try {
            return $this->db->select('slider_items', ['is_active' => 1], '*', ['order' => 'sort_order ASC']);
        } catch (Exception $e) {
            error_log("Slider getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    public function getAllSliders()
    {
        if (!$this->db) {
            return $this->getDemoAllSliders();
        }
        try {
            return $this->db->select('slider_items', [], '*', ['order' => 'sort_order ASC']);
        } catch (Exception $e) {
            error_log("Tüm sliderleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    public function getSliderById($id)
    {
        if (!$this->db) {
            return $this->getDemoSliderById($id);
        }
        try {
            $result = $this->db->select('slider_items', ['id' => intval($id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Slider getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    public function createSlider($data)
    {
        try {
            $lastOrder = $this->getLastSortOrder();
            $data['sort_order'] = $lastOrder + 1;
            $data['created_at'] = date('Y-m-d H:i:s');

            $result = $this->db->insert('slider_items', $data);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Slider oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }

    public function updateSlider($id, $data)
    {
        try {
            $result = $this->db->update('slider_items', $data, ['id' => intval($id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Slider güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSlider($id)
    {
        try {
            $result = $this->db->delete('slider_items', ['id' => intval($id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Slider silme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function toggleSliderStatus($id)
    {
        try {
            $slider = $this->getSliderById($id);
            if (!$slider) {
                return false;
            }

            $newStatus = !$slider['is_active'];
            return $this->updateSlider($id, ['is_active' => $newStatus]);
        } catch (Exception $e) {
            error_log("Slider durum değiştirme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function updateSliderOrder($orderData)
    {
        try {
            foreach ($orderData as $id => $order) {
                $this->updateSlider($id, ['sort_order' => intval($order)]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Slider sıralama güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    private function getLastSortOrder()
    {
        try {
            $result = $this->db->select('slider_items', [], 'sort_order', ['order' => 'sort_order DESC', 'limit' => 1]);
            return !empty($result) ? $result[0]['sort_order'] : 0;
        } catch (Exception $e) {
            error_log("Son sıra numarası getirme hatası: " . $e->getMessage());
            return 0;
        }
    }

    public function getSliderStats()
    {
        if (!$this->db) {
            return $this->getDemoSliderStats();
        }
        try {
            $allSliders = $this->getAllSliders();
            $activeSliders = array_filter($allSliders, function ($slider) {
                return $slider['is_active'] === true;
            });

            return [
                'total' => count($allSliders),
                'active' => count($activeSliders),
                'inactive' => count($allSliders) - count($activeSliders)
            ];
        } catch (Exception $e) {
            error_log("Slider istatistikleri getirme hatası: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'inactive' => 0];
        }
    }

    /**
     * Demo slider verileri
     */
    private function getDemoSliders()
    {
        return [
            [
                'id' => 1,
                'title' => 'Yeni Sezon Koleksiyonu',
                'subtitle' => 'En Trend Ayakkabı Modelleri',
                'description' => '2024 yılının en çok tercih edilen ayakkabı modellerini keşfedin. Spor ayakkabılardan klasik modellere kadar geniş seçenek yelpazesi.',
                'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
                'button_text' => 'Koleksiyonu İncele',
                'button_url' => '/products',
                'text_position' => 'left',
                'overlay_opacity' => 0.4,
                'text_color' => '#ffffff',
                'sort_order' => 1,
                'is_active' => 1,
                'background_color' => 'rgba(0,0,0,0.3)'
            ],
            [
                'id' => 2,
                'title' => 'Spor Ayakkabı Fırsatları',
                'subtitle' => '%50\'ye Varan İndirimler',
                'description' => 'Nike, Adidas, Puma gibi dünyaca ünlü markaların spor ayakkabılarında büyük fırsatlar. Sınırlı süre için geçerli.',
                'image_url' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
                'button_text' => 'Fırsatları Gör',
                'button_url' => '/products?category=spor-ayakkabisi',
                'text_position' => 'center',
                'overlay_opacity' => 0.5,
                'text_color' => '#ffffff',
                'sort_order' => 2,
                'is_active' => 1,
                'background_color' => 'rgba(233,30,99,0.4)'
            ],
            [
                'id' => 3,
                'title' => 'Klasik Şıklık',
                'subtitle' => 'İş Hayatının Vazgeçilmezi',
                'description' => 'Ofis ortamından özel davetlere kadar her ortamda şıklığınızı tamamlayacak klasik ayakkabı modelleri.',
                'image_url' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
                'button_text' => 'Klasik Modeller',
                'button_url' => '/products?category=klasik-ayakkabi',
                'text_position' => 'right',
                'overlay_opacity' => 0.3,
                'text_color' => '#ffffff',
                'sort_order' => 3,
                'is_active' => 1,
                'background_color' => 'rgba(44,44,84,0.4)'
            ],
            [
                'id' => 4,
                'title' => 'Kadın Ayakkabı Koleksiyonu',
                'subtitle' => 'Feminenliğin Adresi',
                'description' => 'Topuklu ayakkabılardan rahat günlük modellere kadar kadın ayakkabı koleksiyonumuzu keşfedin.',
                'image_url' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
                'button_text' => 'Kadın Koleksiyonu',
                'button_url' => '/products?gender=kadin',
                'text_position' => 'left',
                'overlay_opacity' => 0.4,
                'text_color' => '#ffffff',
                'sort_order' => 4,
                'is_active' => 1,
                'background_color' => 'rgba(156,39,176,0.3)'
            ],
            [
                'id' => 5,
                'title' => 'Ücretsiz Kargo',
                'subtitle' => '500 TL ve Üzeri Alışverişlerde',
                'description' => 'Türkiye geneline ücretsiz kargo imkanı. Hızlı teslimat ve güvenli ödeme seçenekleri.',
                'image_url' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
                'button_text' => 'Hemen Alışveriş Yap',
                'button_url' => '/products',
                'text_position' => 'center',
                'overlay_opacity' => 0.6,
                'text_color' => '#ffffff',
                'sort_order' => 5,
                'is_active' => 0,
                'background_color' => 'rgba(76,175,80,0.4)'
            ]
        ];
    }

    /**
     * Demo aktif sliderlar
     */
    private function getDemoActiveSliders()
    {
        $sliders = $this->getDemoSliders();
        return array_filter($sliders, function($slider) {
            return $slider['is_active'] == 1;
        });
    }

    /**
     * Demo tüm sliderlar
     */
    private function getDemoAllSliders()
    {
        return $this->getDemoSliders();
    }

    /**
     * Demo slider detayı
     */
    private function getDemoSliderById($id)
    {
        $sliders = $this->getDemoSliders();
        foreach ($sliders as $slider) {
            if ($slider['id'] == $id) {
                return $slider;
            }
        }
        return null;
    }

    /**
     * Demo slider istatistikleri
     */
    private function getDemoSliderStats()
    {
        $sliders = $this->getDemoSliders();
        $activeCount = count(array_filter($sliders, function($slider) {
            return $slider['is_active'] == 1;
        }));
        
        return [
            'total' => count($sliders),
            'active' => $activeCount,
            'inactive' => count($sliders) - $activeCount
        ];
    }
}

function sliderService()
{
    return new SliderService();
}
