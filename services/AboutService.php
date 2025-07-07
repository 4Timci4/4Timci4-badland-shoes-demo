<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class AboutService {
    private $db;

    public function __construct() {
        $this->db = database();
    }

    public function getAboutPageContent() {
        $settings = $this->getSettings();
        $values = $this->getContentBlocks('values');
        $team = $this->getContentBlocks('team');

        return [
            'settings' => $settings,
            'values' => $values,
            'team' => $team,
        ];
    }

    public function getHomePageAboutSection() {
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

    /**
     * Settings (about_settings) CRUD işlemleri
     */
    
    // Setting güncelle
    public function updateSetting($meta_key, $meta_value, $section = null) {
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

    // Birden fazla setting'i güncelle
    public function updateMultipleSettings($settings) {
        $success = true;
        foreach ($settings as $meta_key => $meta_value) {
            if (!$this->updateSetting($meta_key, $meta_value)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Content Blocks (about_content_blocks) CRUD işlemleri
     */
    
    // Yeni content block oluştur
    public function createContentBlock($data) {
        try {
            // Son sıra numarasını al
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

    // Content block güncelle
    public function updateContentBlock($id, $data) {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->db->update('about_content_blocks', $data, ['id' => intval($id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Content block güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    // Content block sil
    public function deleteContentBlock($id) {
        try {
            $result = $this->db->delete('about_content_blocks', ['id' => intval($id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Content block silme hatası: " . $e->getMessage());
            return false;
        }
    }

    // ID'ye göre content block getir
    public function getContentBlockById($id) {
        try {
            $result = $this->db->select('about_content_blocks', ['id' => intval($id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Content block getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    // Content block sıralamasını güncelle
    public function updateContentBlockOrder($section, $orderData) {
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

    /**
     * Helper metodlar
     */
    
    // Son sıra numarasını getir
    private function getLastSortOrder($section) {
        try {
            $result = $this->db->select('about_content_blocks', ['section' => $section], 'sort_order', ['order' => 'sort_order DESC', 'limit' => 1]);
            return !empty($result) ? $result[0]['sort_order'] : 0;
        } catch (Exception $e) {
            error_log("Son sıra numarası getirme hatası: " . $e->getMessage());
            return 0;
        }
    }

    // İstatistikleri getir
    public function getAboutStats() {
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

    // Son güncelleme tarihini getir
    private function getLastUpdatedDate() {
        try {
            $result = $this->db->select('about_settings', [], 'updated_at', ['order' => 'updated_at DESC', 'limit' => 1]);
            return !empty($result) ? $result[0]['updated_at'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function getSettings() {
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

    private function getContentBlocks($section) {
        try {
            return $this->db->select('about_content_blocks', ['section' => $section], '*', ['order' => 'sort_order ASC']);
        } catch (Exception $e) {
            error_log("Hakkımızda içerik blokları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}

// Global helper function
function aboutService() {
    return new AboutService();
}
