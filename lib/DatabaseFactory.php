<?php


require_once __DIR__ . '/DatabaseInterface.php';
require_once __DIR__ . '/SupabaseClient.php';
require_once __DIR__ . '/../config/env.php';

class DatabaseFactory
{

    public static function create($type = null, $config = [])
    {

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


    private static function createSupabaseClient($config = [])
    {

        $baseUrl = $config['url'] ?? SUPABASE_URL;
        $apiKey = $config['key'] ?? SUPABASE_KEY;
        $options = $config['options'] ?? [
            'useCache' => true,
            'cacheExpiry' => 1
        ];

        $supabaseClient = new SupabaseClient($baseUrl, $apiKey, $options);


        require_once __DIR__ . '/adapters/SupabaseAdapter.php';
        return new SupabaseAdapter($supabaseClient);
    }


    private static function createMariaDBClient($config = [])
    {
        require_once __DIR__ . '/clients/MariaDBClient.php';


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


    public static function getCurrentType()
    {
        return getenv('DB_TYPE') ?: 'supabase';
    }


    public static function getSupportedTypes()
    {
        return ['supabase', 'mariadb', 'mysql'];
    }
}


function database()
{
    static $instance = null;

    if ($instance === null) {
        try {
            $instance = DatabaseFactory::create();
        } catch (Exception $e) {
            error_log('Database bağlantı hatası: ' . $e->getMessage());

            http_response_code(500);
            echo 'Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.';
            exit;
        }
    }

    return $instance;
}


function supabase()
{
    return database();
}
