<?php
/**
 * MariaDB/MySQL Client
 * 
 * PDO kullanarak MariaDB/MySQL veritabanı işlemleri
 */

require_once __DIR__ . '/../DatabaseInterface.php';

class MariaDBClient implements DatabaseInterface {
    private $pdo;
    private $config;
    private $lastError = null;
    private $inTransaction = false;
    private $cache = [];
    private $cacheEnabled = true;
    private $cacheExpiry = 1; // 5 dakika
    
    public function __construct($config) {
        $this->config = $config;
        $this->connect();
    }
    
    /**
     * Veritabanına bağlan
     */
    private function connect() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );
            
            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            throw new Exception("MariaDB bağlantı hatası: " . $e->getMessage());
        }
    }
    
    /**
     * Veri seçme işlemi
     */
    public function select($table, $conditions = [], $columns = '*', $options = []) {
        try {
            $sql = $this->buildSelectSql($table, $conditions, $columns, $options);
            
            // WHERE koşulları varsa parametreleri al
            $params = [];
            if (!empty($conditions)) {
                $whereClause = $this->buildWhereClause($conditions);
                $params = $whereClause['params'];
            }
            
            // Cache kontrolü
            if ($this->cacheEnabled && empty($options['no_cache'])) {
                $cacheKey = md5($sql . serialize($params));
                $cached = $this->getCache($cacheKey);
                if ($cached !== null) {
                    return $cached;
                }
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll();
            
            // Cache'e kaydet
            if ($this->cacheEnabled && empty($options['no_cache'])) {
                $this->setCache($cacheKey, $result);
            }
            
            return $result;
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::select - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Veri ekleme işlemi
     */
    public function insert($table, $data, $options = []) {
        try {
            $columns = array_keys($data);
            $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
            
            $sql = sprintf(
                'INSERT INTO `%s` (`%s`) VALUES (%s)',
                $table,
                implode('`, `', $columns),
                implode(', ', $placeholders)
            );
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            // Returning seçeneği varsa, eklenen veriyi döndür
            if (isset($options['returning'])) {
                $lastId = $this->pdo->lastInsertId();
                if ($lastId) {
                    return $this->select($table, ['id' => $lastId]);
                }
            }
            
            return ['affected_rows' => $stmt->rowCount()];
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::insert - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Veri ekleme veya değiştirme işlemi (REPLACE INTO - duplicate hataları önler)
     */
    public function insertOrReplace($table, $data, $options = []) {
        try {
            $columns = array_keys($data);
            $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
            
            $sql = sprintf(
                'REPLACE INTO `%s` (`%s`) VALUES (%s)',
                $table,
                implode('`, `', $columns),
                implode(', ', $placeholders)
            );
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return ['affected_rows' => $stmt->rowCount()];
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::insertOrReplace - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Upsert işlemi (INSERT ON DUPLICATE KEY UPDATE)
     */
    public function upsert($table, $data, $options = []) {
        try {
            $columns = array_keys($data);
            $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
            
            // UPDATE kısmı için sütunları hazırla (id hariç)
            $updateParts = [];
            foreach ($columns as $column) {
                if ($column !== 'id') { // Primary key'i güncelleme
                    $updateParts[] = "`$column` = VALUES(`$column`)";
                }
            }
            
            $sql = sprintf(
                'INSERT INTO `%s` (`%s`) VALUES (%s)',
                $table,
                implode('`, `', $columns),
                implode(', ', $placeholders)
            );
            
            if (!empty($updateParts)) {
                $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updateParts);
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return ['affected_rows' => $stmt->rowCount()];
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::upsert - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Veri güncelleme işlemi
     */
    public function update($table, $data, $conditions, $options = []) {
        try {
            $setParts = [];
            foreach (array_keys($data) as $column) {
                $setParts[] = "`$column` = :set_$column";
            }
            
            $whereClause = $this->buildWhereClause($conditions, 'where_');
            
            $sql = sprintf(
                'UPDATE `%s` SET %s WHERE %s',
                $table,
                implode(', ', $setParts),
                $whereClause['sql']
            );
            
            // Parametreleri birleştir
            $params = [];
            foreach ($data as $key => $value) {
                // Boolean değerleri manuel olarak 1/0'a dönüştür
                if (is_bool($value)) {
                    $params['set_' . $key] = $value ? 1 : 0;
                } else {
                    $params['set_' . $key] = $value;
                }
            }
            $params = array_merge($params, $whereClause['params']);
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Returning seçeneği varsa, güncellenen veriyi döndür
            if (isset($options['returning'])) {
                return $this->select($table, $conditions);
            }
            
            return ['affected_rows' => $stmt->rowCount()];
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::update - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Veri silme işlemi
     */
    public function delete($table, $conditions, $options = []) {
        try {
            // Returning seçeneği varsa, önce veriyi al
            $returningData = [];
            if (isset($options['returning'])) {
                $returningData = $this->select($table, $conditions);
            }
            
            $whereClause = $this->buildWhereClause($conditions);
            
            $sql = sprintf(
                'DELETE FROM `%s` WHERE %s',
                $table,
                $whereClause['sql']
            );
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($whereClause['params']);
            
            if (isset($options['returning'])) {
                return $returningData;
            }
            
            return ['affected_rows' => $stmt->rowCount()];
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::delete - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ham SQL sorgusu çalıştırma
     */
    public function executeRawSql($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // SELECT sorgusu ise sonuçları döndür
            if (stripos(trim($sql), 'SELECT') === 0) {
                return $stmt->fetchAll();
            }
            
            return ['affected_rows' => $stmt->rowCount()];
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::executeRawSql - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * İlişkili veri çekme (JOIN)
     */
    public function selectWithJoins($table, $joins = [], $conditions = [], $columns = '*', $options = []) {
        try {
            $sql = "SELECT ";
            
            // Sütunları ayarla
            if (is_array($columns)) {
                $sql .= implode(', ', $columns);
            } else {
                $sql .= $columns;
            }
            
            $sql .= " FROM `$table`";
            
            // JOIN'ları ekle
            foreach ($joins as $join) {
                $joinType = strtoupper($join['type'] ?? 'INNER');
                $joinTable = $join['table'];
                $joinCondition = $join['condition'];
                
                $sql .= " $joinType JOIN `$joinTable` ON $joinCondition";
            }
            
            // WHERE koşulları
            if (!empty($conditions)) {
                $whereClause = $this->buildWhereClause($conditions);
                $sql .= " WHERE " . $whereClause['sql'];
                $params = $whereClause['params'];
            } else {
                $params = [];
            }
            
            // ORDER BY
            if (isset($options['order'])) {
                $sql .= " ORDER BY " . $options['order'];
            }
            
            // LIMIT ve OFFSET
            if (isset($options['limit'])) {
                $sql .= " LIMIT " . intval($options['limit']);
                if (isset($options['offset'])) {
                    $sql .= " OFFSET " . intval($options['offset']);
                }
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::selectWithJoins - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Sayfa bazlı veri çekme
     */
    public function paginate($table, $conditions = [], $page = 1, $limit = 10, $options = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Toplam sayıyı al
            $total = $this->count($table, $conditions);
            
            // Verileri al
            $dataOptions = array_merge($options, [
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $data = $this->select($table, $conditions, $options['columns'] ?? '*', $dataOptions);
            
            $totalPages = $limit > 0 ? ceil($total / $limit) : 0;
            
            return [
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => $totalPages
            ];
            
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::paginate - " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'pages' => 0
            ];
        }
    }
    
    /**
     * Kayıt sayısı alma
     */
    public function count($table, $conditions = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM `$table`";
            $params = [];
            
            if (!empty($conditions)) {
                $whereClause = $this->buildWhereClause($conditions);
                $sql .= " WHERE " . $whereClause['sql'];
                $params = $whereClause['params'];
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return intval($result['total']);
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("MariaDBClient::count - " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Önbellek temizleme
     */
    public function clearCache($key = null) {
        if ($key === null) {
            $this->cache = [];
        } else {
            unset($this->cache[$key]);
        }
    }
    
    /**
     * Transaction başlatma
     */
    public function beginTransaction() {
        $this->pdo->beginTransaction();
        $this->inTransaction = true;
    }
    
    /**
     * Transaction commit
     */
    public function commit() {
        $this->pdo->commit();
        $this->inTransaction = false;
    }
    
    /**
     * Transaction rollback
     */
    public function rollback() {
        $this->pdo->rollback();
        $this->inTransaction = false;
    }
    
    /**
     * Bağlantı durumu kontrolü
     */
    public function isConnected() {
        try {
            $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Son hata mesajını alma
     */
    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * PDO instance'a erişim (gelişmiş kullanım için)
     */
    public function getPdo() {
        return $this->pdo;
    }
    
    /**
     * SELECT SQL sorgusu oluşturur
     */
    private function buildSelectSql($table, $conditions, $columns, $options) {
        $sql = "SELECT ";
        
        // Sütunları ayarla
        if (is_array($columns)) {
            $sql .= implode(', ', $columns);
        } else {
            $sql .= $columns;
        }
        
        $sql .= " FROM `$table`";
        
        // WHERE koşulları
        if (!empty($conditions)) {
            $whereClause = $this->buildWhereClause($conditions);
            $sql .= " WHERE " . $whereClause['sql'];
        }
        
        // ORDER BY
        if (isset($options['order'])) {
            $sql .= " ORDER BY " . $options['order'];
        }
        
        // LIMIT ve OFFSET
        if (isset($options['limit'])) {
            $sql .= " LIMIT " . intval($options['limit']);
            if (isset($options['offset'])) {
                $sql .= " OFFSET " . intval($options['offset']);
            }
        }
        
        return $sql;
    }
    
    /**
     * WHERE koşulları oluşturur
     */
    private function buildWhereClause($conditions, $prefix = '') {
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $paramKey = $prefix . $key;
            
            if (is_array($value)) {
                // Operatör bazlı koşul ['>', 18]
                if (count($value) >= 2) {
                    $operator = strtoupper($value[0]);
                    $val = $value[1];

                    // Supabase operatörlerini SQL operatörlerine normalize et
                    $operator_map = [
                        'EQ' => '=',
                        'NEQ' => '!=',
                        'GT' => '>',
                        'GTE' => '>=',
                        'LT' => '<',
                        'LTE' => '<=',
                        'LIKE' => 'LIKE',
                        'ILIKE' => 'LIKE',
                        'IN' => 'IN',
                        '&&' => 'LIKE' // PostgreSQL array overlap operatörünü LIKE'a dönüştür
                    ];
                    
                    if (isset($operator_map[$operator])) {
                        $operator = $operator_map[$operator];
                    }

                    $allowed_operators = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'IN'];
                    if (!in_array($operator, $allowed_operators)) {
                        // && operatörü için özel log
                        if ($operator === '&&') {
                            error_log("MariaDBClient: PostgreSQL array overlap operatörü (&&) LIKE operatörüne dönüştürüldü");
                        } else {
                            throw new Exception("Geçersiz operatör: $operator");
                        }
                    }
                    
                    if ($operator === 'IN') {
                        // IN operatörü için özel işlem
                        if (is_array($val) && !empty($val)) {
                            $inPlaceholders = [];
                            foreach ($val as $i => $inVal) {
                                $inKey = $paramKey . '_in_' . $i;
                                $inPlaceholders[] = ':' . $inKey;
                                $params[$inKey] = $inVal;
                            }
                            $whereParts[] = "`$key` IN (" . implode(',', $inPlaceholders) . ")";
                        }
                    } else {
                        $whereParts[] = "`$key` $operator :$paramKey";
                        $params[$paramKey] = $val;
                    }
                }
            } else {
                // Basit eşitlik
                $whereParts[] = "`$key` = :$paramKey";
                $params[$paramKey] = $value;
            }
        }
        
        return [
            'sql' => implode(' AND ', $whereParts),
            'params' => $params
        ];
    }
    
    
    /**
     * Önbellekten veri çeker
     */
    private function getCache($key) {
        if (!$this->cacheEnabled || !isset($this->cache[$key])) {
            return null;
        }
        
        $cached = $this->cache[$key];
        
        // Süre kontrolü
        if (time() - $cached['time'] > $this->cacheExpiry) {
            unset($this->cache[$key]);
            return null;
        }
        
        return $cached['data'];
    }
    
    /**
     * Veriyi önbelleğe kaydeder
     */
    private function setCache($key, $data) {
        if (!$this->cacheEnabled) {
            return;
        }
        
        $this->cache[$key] = [
            'data' => $data,
            'time' => time()
        ];
        
        // Cache boyutunu kontrol et (max 100 entry)
        if (count($this->cache) > 100) {
            // En eski 10 entry'yi sil
            $this->cache = array_slice($this->cache, 10, null, true);
        }
    }
    
    /**
     * Supabase uyumlu request metodu
     * REST API formatını MariaDB sorgularına dönüştürür
     */
    public function request($endpoint, $method = 'GET', $data = null, $headers = [], $useCache = true) {
        try {
            // Endpoint'i parse et
            $parsed = $this->parseSupabaseEndpoint($endpoint);
            $table = $parsed['table'];
            $conditions = $parsed['conditions'];
            $columns = $parsed['columns'];
            
            switch (strtoupper($method)) {
                case 'GET':
                    $result = $this->select($table, $conditions, $columns);
                    return ['body' => $result];
                    
                case 'POST':
                    $result = $this->insert($table, $data, ['returning' => true]);
                    return ['body' => $result];
                    
                case 'PATCH':
                    $result = $this->update($table, $data, $conditions, ['returning' => true]);
                    return ['body' => $result];
                    
                case 'DELETE':
                    $result = $this->delete($table, $conditions, ['returning' => true]);
                    return ['body' => $result];
                    
                default:
                    throw new Exception("Desteklenmeyen HTTP metodu: $method");
            }
            
        } catch (Exception $e) {
            error_log("MariaDBClient::request - " . $e->getMessage());
            return ['body' => []];
        }
    }
    
    /**
     * Supabase endpoint'ini parse eder
     */
    private function parseSupabaseEndpoint($endpoint) {
        // Örnek: product_categories?select=category_id&product_id=eq.123
        $parts = explode('?', $endpoint);
        $table = $parts[0];
        $conditions = [];
        $columns = ['*'];
        
        if (isset($parts[1])) {
            parse_str($parts[1], $params);
            
            // Select parametresi
            if (isset($params['select'])) {
                $columns = explode(',', $params['select']);
            }
            
            // Diğer parametreler koşul olarak işlenir
            foreach ($params as $key => $value) {
                if ($key === 'select') continue;
                
                // eq.123 formatını parse et
                if (strpos($value, 'eq.') === 0) {
                    $conditions[$key] = ['=', substr($value, 3)];
                } elseif (strpos($value, 'gt.') === 0) {
                    $conditions[$key] = ['>', substr($value, 3)];
                } elseif (strpos($value, 'lt.') === 0) {
                    $conditions[$key] = ['<', substr($value, 3)];
                } elseif (strpos($value, 'gte.') === 0) {
                    $conditions[$key] = ['>=', substr($value, 4)];
                } elseif (strpos($value, 'lte.') === 0) {
                    $conditions[$key] = ['<=', substr($value, 4)];
                } elseif (strpos($value, 'in.') === 0) {
                    $values = explode(',', substr($value, 3));
                    $conditions[$key] = ['IN', $values];
                } else {
                    $conditions[$key] = $value;
                }
            }
        }
        
        return [
            'table' => $table,
            'conditions' => $conditions,
            'columns' => $columns
        ];
    }
}
