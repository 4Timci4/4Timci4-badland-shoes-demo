<?php
/**
 * Ayarlar Servisi
 * 
 * Site genel ayarları ve SEO ayarlarını veritabanından yönetir.
 */

class SettingsService {
    private $supabase;

    public function __construct() {
        $this->supabase = supabase();
    }

    /**
     * Site ayarı getir
     *
     * @param string $key Ayar anahtarı
     * @param string $default Varsayılan değer
     * @return string Ayar değeri
     */
    public function getSiteSetting($key, $default = '') {
        try {
            $response = $this->supabase->request("site_settings?setting_key=eq.{$key}&select=setting_value");
            
            if (!empty($response['body']) && isset($response['body'][0]['setting_value'])) {
                return $response['body'][0]['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Site ayarı getirme hatası: " . $e->getMessage());
        }
        
        return $default;
    }

    /**
     * Site ayarını güncelle
     *
     * @param string $key Ayar anahtarı
     * @param string $value Ayar değeri
     * @param string $group Ayar grubu
     * @param string $description Açıklama
     * @return bool Başarı durumu
     */
    public function updateSiteSetting($key, $value, $group = 'general', $description = '') {
        try {
            // Önce ayar var mı kontrol et
            $check_response = $this->supabase->request("site_settings?setting_key=eq.{$key}");
            $existing = $check_response['body'] ?? [];
            
            $data = [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_group' => $group,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (!empty($existing)) {
                // Güncelle
                $response = $this->supabase->request("site_settings?setting_key=eq.{$key}", 'PATCH', $data);
            } else {
                // Yeni oluştur
                $data['created_at'] = date('Y-m-d H:i:s');
                $response = $this->supabase->request('site_settings', 'POST', $data);
            }
            
            return !empty($response);
        } catch (Exception $e) {
            error_log("Site ayarı güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Grup bazında ayarları getir
     *
     * @param string $group Ayar grubu
     * @return array Ayarlar
     */
    public function getSettingsByGroup($group) {
        try {
            $response = $this->supabase->request("site_settings?setting_group=eq.{$group}&select=*&order=setting_key.asc");
            
            if (!empty($response['body'])) {
                $settings = [];
                foreach ($response['body'] as $setting) {
                    $settings[$setting['setting_key']] = $setting['setting_value'];
                }
                return $settings;
            }
        } catch (Exception $e) {
            error_log("Grup ayarları getirme hatası: " . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Çoklu ayar güncelleme
     *
     * @param array $settings Ayarlar array'i [key => value]
     * @param string $group Ayar grubu
     * @return bool Başarı durumu
     */
    public function updateMultipleSettings($settings, $group = 'general') {
        $success_count = 0;
        
        foreach ($settings as $key => $value) {
            if ($this->updateSiteSetting($key, $value, $group)) {
                $success_count++;
            }
        }
        
        return $success_count > 0;
    }

    /**
     * SEO ayarı getir
     *
     * @param string $key SEO ayar anahtarı
     * @param string $default Varsayılan değer
     * @return string SEO ayar değeri
     */
    public function getSeoSetting($key, $default = '') {
        try {
            $response = $this->supabase->request("seo_settings?setting_key=eq.{$key}&select=setting_value");
            
            if (!empty($response['body']) && isset($response['body'][0]['setting_value'])) {
                return $response['body'][0]['setting_value'];
            }
        } catch (Exception $e) {
            error_log("SEO ayarı getirme hatası: " . $e->getMessage());
        }
        
        return $default;
    }

    /**
     * SEO ayarını güncelle
     *
     * @param string $key SEO ayar anahtarı
     * @param string $value SEO ayar değeri
     * @param string $type SEO ayar tipi
     * @param bool $is_active Aktif durumu
     * @return bool Başarı durumu
     */
    public function updateSeoSetting($key, $value, $type = 'meta', $is_active = true) {
        try {
            // Önce ayar var mı kontrol et
            $check_response = $this->supabase->request("seo_settings?setting_key=eq.{$key}");
            $existing = $check_response['body'] ?? [];
            
            $data = [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => $type,
                'is_active' => $is_active,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (!empty($existing)) {
                // Güncelle
                $response = $this->supabase->request("seo_settings?setting_key=eq.{$key}", 'PATCH', $data);
            } else {
                // Yeni oluştur
                $data['created_at'] = date('Y-m-d H:i:s');
                $response = $this->supabase->request('seo_settings', 'POST', $data);
            }
            
            return !empty($response);
        } catch (Exception $e) {
            error_log("SEO ayarı güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tip bazında SEO ayarları getir
     *
     * @param string $type SEO ayar tipi
     * @return array SEO ayarları
     */
    public function getSeoSettingsByType($type) {
        try {
            $response = $this->supabase->request("seo_settings?setting_type=eq.{$type}&select=*&order=setting_key.asc");
            
            if (!empty($response['body'])) {
                $settings = [];
                foreach ($response['body'] as $setting) {
                    $settings[$setting['setting_key']] = [
                        'value' => $setting['setting_value'],
                        'is_active' => $setting['is_active']
                    ];
                }
                return $settings;
            }
        } catch (Exception $e) {
            error_log("SEO tip ayarları getirme hatası: " . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Tüm SEO ayarları getir
     *
     * @return array Tüm SEO ayarları
     */
    public function getAllSeoSettings() {
        try {
            $response = $this->supabase->request('seo_settings?select=*&order=setting_type.asc,setting_key.asc');
            
            if (!empty($response['body'])) {
                $settings = [];
                foreach ($response['body'] as $setting) {
                    $settings[$setting['setting_type']][$setting['setting_key']] = [
                        'value' => $setting['setting_value'],
                        'is_active' => $setting['is_active']
                    ];
                }
                return $settings;
            }
        } catch (Exception $e) {
            error_log("Tüm SEO ayarları getirme hatası: " . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Varsayılan site ayarlarını getir
     *
     * @return array Varsayılan ayarlar
     */
    public function getDefaultSiteSettings() {
        return [
            'site_name' => 'Schön - Ayakkabı Mağazası',
            'site_tagline' => 'Kaliteli ve Şık Ayakkabılar',
            'site_description' => 'En kaliteli ayakkabı modelleri ve uygun fiyatlarla Schön\'de',
            'site_logo' => 'assets/images/mt-logo.png',
            'site_favicon' => 'assets/images/favicon.ico',
            'primary_color' => '#e91e63',
            'secondary_color' => '#2c2c54',
            'footer_copyright' => '© 2024 Schön. Tüm hakları saklıdır.',
            'default_email' => 'info@schon.com',
            'support_email' => 'support@schon.com',
            'phone_1' => '+90 555 123 4567',
            'phone_2' => '+90 216 123 4567',
            'address' => 'Bağdat Caddesi No:123<br>Kadıköy, İstanbul',
            'working_hours' => 'Pazartesi - Cumartesi: 10:00 - 20:00<br>Pazar: 12:00 - 18:00',
            'currency' => 'TRY',
            'currency_symbol' => '₺',
            'products_per_page' => '12',
            'blogs_per_page' => '10',
            'maintenance_mode' => 'false',
            'site_language' => 'tr',
            'timezone' => 'Europe/Istanbul',
            'comments_enabled' => 'true'
        ];
    }

    /**
     * Varsayılan SEO ayarlarını getir
     *
     * @return array Varsayılan SEO ayarları
     */
    public function getDefaultSeoSettings() {
        return [
            'meta' => [
                'default_title' => 'Schön - Kaliteli Ayakkabı Modelleri',
                'title_separator' => ' | ',
                'default_description' => 'En kaliteli ayakkabı modelleri ve uygun fiyatlarla Schön\'de. Kadın, erkek ve çocuk ayakkabı koleksiyonlarımızı keşfedin.',
                'default_keywords' => 'ayakkabı, kadın ayakkabı, erkek ayakkabı, çocuk ayakkabı, spor ayakkabı, klasik ayakkabı',
                'author' => 'Schön',
                'robots' => 'index, follow'
            ],
            'social' => [
                'og_site_name' => 'Schön',
                'og_type' => 'website',
                'og_image' => 'assets/images/og-image.jpg',
                'twitter_card' => 'summary_large_image',
                'twitter_site' => '@schonshoes',
                'facebook_app_id' => '',
                'linkedin_company' => 'schon-shoes'
            ],
            'analytics' => [
                'google_analytics_id' => '',
                'google_tag_manager_id' => '',
                'facebook_pixel_id' => '',
                'google_search_console' => '',
                'bing_webmaster' => '',
                'yandex_verification' => ''
            ],
            'technical' => [
                'canonical_enabled' => 'true',
                'sitemap_enabled' => 'true',
                'schema_enabled' => 'true',
                'breadcrumbs_enabled' => 'true',
                'amp_enabled' => 'false'
            ]
        ];
    }
}

// Servisi global olarak kullanılabilir hale getirelim
function settingsService() {
    return new SettingsService();
}
