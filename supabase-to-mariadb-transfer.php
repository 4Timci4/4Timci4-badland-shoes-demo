<?php
/**
 * Supabase to MariaDB Transfer Script
 * 
 * Bu script tüm tabloları, verilerini ve materialized viewleri
 * Supabase'den MariaDB'ye transfer eder.
 */

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gerekli dosyalar
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/lib/DatabaseFactory.php';
require_once __DIR__ . '/lib/SupabaseClient.php';
require_once __DIR__ . '/lib/adapters/SupabaseAdapter.php';
require_once __DIR__ . '/lib/adapters/MariaDBAdapter.php';

class SupabaseToMariaDBTransfer {
    private $supabaseAdapter;
    private $mariadbAdapter;
    private $logFile;
    private $startTime;
    
    // Tespit edilen tablolar - Foreign key bağımlılıklarına göre sıralanmış
    private $tables = [
        // 1. BAĞIMSIZ TABLOLAR (Foreign key yok)
        'about_content_blocks',
        'about_settings',
        'admins',
        'blogs',
        'contact_info',
        'contact_messages',
        'seasonal_collections',
        'seo_settings',
        'site_settings',
        'slider_items',
        'social_media_links',
        
        // 2. ANA TABLOLAR (Diğer tablolar bunlara bağlı)
        'users',                // user_addresses buna bağlı
        'categories',           // product_categories buna bağlı
        'product_models',       // product_categories, product_genders, product_images, product_variants buna bağlı
        'colors',               // product_images, product_variants buna bağlı
        'sizes',                // product_variants buna bağlı
        'genders',              // product_genders buna bağlı
        
        // 3. BAĞIMLI TABLOLAR (Diğer tablolara foreign key ile bağlı)
        'product_categories',   // product_models + categories'e bağlı
        'product_genders',      // product_models + genders'e bağlı
        'product_images',       // product_models + colors'a bağlı
        'product_variants',     // product_models + colors + sizes'e bağlı
        'user_addresses'        // users'a bağlı
    ];
    
    // Materialized viewler
    private $materializedViews = [
        'category_product_counts',
        'product_details_view',
        'gender_product_counts',
        'product_api_summary'
    ];
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->logFile = __DIR__ . '/transfer_log_' . date('Y-m-d_H-i-s') . '.txt';
        
        $this->log("=== SUPABASE TO MARIADB TRANSFER BAŞLATILIYOR ===");
        $this->log("Başlangıç zamanı: " . date('Y-m-d H:i:s'));
        
        // Supabase adapter'ı oluştur
        $this->initializeSupabaseAdapter();
        
