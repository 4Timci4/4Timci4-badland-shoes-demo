<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class SliderService
{
    private $db;

    public function __construct()
    {
        $this->db = database();
    }

    public function getActiveSliders()
    {
        try {
            return $this->db->select('slider_items', ['is_active' => 1], '*', ['order' => 'sort_order ASC']);
        } catch (Exception $e) {
            error_log("Slider getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    public function getAllSliders()
    {
        try {
            return $this->db->select('slider_items', [], '*', ['order' => 'sort_order ASC']);
        } catch (Exception $e) {
            error_log("Tüm sliderleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    public function getSliderById($id)
    {
        try {
            $result = $this->db->select('slider_items', ['id' => intval($id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Slider getirme hatası: " . $e->getMessage());
            return null;
        }
    }

    public function createSlider($data)
    {
        try {
            $lastOrder = $this->getLastSortOrder();
            $data['sort_order'] = $lastOrder + 1;
            $data['created_at'] = date('Y-m-d H:i:s');

            $result = $this->db->insert('slider_items', $data);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Slider oluşturma hatası: " . $e->getMessage());
            return false;
        }
    }

    public function updateSlider($id, $data)
    {
        try {
            $result = $this->db->update('slider_items', $data, ['id' => intval($id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Slider güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSlider($id)
    {
        try {
            $result = $this->db->delete('slider_items', ['id' => intval($id)]);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Slider silme hatası: " . $e->getMessage());
            return false;
        }
    }

    public function toggleSliderStatus($id)
    {
        try {
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

    public function updateSliderOrder($orderData)
    {
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

    private function getLastSortOrder()
    {
        try {
            $result = $this->db->select('slider_items', [], 'sort_order', ['order' => 'sort_order DESC', 'limit' => 1]);
            return !empty($result) ? $result[0]['sort_order'] : 0;
        } catch (Exception $e) {
            error_log("Son sıra numarası getirme hatası: " . $e->getMessage());
            return 0;
        }
    }

    public function getSliderStats()
    {
        try {
            $allSliders = $this->getAllSliders();
            $activeSliders = array_filter($allSliders, function ($slider) {
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

function sliderService()
{
    return new SliderService();
}
