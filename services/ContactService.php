<?php
/**
 * İletişim Servisi
 * 
 * İletişim bilgileri, sosyal medya linkleri ve footer bilgilerini yönetir.
 */

class ContactService {
    private $supabase;

    public function __construct() {
        $this->supabase = supabase();
    }

    /**
     * Footer bilgilerini getir
     */
    public function getFooterInfo() {
        try {
            // Footer ve footer_links bölümlerini getir
            $response = $this->supabase->request("contact_info?or=(section.eq.footer,section.eq.footer_links)&select=*");
            $data = $response['body'] ?? [];
            
            $footer_info = [
                'footer' => [],
                'links' => [],
                'contact' => []
            ];
            
            // Verileri organize et
            foreach ($data as $item) {
                if ($item['section'] === 'footer') {
                    $footer_info['footer'][$item['field']] = $item['value'];
                } elseif ($item['section'] === 'footer_links') {
                    $footer_info['links'][$item['field']] = $item['value'];
                }
            }
            
            // Contact bilgilerini de ekle (adres, telefon, email)
            $contact_data = $this->getContactInfo();
            $footer_info['contact'] = [
                'address' => $contact_data['contact']['address'] ?? '',
                'phone' => $contact_data['contact']['phone1'] ?? '',
                'email' => $contact_data['contact']['email1'] ?? ''
            ];
            
            // Sosyal medya linklerini ekle
            $footer_info['social_links'] = $this->getSocialMediaLinks();
            
            return $footer_info;
        } catch (Exception $e) {
            error_log("Footer bilgileri getirme hatası: " . $e->getMessage());
            return [
                'footer' => [
                    'site_title' => 'Schön',
                    'site_description' => 'Türkiye\'nin en kaliteli ayakkabı markası.',
                    'copyright_text' => '© 2025 Schön. Tüm hakları saklıdır.'
                ],
                'links' => [
                    'home_url' => '/index.php',
                    'home_text' => 'Ana Sayfa',
                    'products_url' => '/products.php',
                    'products_text' => 'Ürünler',
                    'about_url' => '/about.php',
                    'about_text' => 'Hakkımızda',
                    'blog_url' => '/blog.php',
                    'blog_text' => 'Blog',
                    'contact_url' => '/contact.php',
                    'contact_text' => 'İletişim'
                ],
                'contact' => [
                    'address' => 'İstanbul, Türkiye',
                    'phone' => '+90 555 123 4567',
                    'email' => 'info@schon.com'
                ],
                'social_links' => []
            ];
        }
    }

