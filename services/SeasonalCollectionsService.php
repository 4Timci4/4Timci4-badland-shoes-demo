<?php

require_once __DIR__ . '/../lib/SupabaseClient.php';

class SeasonalCollectionsService {
    private $client;

    public function __construct() {
        $this->client = supabase();
    }

    /**
     * Aktif sezonluk koleksiyonları getirir
     * 
     * @return array Aktif koleksiyonlar dizisi
     */
    public function getActiveCollections() {
        try {
            // Cache'i temizle
            $this->client->clearCache();
            
            $query = 'seasonal_collections?select=*&order=sort_order.asc';
            $response = $this->client->request($query, 'GET', null, []);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Sezonluk koleksiyonlar getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Belirtilen ID'deki koleksiyonu getirir
     * 
     * @param int $id Koleksiyon ID'si
     * @return array|null Koleksiyon verisi veya null
     */
    public function getCollectionById($id) {
        try {
            $query = 'seasonal_collections?select=*&id=eq.' . intval($id);
            $headers = ['Prefer' => 'return=representation'];
            $response = $this->client->request($query, 'GET', null, $headers);
            $data = $response['body'] ?? [];
            return !empty($data) ? $data[0] : null;
        } catch (Exception $e) {
            error_log("Koleksiyon getirme hatası: " . $e->getMessage());
            return null;
        }
    }
}
