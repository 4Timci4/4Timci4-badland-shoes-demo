<?php


require_once __DIR__ . '/../lib/DatabaseFactory.php';

class SettingsService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }


    public function getSiteSetting($key, $default = '')
    {
        try {
            $result = $this->db->select('site_settings', ['setting_key' => $key], 'setting_value', ['limit' => 1]);

            if (!empty($result)) {
                return $result[0]['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Site ayarı getirme hatası: " . $e->getMessage());
        }

        return $default;
    }


    public function updateSiteSetting($key, $value, $group = 'general', $description = '')
    {
        try {
            $existing = $this->db->select('site_settings', ['setting_key' => $key], 'setting_key', ['limit' => 1]);

            $data = [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_group' => $group,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($existing)) {
                $result = $this->db->update('site_settings', $data, ['setting_key' => $key]);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $result = $this->db->insert('site_settings', $data);
            }

            return $result !== false;
        } catch (Exception $e) {
            error_log("Site ayarı güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function getSettingsByGroup($group)
    {
        require_once __DIR__ . '/../config/cache.php';

        $cacheKey = "settings_{$group}";

        return CacheConfig::get($cacheKey, function () use ($group) {
            try {
                $result = $this->db->select('site_settings', ['setting_group' => $group], '*', ['order' => 'setting_key ASC']);

                if (!empty($result)) {
                    $settings = [];
                    foreach ($result as $setting) {
                        $settings[$setting['setting_key']] = $setting['setting_value'];
                    }
                    return $settings;
                }
            } catch (Exception $e) {
                error_log("Grup ayarları getirme hatası: " . $e->getMessage());
            }

            return [];
        });
    }


    public function updateMultipleSettings($settings, $group = 'general')
    {
        $success_count = 0;

        foreach ($settings as $key => $value) {
            if ($this->updateSiteSetting($key, $value, $group)) {
                $success_count++;
            }
        }

        if ($success_count > 0) {
            require_once __DIR__ . '/../config/cache.php';
            $cacheKey = "settings_{$group}";
            CacheConfig::clear($cacheKey);
        }

        return $success_count > 0;
    }


    public function getSeoSetting($key, $default = '')
    {
        try {
            $result = $this->db->select('seo_settings', ['setting_key' => $key], 'setting_value', ['limit' => 1]);

            if (!empty($result)) {
                return $result[0]['setting_value'];
            }
        } catch (Exception $e) {
            error_log("SEO ayarı getirme hatası: " . $e->getMessage());
        }

        return $default;
    }


    public function updateSeoSetting($key, $value, $type = 'meta', $is_active = true)
    {
        try {
            $existing = $this->db->select('seo_settings', ['setting_key' => $key], 'setting_key', ['limit' => 1]);

            $data = [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => $type,
                'is_active' => $is_active,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($existing)) {
                $result = $this->db->update('seo_settings', $data, ['setting_key' => $key]);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $result = $this->db->insert('seo_settings', $data);
            }

            if ($result !== false) {
                require_once __DIR__ . '/../config/cache.php';
                $cacheKey = "seo_settings_{$type}";
                CacheConfig::clear($cacheKey);
            }

            return $result !== false;
        } catch (Exception $e) {
            error_log("SEO ayarı güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }


    public function getSeoSettingsByType($type)
    {
        require_once __DIR__ . '/../config/cache.php';
        $cacheKey = "seo_settings_{$type}";

        return CacheConfig::get($cacheKey, function () use ($type) {
            try {
                $result = $this->db->select('seo_settings', ['setting_type' => $type], '*', ['order' => 'setting_key ASC']);

                if (!empty($result)) {
                    $settings = [];
                    foreach ($result as $setting) {
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
        });
    }


    public function getAllSeoSettings()
    {
        try {
            $result = $this->db->select('seo_settings', [], '*', ['order' => 'setting_type ASC, setting_key ASC']);

            if (!empty($result)) {
                $settings = [];
                foreach ($result as $setting) {
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


    public function getDefaultSiteSettings()
    {
        return [

            'site_name' => 'Bandland Shoes',
            'site_tagline' => 'Premium Ayakkabı Mağazası',
            'site_description' => 'Kaliteli ve şık ayakkabılar için doğru adres. En yeni modeller ve uygun fiyatlar ile hizmetinizdeyiz.',
            'site_logo' => 'assets/images/logo.png',
            'site_favicon' => 'assets/images/favicon.ico',
            'primary_color' => '#e91e63',
            'secondary_color' => '#2c2c54',
            'footer_copyright' => '© 2025 Bandland Shoes. Tüm hakları saklıdır.',


            'products_per_page' => '12',
            'blogs_per_page' => '10',
            'maintenance_mode' => 'false',
            'site_language' => 'tr',
            'timezone' => 'Europe/Istanbul',


            'meta_title' => 'Bandland Shoes - Premium Ayakkabı Mağazası',
            'meta_description' => 'Kaliteli ve şık ayakkabılar için doğru adres. En yeni modeller ve uygun fiyatlar ile hizmetinizdeyiz.',
            'meta_keywords' => 'ayakkabı, spor ayakkabı, klasik ayakkabı, kadın ayakkabı, erkek ayakkabı, çocuk ayakkabı',
            'canonical_url' => '',
            'robots_txt' => "User-agent: *\nDisallow: /admin/\nDisallow: /api/\nSitemap: /sitemap.xml",


            'og_title' => 'Bandland Shoes - Premium Ayakkabı Mağazası',
            'og_description' => 'Kaliteli ve şık ayakkabılar için doğru adres.',
            'og_image' => 'assets/images/og-image.jpg',
            'twitter_card' => 'summary_large_image',
            'twitter_site' => '@bandlandshoes',


            'google_analytics_id' => '',
            'google_tag_manager_id' => '',
            'facebook_pixel_id' => '',
            'hotjar_id' => '',


            'contact_email' => 'info@bandlandshoes.com',
            'contact_phone' => '+90 212 123 45 67',
            'contact_address' => 'İstanbul, Türkiye',
            'business_hours' => 'Pazartesi - Cumartesi: 09:00 - 18:00',


            'currency' => 'TRY',
            'currency_symbol' => '₺',
            'tax_rate' => '18',
            'free_shipping_limit' => '500',
            'stock_alert_limit' => '10'
        ];
    }


    public function getDefaultSeoSettings()
    {
        return [
            'meta' => [
                'meta_title' => 'Bandland Shoes - Premium Ayakkabı Mağazası',
                'meta_description' => 'Kaliteli ve şık ayakkabılar için doğru adres. En yeni modeller ve uygun fiyatlar ile hizmetinizdeyiz.',
                'meta_keywords' => 'ayakkabı, spor ayakkabı, klasik ayakkabı, kadın ayakkabı, erkek ayakkabı, çocuk ayakkabı, online ayakkabı mağazası, türkiye',
                'meta_author' => 'Bandland Shoes',
                'meta_robots' => 'index, follow',
                'canonical_url' => '',
                'canonical_enabled' => 'true'
            ],
            'social' => [

                'og_title' => 'Bandland Shoes - Premium Ayakkabı Mağazası',
                'og_description' => 'Kaliteli ve şık ayakkabılar için doğru adres. En yeni modeller ve uygun fiyatlar ile hizmetinizdeyiz.',
                'og_image' => 'assets/images/og-image.jpg',
                'og_url' => '',
                'og_type' => 'website',
                'og_site_name' => 'Bandland Shoes',
                'og_locale' => 'tr_TR',


                'twitter_card' => 'summary_large_image',
                'twitter_site' => '@bandlandshoes',
                'twitter_creator' => '@bandlandshoes',
                'twitter_title' => 'Bandland Shoes - Premium Ayakkabı Mağazası',
                'twitter_description' => 'Kaliteli ve şık ayakkabılar için doğru adres.',
                'twitter_image' => 'assets/images/twitter-image.jpg'
            ],
            'analytics' => [
                'google_analytics_id' => '',
                'google_tag_manager_id' => '',
                'google_site_verification' => '',
                'bing_site_verification' => '',
                'yandex_site_verification' => '',
                'facebook_pixel_id' => '',
                'hotjar_id' => '',
                'google_ads_conversion_id' => ''
            ],
            'technical' => [
                'robots_txt' => "User-agent: *\nDisallow: /admin/\nDisallow: /api/\nDisallow: /includes/\nDisallow: /config/\nSitemap: /sitemap.xml",
                'sitemap_enabled' => 'true',
                'breadcrumbs_enabled' => 'true',
                'hreflang_enabled' => 'false',
                'amp_enabled' => 'false',
                'schema_enabled' => 'true',


                'schema_organization_name' => 'Bandland Shoes',
                'schema_organization_type' => 'ShoeStore',
                'schema_organization_url' => '',
                'schema_organization_logo' => 'assets/images/logo.png',
                'schema_organization_description' => 'Premium ayakkabı mağazası',
                'schema_organization_email' => 'info@bandlandshoes.com',
                'schema_organization_phone' => '+90 212 123 45 67',
                'schema_organization_address' => 'İstanbul, Türkiye',


                'local_business_enabled' => 'true',
                'business_name' => 'Bandland Shoes',
                'business_type' => 'ShoeStore',
                'business_address' => 'İstanbul, Türkiye',
                'business_phone' => '+90 212 123 45 67',
                'business_email' => 'info@bandlandshoes.com',
                'business_hours' => 'Mo-Sa 09:00-18:00',


                'product_schema_enabled' => 'true',
                'review_schema_enabled' => 'true',
                'availability_schema_enabled' => 'true'
            ]
        ];
    }
}


function settingsService()
{
    return new SettingsService();
}
