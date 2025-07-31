<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class AboutService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }

    public function getAboutPageContent()
    {
        if (!$this->db) {
            return $this->getDemoAboutPageContent();
        }
        
        $settings = $this->getSettings();
        $values = $this->getContentBlocks('values');
        $team = $this->getContentBlocks('team');

        return [
            'settings' => $settings,
            'values' => $values,
            'team' => $team,
        ];
    }

    public function getHomePageAboutSection()
    {
        if (!$this->db) {
            return $this->getDemoHomePageAboutSection();
        }
        try {
            $data = $this->db->select('about_settings', [], 'meta_key,meta_value');

            $result = [];
            $allowed_keys = [
                'story_content_title',
                'story_content_p1',
                'story_content_p2',
                'story_image_url',
                'story_content_homepage'
            ];

            foreach ($data as $item) {
                if (isset($item['meta_key'], $item['meta_value']) && in_array($item['meta_key'], $allowed_keys)) {
                    $result[$item['meta_key']] = $item['meta_value'];
                }
            }

            return $result;
        } catch (Exception $e) {
            error_log("Anasayfa Hakkımızda bölümü getirme hatası: " . $e->getMessage());
            return [];
        }
    }


    public function updateSetting($meta_key, $meta_value, $section = null)
    {
        try {
            $data = [
                'meta_value' => $meta_value,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($section) {
                $data['section'] = $section;
            }

            $result = $this->db->update('about_settings', $data, ['meta_key' => $meta_key]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Setting güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function updateMultipleSettings($settings)
    {
        $success = true;
        foreach ($settings as $meta_key => $meta_value) {
            if ($meta_key === 'story_title' || $meta_key === 'story_subtitle') {
                continue;
            }
            if (!$this->updateSetting($meta_key, $meta_value)) {
                $success = false;
            }
        }
        return $success;
    }


    public function createContentBlock($data)
    {
        try {
            $lastOrder = $this->getLastSortOrder($data['section']);
            $data['sort_order'] = $lastOrder + 1;
            $data['created_at'] = date('Y-m-d H:i:s');

            $result = $this->db->insert('about_content_blocks', $data);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Content block oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }

    public function updateContentBlock($id, $data)
    {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->db->update('about_content_blocks', $data, ['id' => intval($id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Content block güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function deleteContentBlock($id)
    {
        try {
            $result = $this->db->delete('about_content_blocks', ['id' => intval($id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Content block silme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function getContentBlockById($id)
    {
        try {
            $result = $this->db->select('about_content_blocks', ['id' => intval($id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Content block getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    public function updateContentBlockOrder($section, $orderData)
    {
        try {
            foreach ($orderData as $id => $order) {
                $this->updateContentBlock($id, ['sort_order' => intval($order)]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Content block sıralama güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    private function getLastSortOrder($section)
    {
        try {
            $result = $this->db->select('about_content_blocks', ['section' => $section], 'sort_order', ['order' => 'sort_order DESC', 'limit' => 1]);
            return !empty($result) ? $result[0]['sort_order'] : 0;
        } catch (Exception $e) {
            error_log("Son sıra numarası getirme hatası: " . $e->getMessage());
            return 0;
        }
    }

    public function getAboutStats()
    {
        if (!$this->db) {
            return $this->getDemoAboutStats();
        }
        
        try {
            $allSettings = $this->getSettings();
            $allValues = $this->getContentBlocks('values');
            $allTeam = $this->getContentBlocks('team');

            return [
                'total_settings' => count($allSettings),
                'total_values' => count($allValues),
                'total_team' => count($allTeam),
                'last_updated' => $this->getLastUpdatedDate()
            ];
        } catch (Exception $e) {
            error_log("About istatistikleri getirme hatası: " . $e->getMessage());
            return [
                'total_settings' => 0,
                'total_values' => 0,
                'total_team' => 0,
                'last_updated' => null
            ];
        }
    }

    private function getLastUpdatedDate()
    {
        if (!$this->db) {
            return '2025-01-31 10:00:00'; // Demo son güncelleme tarihi
        }
        
        try {
            $result = $this->db->select('about_settings', [], 'updated_at', ['order' => 'updated_at DESC', 'limit' => 1]);
            return !empty($result) ? $result[0]['updated_at'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function getSettings()
    {
        if (!$this->db) {
            return $this->getDemoSettings();
        }
        try {
            $data = $this->db->select('about_settings', [], '*');
            $settings = [];
            foreach ($data as $item) {
                if (isset($item['meta_key'], $item['meta_value'])) {
                    $settings[$item['meta_key']] = $item['meta_value'];
                }
            }
            return $settings;
        } catch (Exception $e) {
            error_log("Hakkımızda ayarları getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    private function getContentBlocks($section)
    {
        if (!$this->db) {
            return $this->getDemoContentBlocks($section);
        }
        try {
            return $this->db->select('about_content_blocks', ['section' => $section], '*', ['order' => 'sort_order ASC']);
        } catch (Exception $e) {
            error_log("Hakkımızda içerik blokları getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Demo anasayfa hakkımızda bölümü
     */
    private function getDemoHomePageAboutSection()
    {
        return [
            'story_content_title' => 'Bandland Shoes Hikayemiz',
            'story_content_p1' => 'Bandland Shoes hikayesi, 2020 yılında ayakkabı sektöründe 15 yıllık deneyime sahip Ahmet Yılmaz\'ın bir hayaliyle başladı. "Kaliteli ayakkabılar sadece belirli bir kesimin ayrıcalığı olmamalı" düşüncesiyle yola çıkan ekibimiz, önce küçük bir atölyede sadece 3 kişiyle başladı. İlk yılımızda sadece 50 çift ayakkabı ürettik, ancak her birini büyük bir titizlikle hazırladık.',
            'story_content_p2' => 'Bugün geldiğimiz noktada, yılda 10.000\'den fazla çift ayakkabı üretiyor ve Türkiye\'nin dört bir yanındaki 5.000\'den fazla mutlu müşterimize hizmet veriyoruz. Spor ayakkabılardan klasik modellere, çocuk ayakkabılarından özel koleksiyonlara kadar 200\'den fazla model ile geniş bir ürün yelpazesi sunuyoruz. Müşteri memnuniyetinde %98\'lik başarı oranımız ve 30 günlük koşulsuz iade garantimizle sektörde fark yaratıyoruz.',
            'story_content_p3' => 'Vizyonumuz, 2030 yılına kadar Türkiye\'nin en çok tercih edilen ayakkabı markası olmak ve uluslararası pazarlarda da varlık göstermek. Çevre dostu üretim süreçlerimiz, sürdürülebilir malzemeler kullanımımız ve sosyal sorumluluk projelerimizle hem gezegenimize hem de topluma katkı sağlamaya devam ediyoruz.',
            'story_image_url' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
            'story_content_homepage' => 'Bandland Shoes, 2020 yılında "kaliteli ayakkabılar herkes için ulaşılabilir olmalı" vizyonuyla kuruldu. Küçük bir atölyede 3 kişiyle başlayan yolculuğumuz, bugün 10.000\'den fazla çift ayakkabı üreten, 5.000\'den fazla mutlu müşteriye hizmet veren bir başarı hikayesine dönüştü. %98 müşteri memnuniyeti oranımız ve 200\'den fazla modellik ürün yelpazemizle, hem klasik hem de modern tasarımları en uygun fiyatlarla sunuyoruz.'
        ];
    }

    /**
     * Demo ayarlar
     */
     private function getDemoSettings()
     {
         return [
             'company_name' => 'Bandland Shoes',
             'company_tagline' => 'Premium Ayakkabı Deneyimi',
             'company_description' => 'Kalite ve stilin buluştuğu nokta. En yeni trendlerden klasik tasarımlara, her tarza uygun ayakkabıları keşfedin.',
             'mission_statement' => 'Müşterilerimize en kaliteli ayakkabıları, en uygun fiyatlarla sunarak ayak sağlığını ve stilini bir arada yaşatmak.',
             'vision_statement' => 'Türkiye\'nin en güvenilir ve tercih edilen ayakkabı markası olmak.',
             'established_year' => '2020',
             'company_address' => 'İstanbul, Türkiye',
             'phone_number' => '+90 212 555 0123',
             'email_address' => 'info@bandlandshoes.com',
             'working_hours' => 'Pazartesi - Cumartesi: 09:00 - 18:00',
             
             // About sayfası için gerekli alanlar
             'story_image_url' => 'https://images.unsplash.com/photo-1556906781-9a412961c28c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
             'story_content_title' => 'Bandland Shoes Hikayemiz',
             'story_content_p1' => 'Bandland Shoes hikayesi, 2020 yılında ayakkabı sektöründe 15 yıllık deneyime sahip Ahmet Yılmaz\'ın bir hayaliyle başladı. "Kaliteli ayakkabılar sadece belirli bir kesimin ayrıcalığı olmamalı" düşüncesiyle yola çıkan ekibimiz, önce küçük bir atölyede sadece 3 kişiyle başladı.',
             'story_content_p2' => 'Bugün geldiğimiz noktada, yılda 10.000\'den fazla çift ayakkabı üretiyor ve Türkiye\'nin dört bir yanındaki 5.000\'den fazla mutlu müşterimize hizmet veriyoruz. Spor ayakkabılardan klasik modellere, 200\'den fazla model ile geniş bir ürün yelpazesi sunuyoruz.',
             'story_content_p3' => 'Vizyonumuz, 2030 yılına kadar Türkiye\'nin en çok tercih edilen ayakkabı markası olmak ve uluslararası pazarlarda da varlık göstermek. Çevre dostu üretim süreçlerimiz ve sosyal sorumluluk projelerimizle hem gezegenimize hem de topluma katkı sağlıyoruz.',
             
             // Değerler bölümü
             'values_title' => 'Değerlerimiz',
             'values_subtitle' => 'Bandland Shoes olarak bizi özel kılan temel değerler',
             
             // Ekip bölümü
             'team_title' => 'Takımımız',
             'team_subtitle' => 'Başarımızın arkasındaki deneyimli ve tutkulu ekibimiz'
         ];
     }
     
    /**
     * Demo içerik blokları
     */
    private function getDemoContentBlocks($section)
    {
        switch ($section) {
            case 'values':
                return [
                    [
                        'id' => 1,
                        'section' => 'values',
                        'title' => 'Kalite',
                        'content' => 'Her ürünümüzde en yüksek kalite standartlarını uyguluyoruz. Uzun ömürlü ve dayanıklı malzemeler kullanıyoruz.',
                        'icon' => 'fas fa-star',
                        'sort_order' => 1
                    ],
                    [
                        'id' => 2,
                        'section' => 'values',
                        'title' => 'Konfor',
                        'content' => 'Ayak sağlığınızı ön planda tutarak, ergonomik tasarımlar ve rahat tabanlar tercih ediyoruz.',
                        'icon' => 'fas fa-heart',
                        'sort_order' => 2
                    ],
                    [
                        'id' => 3,
                        'section' => 'values',
                        'title' => 'Stil',
                        'content' => 'En son moda trendlerini takip ederek, her zevke uygun şık ve modern tasarımlar sunuyoruz.',
                        'icon' => 'fas fa-gem',
                        'sort_order' => 3
                    ],
                    [
                        'id' => 4,
                        'section' => 'values',
                        'title' => 'Müşteri Memnuniyeti',
                        'content' => 'Müşteri memnuniyeti bizim için her şeyden önemlidir. 7/24 destek hizmeti sunuyoruz.',
                        'icon' => 'fas fa-handshake',
                        'sort_order' => 4
                    ]
                ];

            case 'team':
                return [
                    [
                        'id' => 1,
                        'section' => 'team',
                        'title' => 'Ahmet Yılmaz',
                        'content' => 'Kurucu & CEO - 15 yıllık ayakkabı sektörü deneyimi',
                        'image_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
                        'sort_order' => 1
                    ],
                    [
                        'id' => 2,
                        'section' => 'team',
                        'title' => 'Fatma Kaya',
                        'content' => 'Tasarım Direktörü - Moda tasarımı alanında uzman',
                        'image_url' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
                        'sort_order' => 2
                    ],
                    [
                        'id' => 3,
                        'section' => 'team',
                        'title' => 'Mehmet Demir',
                        'content' => 'Kalite Kontrol Uzmanı - Ürün kalitesi ve test süreçlerinden sorumlu',
                        'image_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80',
                        'sort_order' => 3
                    ]
                ];

            default:
                return [];
        }
    }

    /**
     * Demo hakkımızda sayfa içeriği
     */
    private function getDemoAboutPageContent()
    {
        return [
            'settings' => $this->getDemoSettings(),
            'values' => $this->getDemoContentBlocks('values'),
            'team' => $this->getDemoContentBlocks('team'),
        ];
    }

    /**
     * Demo hakkımızda istatistikleri
     */
    private function getDemoAboutStats()
    {
        $settings = $this->getDemoSettings();
        $values = $this->getDemoContentBlocks('values');
        $team = $this->getDemoContentBlocks('team');
        
        return [
            'total_settings' => count($settings),
            'total_values' => count($values),
            'total_team' => count($team),
            'last_updated' => '2025-01-31 10:00:00'
        ];
    }
}

function aboutService()
{
    return new AboutService();
}