        // MariaDB adapter'ı oluştur
        $this->initializeMariaDBAdapter();
    }
    
    private function initializeSupabaseAdapter() {
        try {
            // Supabase bilgilerini sabitlerden al
            $supabaseUrl = SUPABASE_URL;
            $supabaseKey = SUPABASE_KEY;
            
            if (!$supabaseUrl || !$supabaseKey) {
                throw new Exception("Supabase URL ve KEY bilgileri bulunamadı");
            }
            
            $this->log("Supabase URL: " . $supabaseUrl);
            $this->log("Supabase KEY: " . substr($supabaseKey, 0, 10) . "...");
            
            $supabaseClient = new SupabaseClient($supabaseUrl, $supabaseKey);
            $this->supabaseAdapter = new SupabaseAdapter($supabaseClient);
            
            $this->log("Supabase bağlantısı başarıyla kuruldu");
        } catch (Exception $e) {
            $this->log("HATA: Supabase bağlantısı kurulamadı - " . $e->getMessage());
            throw $e;
        }
    }
    
    private function initializeMariaDBAdapter() {
        try {
            // MariaDB client'ı doğrudan oluştur
            require_once __DIR__ . '/lib/clients/MariaDBClient.php';
            
            $mariadbClient = new MariaDBClient([
                'host' => DB_HOST,
                'port' => DB_PORT,
                'database' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASS,
                'charset' => DB_CHARSET,
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            ]);
            
            $this->mariadbAdapter = new MariaDBAdapter($mariadbClient);
            
            $this->log("MariaDB bağlantısı başarıyla kuruldu");
        } catch (Exception $e) {
            $this->log("HATA: MariaDB bağlantısı kurulamadı - " . $e->getMessage());
            throw $e;
        }
    }
    
    public function transfer() {
        try {
            $this->log("\n=== TRANSFER AŞAMALARI ===");
            
            // 1. Tabloları oluştur
            $this->createTables();
            
            // 2. Verileri transfer et
            $this->transferData();
            
            // 3. Materialized viewleri oluştur
            $this->createMaterializedViews();
            
            // 4. Transfer istatistikleri
            $this->showTransferStats();
            
            $this->log("\n=== TRANSFER BAŞARIYLA TAMAMLANDI ===");
            
        } catch (Exception $e) {
            $this->log("HATA: Transfer sırasında hata oluştu - " . $e->getMessage());
            throw $e;
        }
    }
    
    private function createTables() {
        $this->log("\n--- TABLO OLUŞTURMA AŞAMASI ---");
        
        foreach ($this->tables as $tableName) {
            try {
                $this->log("Tablo oluşturuluyor: $tableName");
                
                // Supabase'den tablo yapısını al
                $createSQL = $this->generateCreateTableSQL($tableName);
                
                // MariaDB'de tabloyu oluştur
                $this->mariadbAdapter->executeRawSql($createSQL);
                
                $this->log("✓ Tablo başarıyla oluşturuldu: $tableName");
                
            } catch (Exception $e) {
                $this->log("✗ Tablo oluşturma hatası ($tableName): " . $e->getMessage());
                // Devam et, önemli değil
            }
        }
    }
    
    private function generateCreateTableSQL($tableName) {
        // Basit tablo oluşturma SQL'i üret
        // Bu örnekte genel yapılar kullanıyoruz
        $createSQL = "CREATE TABLE IF NOT EXISTS `$tableName` (";
        
        switch ($tableName) {
            case 'about_content_blocks':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `section` TEXT NOT NULL,
                    `title` TEXT,
                    `subtitle` TEXT,
                    `content` TEXT,
                    `image_url` TEXT,
                    `icon` TEXT,
                    `sort_order` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ";
                break;
                
            case 'about_settings':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `meta_key` TEXT NOT NULL,
                    `meta_value` TEXT,
                    `section` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `meta_key` (`meta_key`(255))
                ";
                break;
                
            case 'admins':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `username` VARCHAR(255) NOT NULL UNIQUE,
                    `password_hash` VARCHAR(255) NOT NULL,
                    `full_name` VARCHAR(255),
                    `email` VARCHAR(255),
                    `is_active` BOOLEAN DEFAULT TRUE,
                    `last_login_at` TIMESTAMP NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ";
                break;
                
            case 'blogs':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `title` TEXT NOT NULL,
                    `excerpt` TEXT,
                    `content` TEXT,
                    `image_url` TEXT,
                    `category` TEXT,
                    `tags` JSON,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ";
                break;
                
            case 'categories':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL UNIQUE,
                    `slug` VARCHAR(255) NOT NULL UNIQUE,
                    `description` TEXT,
                    `category_type` VARCHAR(50) DEFAULT 'product_type',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ";
                break;
                
            case 'product_categories':
                $createSQL .= "
                    `product_id` INT NOT NULL,
                    `category_id` INT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`product_id`, `category_id`),
                    FOREIGN KEY (`product_id`) REFERENCES `product_models`(`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
                ";
                break;
                
            case 'product_models':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `description` TEXT,
                    `base_price` DECIMAL(10,2) NOT NULL,
                    `features` TEXT,
                    `is_featured` BOOLEAN DEFAULT FALSE,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ";
                break;
                
            case 'colors':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL UNIQUE,
                    `hex_code` VARCHAR(7),
                    `display_order` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ";
                break;
                
            case 'contact_info':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `section` VARCHAR(255) NOT NULL,
                    `field` VARCHAR(255) NOT NULL,
                    `value` TEXT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ";
                break;
                
            case 'contact_messages':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `email` VARCHAR(255) NOT NULL,
                    `subject` VARCHAR(255) NOT NULL,
                    `message` TEXT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ";
                break;
                
            case 'genders':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `slug` VARCHAR(255) NOT NULL UNIQUE,
                    `description` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ";
                break;
                
            case 'product_genders':
                $createSQL .= "
                    `product_id` INT NOT NULL,
                    `gender_id` INT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`product_id`, `gender_id`),
                    FOREIGN KEY (`product_id`) REFERENCES `product_models`(`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`gender_id`) REFERENCES `genders`(`id`) ON DELETE CASCADE
                ";
                break;
                
            case 'product_images':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `model_id` INT,
                    `color_id` INT,
                    `image_url` TEXT NOT NULL,
                    `alt_text` VARCHAR(255),
                    `is_primary` BOOLEAN DEFAULT FALSE,
                    `sort_order` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`model_id`) REFERENCES `product_models`(`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`color_id`) REFERENCES `colors`(`id`) ON DELETE SET NULL
                ";
                break;
                
            case 'product_variants':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `model_id` INT,
                    `color_id` INT,
                    `size_id` INT,
                    `sku` VARCHAR(255) NOT NULL UNIQUE,
                    `price` DECIMAL(10,2) NOT NULL,
                    `original_price` DECIMAL(10,2),
                    `stock_quantity` INT DEFAULT 0,
                    `is_active` BOOLEAN DEFAULT TRUE,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`model_id`) REFERENCES `product_models`(`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`color_id`) REFERENCES `colors`(`id`) ON DELETE SET NULL,
                    FOREIGN KEY (`size_id`) REFERENCES `sizes`(`id`) ON DELETE SET NULL
                ";
                break;
                
            case 'seasonal_collections':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `title` TEXT NOT NULL,
                    `description` TEXT,
                    `image_url` TEXT,
                    `button_url` TEXT,
                    `sort_order` INT DEFAULT 0,
                    `layout_type` VARCHAR(10) DEFAULT 'left',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ";
                break;
                
            case 'seo_settings':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `setting_key` TEXT NOT NULL,
                    `setting_value` TEXT,
                    `setting_type` TEXT DEFAULT 'meta',
                    `is_active` BOOLEAN DEFAULT TRUE,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `setting_key` (`setting_key`(255))
                ";
                break;
                
            case 'site_settings':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `setting_key` TEXT NOT NULL,
                    `setting_value` TEXT,
                    `setting_group` TEXT DEFAULT 'general',
                    `description` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `setting_key` (`setting_key`(255))
                ";
                break;
                
            case 'sizes':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `size_value` VARCHAR(255) NOT NULL,
                    `size_type` VARCHAR(10) DEFAULT 'EU',
                    `display_order` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ";
                break;
                
            case 'slider_items':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `title` TEXT NOT NULL,
                    `description` TEXT,
                    `image_url` TEXT,
                    `bg_color` VARCHAR(7) DEFAULT '#f0f0f0',
                    `button_text` VARCHAR(255) NOT NULL,
                    `button_url` VARCHAR(255) NOT NULL,
                    `is_active` BOOLEAN DEFAULT TRUE,
                    `sort_order` INT DEFAULT 0,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ";
                break;
                
            case 'social_media_links':
                $createSQL .= "
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `platform` VARCHAR(255) NOT NULL,
                    `url` TEXT NOT NULL,
                    `icon_class` VARCHAR(255),
                    `order_index` INT DEFAULT 0,
                    `is_active` BOOLEAN DEFAULT TRUE,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ";
                break;
                
            case 'user_addresses':
                $createSQL .= "
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` VARCHAR(36) NOT NULL,
                    `address_title` TEXT NOT NULL,
                    `full_address` TEXT NOT NULL,
                    `city` TEXT NOT NULL,
                    `province` TEXT,
                    `postal_code` TEXT,
                    `country` TEXT NOT NULL,
                    `is_default_shipping` BOOLEAN DEFAULT FALSE,
                    `is_default_billing` BOOLEAN DEFAULT FALSE,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                ";
                break;
                
            case 'users':
                $createSQL .= "
                    `id` VARCHAR(36) PRIMARY KEY,
                    `email` TEXT UNIQUE,
                    `first_name` TEXT,
                    `last_name` TEXT,
                    `phone_number` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ";
                break;
                
            default:
                throw new Exception("Bilinmeyen tablo: $tableName");
        }
        
        $createSQL .= ")";
        
        return $createSQL;
    }
    
    private function transferData() {
        $this->log("\n--- VERİ TRANSFER AŞAMASI ---");
        
        foreach ($this->tables as $tableName) {
            try {
                $this->log("Veri transfer ediliyor: $tableName");
                
                // Supabase'den verileri al
                $data = $this->supabaseAdapter->select($tableName);
                
                if (empty($data)) {
                    $this->log("→ Tablo boş: $tableName");
                    continue;
                }
                
                $count = count($data);
                $this->log("→ $count kayıt bulundu");
                
                // Verileri MariaDB'ye aktar (REPLACE INTO kullanarak duplicate hataları engelle)
                $successCount = 0;
                $errorCount = 0;
                
                foreach ($data as $row) {
                    try {
                        // Veri tiplerini düzenle
                        $row = $this->prepareDataForMariaDB($row, $tableName);
                        
                        // REPLACE INTO kullanarak duplicate hatalarını engelle
                        $result = $this->mariadbAdapter->insertOrReplace($tableName, $row);
                        
                        if (!empty($result['affected_rows'])) {
                            $successCount++;
                        }
                        
                    } catch (Exception $e) {
                        $errorCount++;
                        $this->log("✗ Kayıt işlenirken hata ($tableName): " . $e->getMessage());
                    }
                }
                
                if ($errorCount > 0) {
                    $this->log("✓ $successCount / $count kayıt transfer edildi, $errorCount hata: $tableName");
                } else {
                    $this->log("✓ $successCount / $count kayıt başarıyla transfer edildi: $tableName");
                }
                
            } catch (Exception $e) {
                $this->log("✗ Veri transfer hatası ($tableName): " . $e->getMessage());
            }
        }
    }
    
    private function prepareDataForMariaDB($row, $tableName) {
        // Veri tiplerini MariaDB'ye uygun hale getir
        foreach ($row as $key => $value) {
            // JSON dizilerini string'e çevir
            if (is_array($value)) {
                $row[$key] = json_encode($value);
            }
            
            // Boolean değerleri düzenle
            if (is_bool($value)) {
                $row[$key] = $value ? 1 : 0;
            }
            
            // Tarih değerlerini düzenle
            if (in_array($key, ['created_at', 'updated_at', 'last_login_at']) && $value) {
                $row[$key] = date('Y-m-d H:i:s', strtotime($value));
            }
        }
        
        return $row;
    }
    
    private function createMaterializedViews() {
        $this->log("\n--- MATERIALIZED VIEW OLUŞTURMA AŞAMASI ---");
        
        // MariaDB'de materialized view yoktur, normal view olarak oluşturacağız
        $viewDefinitions = [
            'category_product_counts' => "
                SELECT
                    c.id,
                    c.name,
                    c.slug,
                    c.category_type,
                    COUNT(pc.product_id) AS product_count
                FROM categories c
                LEFT JOIN product_categories pc ON c.id = pc.category_id
                LEFT JOIN product_models pm ON pc.product_id = pm.id
                GROUP BY c.id, c.name, c.slug, c.category_type
                ORDER BY c.name
            ",
            
            'gender_product_counts' => "
                SELECT
                    g.id,
                    g.name,
                    g.slug,
                    COUNT(pg.product_id) AS product_count
                FROM genders g
                LEFT JOIN product_genders pg ON g.id = pg.gender_id
                GROUP BY g.id, g.name, g.slug
                ORDER BY g.name
            ",
            
            'product_api_summary' => "
                SELECT
                    pm.id,
                    pm.name,
                    pm.description,
                    pm.base_price,
                    pm.is_featured,
                    pm.created_at,
                    (SELECT pi.image_url FROM product_images pi
                     WHERE pi.model_id = pm.id AND pi.is_primary = 1
                     ORDER BY pi.sort_order LIMIT 1) AS primary_image
                FROM product_models pm
            ",
            
            'product_details_view' => "
                SELECT
                    pm.id,
                    pm.name,
                    pm.description,
                    pm.base_price,
                    pm.features,
                    pm.is_featured,
                    pm.created_at,
                    (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', c.id, 'name', c.name, 'slug', c.slug))
                     FROM product_categories pc
                     JOIN categories c ON pc.category_id = c.id
                     WHERE pc.product_id = pm.id) AS categories,
                    (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', g.id, 'name', g.name, 'slug', g.slug))
                     FROM product_genders pg
                     JOIN genders g ON pg.gender_id = g.id
                     WHERE pg.product_id = pm.id) AS genders,
                    (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', pv.id, 'color_id', pv.color_id, 'size_id', pv.size_id, 'sku', pv.sku, 'price', pv.price, 'stock_quantity', pv.stock_quantity, 'is_active', pv.is_active))
                     FROM product_variants pv
                     WHERE pv.model_id = pm.id AND pv.is_active = 1) AS variants,
                    (SELECT JSON_ARRAYAGG(JSON_OBJECT('id', pi.id, 'image_url', pi.image_url, 'alt_text', pi.alt_text, 'is_primary', pi.is_primary, 'color_id', pi.color_id, 'sort_order', pi.sort_order))
                     FROM product_images pi
                     WHERE pi.model_id = pm.id
                     ORDER BY pi.sort_order) AS images
                FROM product_models pm
            "
        ];
        
        foreach ($viewDefinitions as $viewName => $definition) {
            try {
                $this->log("View oluşturuluyor: $viewName");
                
                // Önce view'i sil
                $this->mariadbAdapter->executeRawSql("DROP VIEW IF EXISTS `$viewName`");
                
                // Yeni view oluştur
                $createViewSQL = "CREATE VIEW `$viewName` AS $definition";
                $this->mariadbAdapter->executeRawSql($createViewSQL);
                
                $this->log("✓ View başarıyla oluşturuldu: $viewName");
                
            } catch (Exception $e) {
                $this->log("✗ View oluşturma hatası ($viewName): " . $e->getMessage());
            }
        }
    }
    
    private function showTransferStats() {
        $this->log("\n--- TRANSFER İSTATİSTİKLERİ ---");
        
        foreach ($this->tables as $tableName) {
            try {
                $count = $this->mariadbAdapter->count($tableName);
                $this->log("$tableName: $count kayıt");
            } catch (Exception $e) {
                $this->log("$tableName: Sayı alınamadı");
            }
        }
        
        $endTime = microtime(true);
        $duration = round($endTime - $this->startTime, 2);
        $this->log("\nToplam süre: $duration saniye");
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        // Konsola yazdır
        echo $logMessage;
        
        // Dosyaya kaydet
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}

// Script çalıştırma
if (php_sapi_name() === 'cli') {
    try {
        $transfer = new SupabaseToMariaDBTransfer();
        $transfer->transfer();
        
        echo "\nTransfer başarıyla tamamlandı!\n";
        
    } catch (Exception $e) {
        echo "Transfer hatası: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "Bu script sadece komut satırından çalıştırılabilir.\n";
    exit(1);
}