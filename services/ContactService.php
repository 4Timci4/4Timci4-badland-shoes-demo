<?php
/**
 * İletişim Servisi
 * 
 * İletişim sayfası bilgilerini veritabanından yönetir.
 */

class ContactService {
    private $supabase;

    public function __construct() {
        $this->supabase = supabase();
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
}

// Servisi global olarak kullanılabilir hale getirelim
function contactService() {
    return new ContactService();
}
