<?php

require_once __DIR__ . '/../lib/SupabaseClient.php';

class SliderService {
    private $client;

    public function __construct() {
        $this->client = supabase();
    }

    /**
     * Tüm aktif sliderleri getir (frontend için)
     */
    public function getActiveSliders() {
        try {
            $query = 'slider_items?select=*&is_active=eq.true&order=sort_order.asc';
            $response = $this->client->request($query);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Slider getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tüm sliderleri getir (admin için)
     */
    public function getAllSliders() {
        try {
            $query = 'slider_items?select=*&order=sort_order.asc';
            $response = $this->client->request($query);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Tüm sliderleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ID'ye göre slider getir
     */
    public function getSliderById($id) {
        try {
            $query = 'slider_items?id=eq.' . intval($id) . '&select=*';
            $response = $this->client->request($query);
            $result = $response['body'] ?? [];
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Slider getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Yeni slider oluştur
     */
    public function createSlider($data) {
        try {
            // Son sıra numarasını al
            $lastOrder = $this->getLastSortOrder();
            $data['sort_order'] = $lastOrder + 1;
            
            $response = $this->client->request('slider_items', 'POST', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Slider oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Slider güncelle
     */
    public function updateSlider($id, $data) {
        try {
            $query = 'slider_items?id=eq.' . intval($id);
            $response = $this->client->request($query, 'PATCH', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Slider güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Slider sil
     */
    public function deleteSlider($id) {
        try {
            $query = 'slider_items?id=eq.' . intval($id);
            $response = $this->client->request($query, 'DELETE');
            return !empty($response);
        } catch (Exception $e) {
            error_log("Slider silme hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Slider durumunu değiştir (aktif/pasif)
     */
    public function toggleSliderStatus($id) {
        try {
            // Önce mevcut durumu al
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

    /**
     * Slider sıralamasını güncelle
     */
    public function updateSliderOrder($orderData) {
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

    /**
     * Son sıra numarasını getir
     */
    private function getLastSortOrder() {
        try {
            $query = 'slider_items?select=sort_order&order=sort_order.desc&limit=1';
            $response = $this->client->request($query);
            $result = $response['body'] ?? [];
            return !empty($result) ? $result[0]['sort_order'] : 0;
        } catch (Exception $e) {
            error_log("Son sıra numarası getirme hatası: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Slider istatistiklerini getir
     */
    public function getSliderStats() {
        try {
            $allSliders = $this->getAllSliders();
            $activeSliders = array_filter($allSliders, function($slider) {
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
}

// Global helper function
function sliderService() {
    return new SliderService();
}
