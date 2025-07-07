<?php
/**
 * Ayarlar Servisi
 * 
 * Site genel ayarları ve SEO ayarlarını veritabanından yönetir.
 */

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class SettingsService {
    private $db;

    public function __construct() {
        $this->db = database();
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
            $result = $this->db->select('site_settings', ['setting_key' => $key], 'setting_value', ['limit' => 1]);
            
            if (!empty($result)) {
                return $result[0]['setting_value'];
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

    /**
     * Grup bazında ayarları getir
     *
     * @param string $group Ayar grubu
     * @return array Ayarlar
     */
    public function getSettingsByGroup($group) {
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
            $result = $this->db->select('seo_settings', ['setting_key' => $key], 'setting_value', ['limit' => 1]);
            
            if (!empty($result)) {
                return $result[0]['setting_value'];
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
            
            return $result !== false;
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
    }

    /**
     * Tüm SEO ayarları getir
     *
     * @return array Tüm SEO ayarları
     */
    public function getAllSeoSettings() {
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
}

// Servisi global olarak kullanılabilir hale getirelim
function settingsService() {
    return new SettingsService();
}
