<?php
/**
 * Database Factory
 * 
 * Yapılandırmaya göre uygun veritabanı istemcisini oluşturur
 */

require_once __DIR__ . '/DatabaseInterface.php';
require_once __DIR__ . '/SupabaseClient.php';
require_once __DIR__ . '/../config/env.php';

class DatabaseFactory {
    /**
     * Database türüne göre uygun client oluşturur
     * 
     * @param string $type Database türü ('supabase', 'mariadb')
     * @param array $config Özel yapılandırma
     * @return DatabaseInterface
     * @throws Exception Desteklenmeyen database türü için
     */
    public static function create($type = null, $config = []) {
        // Config'den type al, yoksa env'den
        if ($type === null) {
            $type = getenv('DB_TYPE') ?: 'supabase';
        }
        
        switch (strtolower($type)) {
            case 'supabase':
                return self::createSupabaseClient($config);
            
            case 'mariadb':
            case 'mysql':
                return self::createMariaDBClient($config);
            
            default:
                throw new Exception("Desteklenmeyen veritabanı türü: $type");
        }
    }
    
    /**
     * Supabase client oluşturur
     * 
     * @param array $config Özel yapılandırma
     * @return SupabaseAdapter
     */
    private static function createSupabaseClient($config = []) {
        // Mevcut SupabaseClient'ı adapter ile sarmalayacağız
        $baseUrl = $config['url'] ?? SUPABASE_URL;
        $apiKey = $config['key'] ?? SUPABASE_KEY;
        $options = $config['options'] ?? [
            'useCache' => true,
            'cacheExpiry' => 300
        ];
        
        $supabaseClient = new SupabaseClient($baseUrl, $apiKey, $options);
        
        // Supabase için adapter döndür
        require_once __DIR__ . '/adapters/SupabaseAdapter.php';
        return new SupabaseAdapter($supabaseClient);
    }
    
    /**
     * MariaDB client oluşturur
     * 
     * @param array $config Özel yapılandırma
     * @return MariaDBClient
     */
    private static function createMariaDBClient($config = []) {
        require_once __DIR__ . '/clients/MariaDBClient.php';
        
        // Default MariaDB config
        $defaultConfig = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: 3306,
            'database' => getenv('DB_NAME') ?: 'bandland_shoes',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];
        
        $finalConfig = array_merge($defaultConfig, $config);
        
        return new MariaDBClient($finalConfig);
    }
    
    /**
     * Mevcut database türünü döndürür
     * 
     * @return string
     */
    public static function getCurrentType() {
        return getenv('DB_TYPE') ?: 'supabase';
    }
    
    /**
     * Desteklenen database türlerini listeler
     * 
     * @return array
     */
    public static function getSupportedTypes() {
        return ['supabase', 'mariadb', 'mysql'];
    }
}

/**
 * Global database instance - singleton pattern
 * Bu fonksiyon mevcut supabase() fonksiyonunu değiştirecek
 */
function database() {
    static $instance = null;
    
    if ($instance === null) {
        try {
            $instance = DatabaseFactory::create();
        } catch (Exception $e) {
            error_log('Database bağlantı hatası: ' . $e->getMessage());
            // Kritik hata, uygulama durmalı
            http_response_code(500);
            echo 'Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.';
            exit;
        }
    }
    
    return $instance;
}

/**
 * Geriye uyumluluk için supabase() fonksiyonunu koruyalım
 * Geliştiriciler supabase() kullanmaya devam edebilir
 */
function supabase() {
    return database();
}
