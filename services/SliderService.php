<?php

require_once __DIR__ . '/../lib/SupabaseClient.php';

class SliderService {
    private $client;

    public function __construct() {
        $this->client = supabase();
    }

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
}
