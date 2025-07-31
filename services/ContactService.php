<?php


require_once __DIR__ . '/../lib/DatabaseFactory.php';
require_once __DIR__ . '/../config/cache.php';

class ContactService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }


    public function getFooterInfo()
    {
        if (!$this->db) {
            return $this->getDemoFooterInfo();
        }

        return CacheConfig::get('footer_info', function () {
            try {

                $data = $this->db->select('contact_info', ['section' => ['IN', ['footer', 'footer_links']]]);

                $footer_info = [
                    'footer' => [],
                    'links' => [],
                    'contact' => []
                ];


                foreach ($data as $item) {
                    if ($item['section'] === 'footer' && $item['field'] !== 'copyright_text') {
                        $footer_info['footer'][$item['field']] = $item['value'];
                    } elseif ($item['section'] === 'footer_links') {
                        $footer_info['links'][$item['field']] = $item['value'];
                    }
                }


                $contact_data = $this->getContactInfo();
                $footer_info['contact'] = [
                    'address' => $contact_data['contact']['address'] ?? '',
                    'phone' => $contact_data['contact']['phone1'] ?? '',
                    'email' => $contact_data['contact']['email1'] ?? ''
                ];


                $footer_info['social_links'] = $this->getSocialMediaLinks();

                return $footer_info;
            } catch (Exception $e) {
                error_log("Footer bilgileri getirme hatası: " . $e->getMessage());
                return [
                    'footer' => [],
                    'links' => [],
                    'contact' => [],
                    'social_links' => []
                ];
            }
        }, 1800);
    }


    public function updateFooterInfo($footer_data)
    {
        if (!$this->db) {
            return false; // Demo modunda güncelleme devre dışı
        }
        
        try {
            $success = true;


            foreach ($footer_data['footer'] as $field => $value) {
                $result = $this->db->update(
                    'contact_info',
                    ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                    ['section' => 'footer', 'field' => $field]
                );
                if ($result === false) {
                    $success = false;
                }
            }


            if (isset($footer_data['links'])) {
                foreach ($footer_data['links'] as $field => $value) {
                    $result = $this->db->update(
                        'contact_info',
                        ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                        ['section' => 'footer_links', 'field' => $field]
                    );
                    if ($result === false) {
                        $success = false;
                    }
                }
            }


            CacheConfig::clear('footer_info');
            CacheConfig::clear('contact_info');

            return $success;
        } catch (Exception $e) {
            error_log("Footer bilgileri güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function getContactInfo()
    {
        if (!$this->db) {
            return $this->getDemoContactInfo();
        }

        return CacheConfig::get('contact_info', function () {
            try {
                $data = $this->db->select('contact_info');

                if (!empty($data)) {

                    $organized = [];
                    foreach ($data as $item) {
                        $organized[$item['section']][$item['field']] = $item['value'];
                    }

                    return $organized;
                }
            } catch (Exception $e) {
                error_log("İletişim bilgilerini getirme hatası: " . $e->getMessage());
            }

            return [];
        }, 3600);
    }

    public function getSocialMediaLinks($only_active = true)
    {
        if (!$this->db) {
            return $this->getDemoSocialMediaLinks($only_active);
        }
        $cache_key = $only_active ? 'social_media_links_active' : 'social_media_links_all';

        return CacheConfig::get($cache_key, function () use ($only_active) {
            try {
                $conditions = [];
                if ($only_active) {
                    $conditions['is_active'] = 1;
                }
                return $this->db->select('social_media_links', $conditions, '*', ['order' => 'order_index ASC']);
            } catch (Exception $e) {
                error_log("Sosyal medya linklerini getirme hatası: " . $e->getMessage());
                return [];
            }
        }, 7200);
    }


    public function submitContactForm($formData)
    {
        if (!$this->db) {
            error_log("DEMO MODE: İletişim formu gönderildi - Name: " . $formData['name'] . ", Email: " . $formData['email'] . ", Subject: " . $formData['subject']);
            return true; // Demo modunda başarı simülasyonu
        }
        
        try {

            $data = [
                'name' => htmlspecialchars($formData['name']),
                'email' => htmlspecialchars($formData['email']),
                'subject' => htmlspecialchars($formData['subject']),
                'message' => htmlspecialchars($formData['message']),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->insert('contact_messages', $data);
            return $result !== false;
        } catch (Exception $e) {
            error_log("İletişim formu gönderme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function getAllMessages($limit = 20, $offset = 0, $search = '')
    {
        if (!$this->db) {
            return $this->getDemoMessages($limit, $offset, $search);
        }
        
        try {
            $conditions = [];
            if (!empty($search)) {

                $conditions['OR'] = [
                    'name' => ['LIKE', "%{$search}%"],
                    'email' => ['LIKE', "%{$search}%"],
                    'subject' => ['LIKE', "%{$search}%"]
                ];
            }

            $total_count = $this->db->count('contact_messages', $conditions);

            $messages = $this->db->select('contact_messages', $conditions, '*', [
                'order' => 'created_at DESC',
                'limit' => $limit,
                'offset' => $offset
            ]);

            return [
                'messages' => $messages,
                'total' => $total_count,
                'limit' => $limit,
                'offset' => $offset
            ];

        } catch (Exception $e) {
            error_log("Mesajları getirme hatası: " . $e->getMessage());
            return [
                'messages' => [],
                'total' => 0,
                'limit' => $limit,
                'offset' => $offset
            ];
        }
    }


    public function deleteMessage($message_id)
    {
        if (!$this->db) {
            return false; // Demo modunda silme devre dışı
        }
        
        try {
            $result = $this->db->delete('contact_messages', ['id' => intval($message_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Mesaj silme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function updateContactInfo($data)
    {
        if (!$this->db) {
            return false; // Demo modunda güncelleme devre dışı
        }
        
        try {
            $success_count = 0;
            foreach ($data as $section => $fields) {
                foreach ($fields as $field => $value) {
                    $update_data = [
                        'value' => htmlspecialchars($value),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $existing = $this->db->select('contact_info', ['section' => $section, 'field' => $field], 'id', ['limit' => 1]);

                    if (!empty($existing)) {
                        $result = $this->db->update('contact_info', $update_data, ['section' => $section, 'field' => $field]);
                    } else {
                        $insert_data = array_merge($update_data, ['section' => $section, 'field' => $field]);
                        $result = $this->db->insert('contact_info', $insert_data);
                    }

                    if ($result !== false) {
                        $success_count++;
                    }
                }
            }


            CacheConfig::clear('contact_info');
            CacheConfig::clear('footer_info');

            return $success_count > 0;
        } catch (Exception $e) {
            error_log("İletişim bilgileri güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function updateSocialMediaLink($link_id, $data)
    {
        if (!$this->db) {
            return false; // Demo modunda güncelleme devre dışı
        }
        
        try {
            $update_data = [
                'platform' => htmlspecialchars($data['platform']),
                'url' => htmlspecialchars($data['url']),
                'icon_class' => htmlspecialchars($data['icon_class']),
                'order_index' => intval($data['order_index']),
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->update('social_media_links', $update_data, ['id' => intval($link_id)]);


            if ($result !== false) {
                CacheConfig::clear('social_media_links_active');
                CacheConfig::clear('social_media_links_all');
                CacheConfig::clear('footer_info');
            }

            return $result !== false;
        } catch (Exception $e) {
            error_log("Sosyal medya linki güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function deleteSocialMediaLink($link_id)
    {
        if (!$this->db) {
            return false; // Demo modunda silme devre dışı
        }
        
        try {
            $result = $this->db->delete('social_media_links', ['id' => intval($link_id)]);


            if ($result !== false) {
                CacheConfig::clear('social_media_links_active');
                CacheConfig::clear('social_media_links_all');
                CacheConfig::clear('footer_info');
            }

            return $result !== false;
        } catch (Exception $e) {
            error_log("Sosyal medya linki silme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function addSocialMediaLink($data)
    {
        if (!$this->db) {
            return false; // Demo modunda ekleme devre dışı
        }
        
        try {
            $insert_data = [
                'platform' => htmlspecialchars($data['platform']),
                'url' => htmlspecialchars($data['url']),
                'icon_class' => htmlspecialchars($data['icon_class']),
                'order_index' => intval($data['order_index']),
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->insert('social_media_links', $insert_data);


            if ($result !== false) {
                CacheConfig::clear('social_media_links_active');
                CacheConfig::clear('social_media_links_all');
                CacheConfig::clear('footer_info');
            }

            return $result !== false;
        } catch (Exception $e) {
            error_log("Sosyal medya linki ekleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Demo footer bilgileri
     */
    private function getDemoFooterInfo()
    {
        return [
            'footer' => [
                'company_description' => 'Bandland Shoes, kaliteli ve şık ayakkabıların adresi. En yeni trendlerden klasik tasarımlara kadar geniş ürün yelpazemizle ayakkabı ihtiyacınızı karşılıyoruz.',
                'footer_title' => 'Bandland Shoes',
                'footer_tagline' => 'Adımınızdaki Fark',
                'working_hours' => 'Pazartesi - Cumartesi: 09:00 - 18:00<br>Pazar: 11:00 - 17:00'
            ],
            'links' => [
                'about_us' => '/about.php',
                'contact' => '/contact.php',
                'blog' => '/blog.php',
                'privacy_policy' => '/privacy.php',
                'terms_of_service' => '/terms.php',
                'size_guide' => '/size-guide.php'
            ],
            'contact' => [
                'address' => 'Örnek Mahallesi, Ayakkabı Caddesi No:123, Kadıköy/İstanbul',
                'phone' => '+90 216 555 0123',
                'email' => 'info@bandlandshoes.com'
            ],
            'social_links' => $this->getDemoSocialMediaLinks(true)
        ];
    }

    /**
     * Demo iletişim bilgileri
     */
    private function getDemoContactInfo()
    {
        return [
            'banner' => [
                'title' => 'İletişim',
                'subtitle' => 'Sizinle tanışmak ve sorularınızı yanıtlamak için buradayız'
            ],
            'contact' => [
                'title' => 'İletişim Bilgileri',
                'description' => 'Size en iyi hizmeti verebilmek için her zaman ulaşılabilir durumdayız. Sorularınız, önerileriniz veya destek talebiniz için bizimle iletişime geçin.',
                'address' => 'Örnek Mahallesi, Ayakkabı Caddesi No:123, Kadıköy/İstanbul',
                'phone1' => '+90 216 555 0123',
                'phone2' => '+90 216 555 0124',
                'email1' => 'info@bandlandshoes.com',
                'email2' => 'destek@bandlandshoes.com',
                'whatsapp' => '+90 533 123 45 67',
                'working_hours1' => 'Pazartesi - Cumartesi: 09:00 - 18:00',
                'working_hours2' => 'Pazar: 11:00 - 17:00'
            ],
            'form' => [
                'title' => 'Bize Mesaj Gönderin',
                'success_title' => 'Mesajınız Başarıyla Gönderildi!',
                'success_message' => 'En kısa sürede size geri dönüş yapacağız.',
                'success_button' => 'Yeni Mesaj Gönder'
            ],
            'map' => [
                'title' => 'Bizi Ziyaret Edin',
                'embed_code' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3010.2461!2d29.0247!3d40.9925!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDU5JzMzLjAiTiAyOcKwMDEnMjkuMCJF!5e0!3m2!1str!2str!4v1635789123456!5m2!1str!2str'
            ],
            'store' => [
                'store_name' => 'Bandland Shoes Mağaza',
                'store_address' => 'Bağdat Caddesi No:456, Kadıköy/İstanbul',
                'store_phone' => '+90 216 555 0125',
                'store_hours' => 'Her gün 10:00 - 22:00',
                'parking_info' => 'Ücretsiz vale hizmeti mevcuttur'
            ],
            'support' => [
                'support_email' => 'destek@bandlandshoes.com',
                'support_phone' => '+90 216 555 0126',
                'support_hours' => '7/24 Online Destek',
                'live_chat' => 'Aktif'
            ]
        ];
    }

    /**
     * Demo sosyal medya linkleri
     */
    private function getDemoSocialMediaLinks($only_active = true)
    {
        $links = [
            [
                'id' => 1,
                'platform' => 'Facebook',
                'url' => 'https://facebook.com/bandlandshoes',
                'icon_class' => 'fab fa-facebook-f',
                'order_index' => 1,
                'is_active' => 1
            ],
            [
                'id' => 2,
                'platform' => 'Instagram',
                'url' => 'https://instagram.com/bandlandshoes',
                'icon_class' => 'fab fa-instagram',
                'order_index' => 2,
                'is_active' => 1
            ],
            [
                'id' => 3,
                'platform' => 'Twitter',
                'url' => 'https://twitter.com/bandlandshoes',
                'icon_class' => 'fab fa-twitter',
                'order_index' => 3,
                'is_active' => 1
            ],
            [
                'id' => 4,
                'platform' => 'YouTube',
                'url' => 'https://youtube.com/bandlandshoes',
                'icon_class' => 'fab fa-youtube',
                'order_index' => 4,
                'is_active' => 1
            ],
            [
                'id' => 5,
                'platform' => 'LinkedIn',
                'url' => 'https://linkedin.com/company/bandlandshoes',
                'icon_class' => 'fab fa-linkedin-in',
                'order_index' => 5,
                'is_active' => 0
            ],
            [
                'id' => 6,
                'platform' => 'Pinterest',
                'url' => 'https://pinterest.com/bandlandshoes',
                'icon_class' => 'fab fa-pinterest',
                'order_index' => 6,
                'is_active' => 1
            ],
            [
                'id' => 7,
                'platform' => 'TikTok',
                'url' => 'https://tiktok.com/@bandlandshoes',
                'icon_class' => 'fab fa-tiktok',
                'order_index' => 7,
                'is_active' => 1
            ]
        ];

        if ($only_active) {
            $links = array_filter($links, function($link) {
                return $link['is_active'] == 1;
            });
        }

        return $links;
    }

    /**
     * Demo mesajlar
     */
    private function getDemoMessages($limit = 20, $offset = 0, $search = '')
    {
        $messages = [
            [
                'id' => 1,
                'name' => 'Ayşe Yılmaz',
                'email' => 'ayse@example.com',
                'subject' => 'Ürün Hakkında Soru',
                'message' => 'Merhaba, 42 numara spor ayakkabı stokunuz var mı?',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],
            [
                'id' => 2,
                'name' => 'Mehmet Kaya',
                'email' => 'mehmet@example.com',
                'subject' => 'Kargo Takibi',
                'message' => 'Siparişim ne zaman gelir?',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                'id' => 3,
                'name' => 'Fatma Öztürk',
                'email' => 'fatma@example.com',
                'subject' => 'İade Talebi',
                'message' => 'Satın aldığım ayakkabı bana küçük geldi, iade edebilir miyim?',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ]
        ];

        // Basit arama filtresi
        if (!empty($search)) {
            $messages = array_filter($messages, function($msg) use ($search) {
                return stripos($msg['name'], $search) !== false || 
                       stripos($msg['email'], $search) !== false || 
                       stripos($msg['subject'], $search) !== false;
            });
        }

        $total = count($messages);
        $filtered_messages = array_slice($messages, $offset, $limit);

        return [
            'messages' => $filtered_messages,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
}


function contactService()
{
    return new ContactService();
}
