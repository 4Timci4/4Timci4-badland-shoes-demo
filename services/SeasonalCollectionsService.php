<?php

require_once __DIR__ . '/../lib/DatabaseFactory.php';

class SeasonalCollectionsService {
    private $db;

    public function __construct() {
        $this->db = database();
    }

    /**
     * Aktif sezonluk koleksiyonları getirir
     * 
     * @return array Aktif koleksiyonlar dizisi
     */
    public function getActiveCollections() {
        try {
            return $this->db->select('seasonal_collections', [], '*', ['order' => 'sort_order ASC']);
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
            $result = $this->db->select('seasonal_collections', ['id' => intval($id)], '*', ['limit' => 1]);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Koleksiyon getirme hatası: " . $e->getMessage());
            return null;
        }
    }
}