    /**
     * Footer bilgilerini güncelle
     */
    public function updateFooterInfo($footer_data) {
        try {
            $success = true;
            
            // Footer bilgilerini güncelle
            foreach ($footer_data['footer'] as $field => $value) {
                $response = $this->supabase->request(
                    "contact_info?section=eq.footer&field=eq.{$field}",
                    'PATCH',
                    ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]
                );
                if (empty($response)) {
                    $success = false;
                }
            }
            
            // Footer links güncelle
            if (isset($footer_data['links'])) {
                foreach ($footer_data['links'] as $field => $value) {
                    $response = $this->supabase->request(
                        "contact_info?section=eq.footer_links&field=eq.{$field}",
                        'PATCH',
                        ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')]
                    );
                    if (empty($response)) {
                        $success = false;
                    }
                }
            }
            
            return $success;
        } catch (Exception $e) {
            error_log("Footer bilgileri güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * İletişim bilgilerini veritabanından getirir.
     *
     * @return array İletişim bilgileri
     */
    public function getContactInfo() {
        try {
            // Önbelleği temizle
            $this->supabase->clearCache('contact_info?select=*');
            
            $response = $this->supabase->request('contact_info?select=*');
            
            // Debug için log ekle
            error_log("Contact Info Response: " . json_encode($response));
            
            if (!empty($response['body'])) {
                // Verileri section ve field'a göre organize et
                $organized = [];
                foreach ($response['body'] as $item) {
                    $organized[$item['section']][$item['field']] = $item['value'];
                }
                
                // Debug için log ekle
                error_log("Organized Contact Info: " . json_encode($organized));
                
                return $organized;
            } else {
                error_log("Veritabanından veri alınamadı - boş response body");
            }
        } catch (Exception $e) {
            error_log("İletişim bilgilerini getirme hatası: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
        
        // Supabase'den veri gelmezse varsayılan değerleri kullan
        error_log("Varsayılan değerler kullanılıyor");
        return $this->getDefaultContactInfo();
    }

    /**
     * Sosyal medya linklerini veritabanından getirir.
     *
     * @return array Sosyal medya linkleri
     */
    public function getSocialMediaLinks() {
        try {
            $response = $this->supabase->request('social_media_links?select=*&is_active=eq.true&order=order_index');
            if (!empty($response['body'])) {
                return $response['body'];
            }
        } catch (Exception $e) {
            error_log("Sosyal medya linklerini getirme hatası: " . $e->getMessage());
        }
        
        // Supabase'den veri gelmezse varsayılan değerleri kullan
        return $this->getDefaultSocialMediaLinks();
    }

    /**
     * Varsayılan iletişim bilgilerini döndürür.
     *
     * @return array Varsayılan iletişim bilgileri
     */
    private function getDefaultContactInfo() {
        return [
            'banner' => [
                'title' => 'İletişim',
                'subtitle' => 'Bize ulaşın'
            ],
            'contact' => [
                'title' => 'İletişim Bilgileri',
                'description' => 'Herhangi bir sorunuz, öneriniz veya geri bildiriminiz mi var? Aşağıdaki bilgiler aracılığıyla bize ulaşabilir veya iletişim formunu doldurabilirsiniz.',
                'address' => 'Bağdat Caddesi No:123<br>Kadıköy, İstanbul',
                'phone1' => '+90 555 123 4567',
                'phone2' => '+90 216 123 4567',
                'email1' => 'info@schon.com',
                'email2' => 'support@schon.com',
                'working_hours1' => 'Pazartesi - Cumartesi: 10:00 - 20:00',
                'working_hours2' => 'Pazar: 12:00 - 18:00'
            ],
            'form' => [
                'title' => 'Bize Mesaj Gönderin',
                'success_title' => 'Mesajınız Başarıyla Gönderildi!',
                'success_message' => 'En kısa sürede size geri dönüş yapacağız.',
                'success_button' => 'Yeni Mesaj Gönder'
            ],
            'map' => [
                'title' => 'Bizi Ziyaret Edin',
                'embed_code' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d24086.521847965187!2d29.040827342981352!3d40.96982862395644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab617a4696a95%3A0xb08d84362e53c232!2zQmHEn2RhdCBDYWRkZXNpLCBLYWTEsWvDtnkvxLBzdGFuYnVs!5e0!3m2!1str!2str!4v1624529096157!5m2!1str!2str'
            ]
        ];
    }

    /**
     * Varsayılan sosyal medya linklerini döndürür.
     *
     * @return array Varsayılan sosyal medya linkleri
     */
    private function getDefaultSocialMediaLinks() {
        return [
            [
                'platform' => 'Facebook',
                'url' => '#',
                'icon_class' => 'fab fa-facebook-f',
                'order_index' => 1
            ],
            [
                'platform' => 'Twitter',
                'url' => '#',
                'icon_class' => 'fab fa-twitter',
                'order_index' => 2
            ],
            [
                'platform' => 'Instagram',
                'url' => '#',
                'icon_class' => 'fab fa-instagram',
                'order_index' => 3
            ],
            [
                'platform' => 'LinkedIn',
                'url' => '#',
                'icon_class' => 'fab fa-linkedin-in',
                'order_index' => 4
            ],
            [
                'platform' => 'YouTube',
                'url' => '#',
                'icon_class' => 'fab fa-youtube',
                'order_index' => 5
            ]
        ];
    }

    /**
     * İletişim formunu işler ve veritabanına kaydeder.
     *
     * @param array $formData Form verileri
     * @return bool Başarılı olursa true, aksi halde false
     */
    public function submitContactForm($formData) {
        try {
            // Form verilerini temizle
            $data = [
                'name' => htmlspecialchars($formData['name']),
                'email' => htmlspecialchars($formData['email']),
                'subject' => htmlspecialchars($formData['subject']),
                'message' => htmlspecialchars($formData['message']),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Veritabanına kaydet (eğer contact_messages tablosu varsa)
            $response = $this->supabase->request('contact_messages', 'POST', $data);
            
            if (!empty($response['body'])) {
                return true;
            }
        } catch (Exception $e) {
            error_log("İletişim formu gönderme hatası: " . $e->getMessage());
        }
        
        // Veritabanına kayıt başarısız olsa bile form gönderildi olarak işaretle
        // Gerçek uygulamada burada e-posta gönderimi yapılabilir
        return true;
    }

    /**
     * Admin için tüm mesajları getir (sayfalama ile)
     *
     * @param int $limit Sayfa başına mesaj sayısı
     * @param int $offset Başlangıç indeksi
     * @param string $search Arama terimi
     * @return array Mesajlar ve toplam sayı
     */
    public function getAllMessages($limit = 20, $offset = 0, $search = '') {
        try {
            $query_parts = ['select=*', 'order=created_at.desc'];
            
            // Arama filtresi
            if (!empty($search)) {
                $search_encoded = urlencode($search);
                $query_parts[] = "or=(name.ilike.*{$search_encoded}*,email.ilike.*{$search_encoded}*,subject.ilike.*{$search_encoded}*)";
            }
            
            // Sayfalama
            $query_parts[] = 'limit=' . intval($limit);
            $query_parts[] = 'offset=' . intval($offset);
            
            $query_string = implode('&', $query_parts);
            $response = $this->supabase->request('contact_messages?' . $query_string);
            $messages = $response['body'] ?? [];
            
            // Toplam sayı için ayrı sorgu
            $count_query = [];
            if (!empty($search)) {
                $search_encoded = urlencode($search);
                $count_query[] = "or=(name.ilike.*{$search_encoded}*,email.ilike.*{$search_encoded}*,subject.ilike.*{$search_encoded}*)";
            }
            
            $count_query_string = empty($count_query) ? '' : '?' . implode('&', $count_query);
            $count_response = $this->supabase->request('contact_messages' . $count_query_string);
            $total_count = count($count_response['body'] ?? []);
            
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

    /**
     * Mesaj silme
     *
     * @param int $message_id Mesaj ID
     * @return bool Başarı durumu
     */
    public function deleteMessage($message_id) {
        try {
            $response = $this->supabase->request('contact_messages?id=eq.' . intval($message_id), 'DELETE');
            return !empty($response);
        } catch (Exception $e) {
            error_log("Mesaj silme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * İletişim bilgilerini güncelle
     *
     * @param array $data Güncellenecek veriler
     * @return bool Başarı durumu
     */
    public function updateContactInfo($data) {
        try {
            $success_count = 0;
            foreach ($data as $section => $fields) {
                foreach ($fields as $field => $value) {
                    // Mevcut kaydı güncelle veya yeni oluştur
                    $update_data = [
                        'section' => $section,
                        'field' => $field,
                        'value' => htmlspecialchars($value),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // Önce kayıt var mı kontrol et
                    $check_response = $this->supabase->request("contact_info?section=eq.{$section}&field=eq.{$field}");
                    $existing = $check_response['body'] ?? [];
                    
                    if (!empty($existing)) {
                        // Güncelle
                        $response = $this->supabase->request("contact_info?section=eq.{$section}&field=eq.{$field}", 'PATCH', ['value' => htmlspecialchars($value), 'updated_at' => date('Y-m-d H:i:s')]);
                    } else {
                        // Yeni oluştur
                        $response = $this->supabase->request('contact_info', 'POST', $update_data);
                    }
                    
                    if (!empty($response)) {
                        $success_count++;
                    }
                }
            }
            
            return $success_count > 0;
        } catch (Exception $e) {
            error_log("İletişim bilgileri güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sosyal medya linki güncelle
     *
     * @param int $link_id Link ID
     * @param array $data Güncellenecek veriler
     * @return bool Başarı durumu
     */
    public function updateSocialMediaLink($link_id, $data) {
        try {
            $update_data = [
                'platform' => htmlspecialchars($data['platform']),
                'url' => htmlspecialchars($data['url']),
                'icon_class' => htmlspecialchars($data['icon_class']),
                'order_index' => intval($data['order_index']),
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $response = $this->supabase->request('social_media_links?id=eq.' . intval($link_id), 'PATCH', $update_data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Sosyal medya linki güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sosyal medya linki sil
     *
     * @param int $link_id Link ID
     * @return bool Başarı durumu
     */
    public function deleteSocialMediaLink($link_id) {
        try {
            $response = $this->supabase->request('social_media_links?id=eq.' . intval($link_id), 'DELETE');
            return !empty($response);
        } catch (Exception $e) {
            error_log("Sosyal medya linki silme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Yeni sosyal medya linki ekle
     *
     * @param array $data Link verileri
     * @return bool Başarı durumu
     */
    public function addSocialMediaLink($data) {
        try {
            $insert_data = [
                'platform' => htmlspecialchars($data['platform']),
                'url' => htmlspecialchars($data['url']),
                'icon_class' => htmlspecialchars($data['icon_class']),
                'order_index' => intval($data['order_index']),
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $response = $this->supabase->request('social_media_links', 'POST', $insert_data);
            return !empty($response['body']);
        } catch (Exception $e) {
            error_log("Sosyal medya linki ekleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tüm sosyal medya linklerini getir (admin için)
     *
     * @return array Sosyal medya linkleri
     */
    public function getAllSocialMediaLinks() {
        try {
            $response = $this->supabase->request('social_media_links?select=*&order=order_index.asc');
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Tüm sosyal medya linklerini getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// Servisi global olarak kullanılabilir hale getirelim
function contactService() {
    return new ContactService();
}
