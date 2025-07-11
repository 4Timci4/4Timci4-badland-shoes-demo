<?php
/**
 * İletişim Servisi
 *
 * İletişim bilgileri, sosyal medya linkleri ve footer bilgilerini yönetir.
 */

require_once __DIR__ . '/../lib/DatabaseFactory.php';
require_once __DIR__ . '/../config/cache.php';

class ContactService {
    private $db;

    public function __construct() {
        $this->db = database();
    }

    /**
     * Footer bilgilerini getir - Performans için önbellek kullanır
     */
    public function getFooterInfo() {
        // Önbellekten getirmeyi dene - 30 dakika boyunca önbellekte tut
        return CacheConfig::get('footer_info', function() {
            try {
                // Footer ve footer_links bölümlerini getir
                $data = $this->db->select('contact_info', ['section' => ['IN', ['footer', 'footer_links']]]);
                
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
                    'footer' => [],
                    'links' => [],
                    'contact' => [],
                    'social_links' => []
                ];
            }
        }, 1800); // 30 dakika
    }

    /**
     * Footer bilgilerini güncelle ve önbelleği temizle
     */
    public function updateFooterInfo($footer_data) {
        try {
            $success = true;
            
            // Footer bilgilerini güncelle
            foreach ($footer_data['footer'] as $field => $value) {
                $result = $this->db->update('contact_info',
                    ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                    ['section' => 'footer', 'field' => $field]
                );
                if ($result === false) {
                    $success = false;
                }
            }
            
            // Footer links güncelle
            if (isset($footer_data['links'])) {
                foreach ($footer_data['links'] as $field => $value) {
                    $result = $this->db->update('contact_info',
                        ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                        ['section' => 'footer_links', 'field' => $field]
                    );
                    if ($result === false) {
                        $success = false;
                    }
                }
            }
            
            // Önbelleği temizle
            CacheConfig::clear('footer_info');
            CacheConfig::clear('contact_info');
            
            return $success;
        } catch (Exception $e) {
            error_log("Footer bilgileri güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * İletişim bilgilerini veritabanından getirir - Performans için önbellek kullanır
     *
     * @return array İletişim bilgileri
     */
    public function getContactInfo() {
        // Önbellekten getirmeyi dene - 1 saat boyunca önbellekte tut
        return CacheConfig::get('contact_info', function() {
            try {
                $data = $this->db->select('contact_info');
                
                if (!empty($data)) {
                    // Verileri section ve field'a göre organize et
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
        }, 3600); // 1 saat
    }

    /**
     * Sosyal medya linklerini veritabanından getirir - Performans için önbellek kullanır
     *
     * @return array Sosyal medya linkleri
     */
    public function getSocialMediaLinks() {
        // Önbellekten getirmeyi dene - 2 saat boyunca önbellekte tut
        return CacheConfig::get('social_media_links', function() {
            try {
                return $this->db->select('social_media_links', ['is_active' => 1], '*', ['order' => 'order_index ASC']);
            } catch (Exception $e) {
                error_log("Sosyal medya linklerini getirme hatası: " . $e->getMessage());
                return [];
            }
        }, 7200); // 2 saat
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

            $result = $this->db->insert('contact_messages', $data);
            return $result !== false;
        } catch (Exception $e) {
            error_log("İletişim formu gönderme hatası: " . $e->getMessage());
            return false;
        }
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
            $conditions = [];
            if (!empty($search)) {
                // Basit bir arama, daha karmaşık senaryolar için uyarlanabilir
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

    /**
     * Mesaj silme
     *
     * @param int $message_id Mesaj ID
     * @return bool Başarı durumu
     */
    public function deleteMessage($message_id) {
        try {
            $result = $this->db->delete('contact_messages', ['id' => intval($message_id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Mesaj silme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * İletişim bilgilerini güncelle ve önbelleği temizle
     *
     * @param array $data Güncellenecek veriler
     * @return bool Başarı durumu
     */
    public function updateContactInfo($data) {
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
            
            // Önbelleği temizle
            CacheConfig::clear('contact_info');
            CacheConfig::clear('footer_info');
            
            return $success_count > 0;
        } catch (Exception $e) {
            error_log("İletişim bilgileri güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sosyal medya linki güncelle ve önbelleği temizle
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
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->db->update('social_media_links', $update_data, ['id' => intval($link_id)]);
            
            // Önbelleği temizle
            if ($result !== false) {
                CacheConfig::clear('social_media_links');
                CacheConfig::clear('footer_info');
            }
            
            return $result !== false;
        } catch (Exception $e) {
            error_log("Sosyal medya linki güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sosyal medya linki sil ve önbelleği temizle
     *
     * @param int $link_id Link ID
     * @return bool Başarı durumu
     */
    public function deleteSocialMediaLink($link_id) {
        try {
            $result = $this->db->delete('social_media_links', ['id' => intval($link_id)]);
            
            // Önbelleği temizle
            if ($result !== false) {
                CacheConfig::clear('social_media_links');
                CacheConfig::clear('footer_info');
            }
            
            return $result !== false;
        } catch (Exception $e) {
            error_log("Sosyal medya linki silme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Yeni sosyal medya linki ekle ve önbelleği temizle
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
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $this->db->insert('social_media_links', $insert_data);
            
            // Önbelleği temizle
            if ($result !== false) {
                CacheConfig::clear('social_media_links');
                CacheConfig::clear('footer_info');
            }
            
            return $result !== false;
        } catch (Exception $e) {
            error_log("Sosyal medya linki ekleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tüm sosyal medya linklerini getir (admin için) - Kısa süreli önbellek kullanır
     *
     * @return array Sosyal medya linkleri
     */
    public function getAllSocialMediaLinks() {
        // Admin için kısa süreli önbellek (15 dakika)
        return CacheConfig::get('all_social_media_links', function() {
            try {
                return $this->db->select('social_media_links', [], '*', ['order' => 'order_index ASC']);
            } catch (Exception $e) {
                error_log("Tüm sosyal medya linklerini getirme hatası: " . $e->getMessage());
                return [];
            }
        }, 900); // 15 dakika
    }
}

// Servisi global olarak kullanılabilir hale getirelim
function contactService() {
    return new ContactService();
}
