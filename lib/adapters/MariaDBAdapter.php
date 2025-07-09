<?php
/**
 * MariaDB Database Adapter
 * 
 * MariaDBClient'ı DatabaseInterface ile uyumlu hale getirir
 * Supabase REST API ile aynı interface'i sağlar
 */

require_once __DIR__ . '/../DatabaseInterface.php';

class MariaDBAdapter implements DatabaseInterface {
    private $mariadbClient;
    
    public function __construct($mariadbClient) {
        $this->mariadbClient = $mariadbClient;
    }
    
    /**
     * Veri seçme işlemi
     */
    public function select($table, $conditions = [], $columns = '*', $options = []) {
        return $this->mariadbClient->select($table, $conditions, $columns, $options);
    }
    
    /**
     * Veri ekleme işlemi
     */
    public function insert($table, $data, $options = []) {
        return $this->mariadbClient->insert($table, $data, $options);
    }
    
    /**
     * Veri ekleme veya değiştirme işlemi (duplicate hataları önler)
     */
    public function insertOrReplace($table, $data, $options = []) {
        return $this->mariadbClient->insertOrReplace($table, $data, $options);
    }
    
    /**
     * Upsert işlemi (INSERT ON DUPLICATE KEY UPDATE)
     */
    public function upsert($table, $data, $options = []) {
        return $this->mariadbClient->upsert($table, $data, $options);
    }
    
    /**
     * Veri güncelleme işlemi
     */
    public function update($table, $data, $conditions, $options = []) {
        return $this->mariadbClient->update($table, $data, $conditions, $options);
    }
    
    /**
     * Veri silme işlemi
     */
    public function delete($table, $conditions, $options = []) {
        return $this->mariadbClient->delete($table, $conditions, $options);
    }
    
    /**
     * Kayıt sayısı alma
     */
    public function count($table, $conditions = []) {
        return $this->mariadbClient->count($table, $conditions);
    }
    
    /**
     * Ham SQL sorgusu çalıştırma
     */
    public function executeRawSql($sql, $params = []) {
        return $this->mariadbClient->executeRawSql($sql, $params);
    }
    
    /**
     * İlişkili veri çekme (JOIN)
     */
    public function selectWithJoins($table, $joins = [], $conditions = [], $columns = '*', $options = []) {
        return $this->mariadbClient->selectWithJoins($table, $joins, $conditions, $columns, $options);
    }
    
    /**
     * Sayfa bazlı veri çekme
     */
    public function paginate($table, $conditions = [], $page = 1, $limit = 10, $options = []) {
        return $this->mariadbClient->paginate($table, $conditions, $page, $limit, $options);
    }
    
    /**
     * Önbellek temizleme
     */
    public function clearCache($key = null) {
        return $this->mariadbClient->clearCache($key);
    }
    
    /**
     * Transaction başlatma
     */
    public function beginTransaction() {
        return $this->mariadbClient->beginTransaction();
    }
    
    /**
     * Transaction commit
     */
    public function commit() {
        return $this->mariadbClient->commit();
    }
    
    /**
     * Transaction rollback
     */
    public function rollback() {
        return $this->mariadbClient->rollback();
    }
    
    /**
     * Bağlantı durumu kontrolü
     */
    public function isConnected() {
        return $this->mariadbClient->isConnected();
    }
    
    /**
     * Son hata mesajını alma
     */
    public function getLastError() {
        return $this->mariadbClient->getLastError();
    }
    
    /**
     * Supabase uyumlu request metodu
     * MariaDBClient'ın request metodunu kullanır
     */
    public function request($endpoint, $method = 'GET', $data = null, $headers = [], $useCache = true) {
        return $this->mariadbClient->request($endpoint, $method, $data, $headers, $useCache);
    }
    
    /**
     * Batch insert işlemi (MariaDB için optimize edilmiş)
     */
    public function batchInsert($table, $data, $options = []) {
        if (empty($data)) {
            return ['affected_rows' => 0];
        }
        
        try {
            $this->mariadbClient->beginTransaction();
            
            $totalAffected = 0;
            foreach ($data as $row) {
                $result = $this->mariadbClient->insert($table, $row, $options);
                $totalAffected += $result['affected_rows'] ?? 0;
            }
            
            $this->mariadbClient->commit();
            
            return ['affected_rows' => $totalAffected];
            
        } catch (Exception $e) {
            $this->mariadbClient->rollback();
            error_log("MariaDBAdapter::batchInsert - " . $e->getMessage());
            return ['affected_rows' => 0];
        }
    }
    
    /**
     * Batch update işlemi (MariaDB için optimize edilmiş)
     */
    public function batchUpdate($table, $updates, $options = []) {
        if (empty($updates)) {
            return ['affected_rows' => 0];
        }
        
        try {
            $this->mariadbClient->beginTransaction();
            
            $totalAffected = 0;
            foreach ($updates as $update) {
                $data = $update['data'];
                $conditions = $update['conditions'];
                
                $result = $this->mariadbClient->update($table, $data, $conditions, $options);
                $totalAffected += $result['affected_rows'] ?? 0;
            }
            
            $this->mariadbClient->commit();
            
            return ['affected_rows' => $totalAffected];
            
        } catch (Exception $e) {
            $this->mariadbClient->rollback();
            error_log("MariaDBAdapter::batchUpdate - " . $e->getMessage());
            return ['affected_rows' => 0];
        }
    }
    
    /**
     * Cache pattern silme (SimpleCache uyumlu)
     */
    public function deleteByPattern($pattern) {
        // MariaDB adapter için cache işlemi SimpleCache'e bırakılır
        // Bu metod interface uyumluluğu için tanımlanmıştır
        return true;
    }
    
    /**
     * Gelişmiş MariaDB özelliklerini kullanma
     */
    public function getMariaDBClient() {
        return $this->mariadbClient;
    }
}