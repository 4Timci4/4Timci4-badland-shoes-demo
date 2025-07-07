<?php
/**
 * Database Interface
 * 
 * Farklı veritabanı sistemleri için ortak arayüz
 */

interface DatabaseInterface {
    /**
     * Veri seçme işlemi
     * 
     * @param string $table Tablo adı
     * @param array $conditions Koşullar ['column' => 'value', 'age' => ['>', 18]]
     * @param string|array $columns Seçilecek sütunlar
     * @param array $options Ek seçenekler (limit, offset, order, joins)
     * @return array Sonuç verisi
     */
    public function select($table, $conditions = [], $columns = '*', $options = []);
    
    /**
     * Veri ekleme işlemi
     * 
     * @param string $table Tablo adı
     * @param array $data Eklenecek veri
     * @param array $options Ek seçenekler (returning, conflict handling)
     * @return array Eklenen veri veya sonuç
     */
    public function insert($table, $data, $options = []);
    
    /**
     * Veri güncelleme işlemi
     * 
     * @param string $table Tablo adı
     * @param array $data Güncellenecek veri
     * @param array $conditions Güncelleme koşulları
     * @param array $options Ek seçenekler
     * @return array Güncellenen veri veya sonuç
     */
    public function update($table, $data, $conditions, $options = []);
    
    /**
     * Veri silme işlemi
     * 
     * @param string $table Tablo adı
     * @param array $conditions Silme koşulları
     * @param array $options Ek seçenekler
     * @return array Silme sonucu
     */
    public function delete($table, $conditions, $options = []);
    
    /**
     * Ham SQL sorgusu çalıştırma
     * 
     * @param string $sql SQL sorgusu
     * @param array $params Sorgu parametreleri
     * @return array Sorgu sonucu
     */
    public function executeRawSql($sql, $params = []);
    
    /**
     * İlişkili veri çekme (JOIN)
     * 
     * @param string $table Ana tablo
     * @param array $joins JOIN yapıları
     * @param array $conditions Koşullar
     * @param string|array $columns Seçilecek sütunlar
     * @param array $options Ek seçenekler
     * @return array Sonuç verisi
     */
    public function selectWithJoins($table, $joins = [], $conditions = [], $columns = '*', $options = []);
    
    /**
     * Sayfa bazlı veri çekme
     * 
     * @param string $table Tablo adı
     * @param array $conditions Koşullar
     * @param int $page Sayfa numarası (1'den başlar)
     * @param int $limit Sayfa başına kayıt sayısı
     * @param array $options Ek seçenekler (order, columns)
     * @return array ['data' => [], 'total' => int, 'page' => int, 'pages' => int]
     */
    public function paginate($table, $conditions = [], $page = 1, $limit = 10, $options = []);
    
    /**
     * Kayıt sayısı alma
     * 
     * @param string $table Tablo adı
     * @param array $conditions Koşullar
     * @return int Kayıt sayısı
     */
    public function count($table, $conditions = []);
    
    /**
     * Önbellek temizleme
     * 
     * @param string|null $key Belirli bir anahtar (null ise tümü)
     * @return void
     */
    public function clearCache($key = null);
    
    /**
     * Transaction başlatma
     * 
     * @return void
     */
    public function beginTransaction();
    
    /**
     * Transaction commit
     * 
     * @return void
     */
    public function commit();
    
    /**
     * Transaction rollback
     * 
     * @return void
     */
    public function rollback();
    
    /**
     * Bağlantı durumu kontrolü
     * 
     * @return bool
     */
    public function isConnected();
    
    /**
     * Son hata mesajını alma
     * 
     * @return string|null
     */
    public function getLastError();
}
