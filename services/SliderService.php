<?php

require_once __DIR__ . '/../lib/SupabaseClient.php';

class SliderService {
    private $client;

    public function __construct() {
        $this->client = supabase();
    }

    public function getActiveSliders() {
        $query = [
            'select' => '*',
            'is_active' => 'eq.true',
            'order' => 'sort_order.asc'
        ];
        return $this->client->request('slider_items?' . http_build_query($query));
    }
}
