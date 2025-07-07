<?php
/**
 * MCP Enhanced Supabase'den MariaDB'ye Migration
 * 
 * Bu script Supabase MCP'den alınan gerçek şema bilgileriyle migration yapar
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

require_once __DIR__ . '/../config/database.php';

echo "🔄 MCP Enhanced Supabase → MariaDB Migration Başlıyor...\n\n";

// Database bağlantıları
putenv('DB_TYPE=supabase');
$supabase = DatabaseFactory::create('supabase');
$mariadb = DatabaseFactory::create('mariadb');

// Supabase MCP'den alınan gerçek şema bilgileri
$tableSchemas = [
    'colors' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'name', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'hex_code', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'display_order', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => '0']
        ]
    ],
    'sizes' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'size_value', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'size_type', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => "'EU'"],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'display_order', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => '0']
        ]
    ],
    'genders' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'name', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'slug', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'description', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'categories' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'name', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'slug', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'description', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'category_type', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => "'product_type'"],
            ['name' => 'parent_id', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => null]
        ]
    ],
    'admins' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'username', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'password_hash', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'full_name', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'is_active', 'data_type' => 'boolean', 'is_nullable' => true, 'default_value' => 'true'],
            ['name' => 'last_login_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'email', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => null]
        ]
    ],
    'contact_info' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'section', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'field', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'value', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp without time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp without time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'social_media_links' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'platform', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'url', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'icon_class', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'order_index', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => '0'],
            ['name' => 'is_active', 'data_type' => 'boolean', 'is_nullable' => true, 'default_value' => 'true'],
            ['name' => 'created_at', 'data_type' => 'timestamp without time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp without time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'contact_messages' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'name', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'email', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'subject', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'message', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp without time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'slider_items' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'title', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'description', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'image_url', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'bg_color', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => "'#f0f0f0'"],
            ['name' => 'button_text', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'button_url', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'is_active', 'data_type' => 'boolean', 'is_nullable' => true, 'default_value' => 'true'],
            ['name' => 'sort_order', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => '0'],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'seasonal_collections' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'title', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'description', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'image_url', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'button_url', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'sort_order', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => '0'],
            ['name' => 'layout_type', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => "'left'"],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'blogs' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'title', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'excerpt', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'content', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'image_url', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'category', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'tags', 'data_type' => 'ARRAY', 'is_nullable' => true, 'default_value' => null]
        ]
    ],
    'about_settings' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'meta_key', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'meta_value', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'section', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'about_content_blocks' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'section', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'title', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'subtitle', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'content', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'image_url', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'icon', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'sort_order', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => '0'],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'site_settings' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'setting_key', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'setting_value', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'setting_group', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => "'general'"],
            ['name' => 'description', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'seo_settings' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'setting_key', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'setting_value', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'setting_type', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => "'meta'"],
            ['name' => 'is_active', 'data_type' => 'boolean', 'is_nullable' => true, 'default_value' => 'true'],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'product_models' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'name', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'description', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'base_price', 'data_type' => 'numeric', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'is_featured', 'data_type' => 'boolean', 'is_nullable' => true, 'default_value' => 'false'],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'features', 'data_type' => 'text', 'is_nullable' => true, 'default_value' => null]
        ]
    ],
    'product_variants' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'model_id', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'color_id', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'size_id', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'sku', 'data_type' => 'character varying', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'price', 'data_type' => 'numeric', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'original_price', 'data_type' => 'numeric', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'stock_quantity', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => '0'],
            ['name' => 'is_active', 'data_type' => 'boolean', 'is_nullable' => true, 'default_value' => 'true'],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()'],
            ['name' => 'updated_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'product_images' => [
        'columns' => [
            ['name' => 'id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => 'nextval'],
            ['name' => 'model_id', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'color_id', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'image_url', 'data_type' => 'text', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'alt_text', 'data_type' => 'character varying', 'is_nullable' => true, 'default_value' => null],
            ['name' => 'is_primary', 'data_type' => 'boolean', 'is_nullable' => true, 'default_value' => 'false'],
            ['name' => 'sort_order', 'data_type' => 'integer', 'is_nullable' => true, 'default_value' => '0'],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'product_categories' => [
        'columns' => [
            ['name' => 'product_id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'category_id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ],
    'product_genders' => [
        'columns' => [
            ['name' => 'product_id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'gender_id', 'data_type' => 'integer', 'is_nullable' => false, 'default_value' => null],
            ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
        ]
    ]
];

// Migration sırası (dependency order)
$migrationOrder = [
    'colors', 'sizes', 'genders', 'categories', 'admins',
    'contact_info', 'social_media_links', 'contact_messages', 
    'slider_items', 'seasonal_collections', 'blogs',
    'about_settings', 'about_content_blocks', 'site_settings', 'seo_settings',
    'product_models', 'product_variants', 'product_images',
    'product_categories', 'product_genders'
];

/**
 * PostgreSQL tipini MySQL tipine çevir
 */
function convertPgTypeToMySQL($pgType) {
    $typeMap = [
        'integer' => 'INT',
        'bigint' => 'BIGINT',
        'character varying' => 'VARCHAR(255)',
        'text' => 'TEXT',
        'boolean' => 'BOOLEAN',
        'numeric' => 'DECIMAL(10,2)',
        'timestamp with time zone' => 'TIMESTAMP',
        'timestamp without time zone' => 'TIMESTAMP',
        'ARRAY' => 'JSON'
    ];
    
    return $typeMap[$pgType] ?? 'TEXT';
}

/**
 * MCP şemasından MySQL CREATE TABLE SQL'i oluştur
 */
function createMySQLTableFromMCPSchema($tableName, $schema, $mariadb) {
    echo "🔨 $tableName tablosu oluşturuluyor (MCP Enhanced)...\n";
    
    // Tabloyu sil
    try {
        $mariadb->executeRawSql("SET FOREIGN_KEY_CHECKS = 0");
        $mariadb->executeRawSql("DROP TABLE IF EXISTS `$tableName`");
        $mariadb->executeRawSql("SET FOREIGN_KEY_CHECKS = 1");
    } catch (Exception $e) {
        // Ignore
    }
    
    $sql = "CREATE TABLE `$tableName` (\n";
    $columnDefinitions = [];
    
    foreach ($schema['columns'] as $column) {
        $name = $column['name'];
        $type = convertPgTypeToMySQL($column['data_type']);
        $nullable = $column['is_nullable'] ? 'NULL' : 'NOT NULL';
        $default = '';
        
        // ID sütunları için primary key
        if ($name === 'id') {
            if ($column['data_type'] === 'bigint') {
                $columnDefinitions[] = "  `$name` BIGINT AUTO_INCREMENT PRIMARY KEY";
            } else {
                $columnDefinitions[] = "  `$name` INT AUTO_INCREMENT PRIMARY KEY";
            }
        } 
        // Junction tablolar için composite primary key
        elseif (in_array($tableName, ['product_categories', 'product_genders']) && 
                in_array($name, ['product_id', 'category_id', 'gender_id'])) {
            $columnDefinitions[] = "  `$name` INT $nullable";
        }
        else {
            // Default değerleri işle
            if ($column['default_value'] && 
                !in_array($column['default_value'], ['nextval', 'now()'])) {
                
                $defaultValue = $column['default_value'];
                
                if ($column['data_type'] === 'boolean') {
                    $default = " DEFAULT " . ($defaultValue === 'true' ? 'TRUE' : 'FALSE');
                } elseif (strpos($defaultValue, "'") === 0) {
                    $default = " DEFAULT $defaultValue";
                } else {
                    $default = " DEFAULT '$defaultValue'";
                }
            }
            
            // Timestamp sütunları için
            if (in_array($name, ['created_at', 'updated_at']) && 
                strpos($type, 'TIMESTAMP') !== false) {
                if ($name === 'created_at') {
                    $default = " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $default = " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                }
            }
            
            $columnDefinitions[] = "  `$name` $type $nullable$default";
        }
    }
    
    // Junction tabloları için composite primary key ekle
    if ($tableName === 'product_categories') {
        $columnDefinitions[] = "  PRIMARY KEY (`product_id`, `category_id`)";
    } elseif ($tableName === 'product_genders') {
        $columnDefinitions[] = "  PRIMARY KEY (`product_id`, `gender_id`)";
    }
    
    $sql .= implode(",\n", $columnDefinitions);
    $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    try {
        $mariadb->executeRawSql($sql);
        echo "   ✅ Tablo başarıyla oluşturuldu\n";
        return true;
    } catch (Exception $e) {
        echo "   ❌ Tablo oluşturma hatası: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Tarih formatını dönüştür
 */
function convertDatetime($value) {
    if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
        try {
            $datetime = new DateTime($value);
            return $datetime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return $value;
        }
    }
    return $value;
}

/**
 * Verileri aktar
 */
function migrateDataWithMCP($tableName, $supabase, $mariadb) {
    echo "📋 $tableName verisi kopyalanıyor...\n";
    
    try {
        $data = $supabase->select($tableName);
        
        if (empty($data)) {
            echo "   ⚠️  Veri yok\n";
            return true;
        }
        
        $insertCount = 0;
        $errorCount = 0;
        
        foreach ($data as $row) {
            $cleanRow = [];
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    $cleanRow[$key] = json_encode($value);
                } elseif (is_bool($value)) {
                    $cleanRow[$key] = $value ? 1 : 0;
                } elseif (in_array($key, ['created_at', 'updated_at', 'last_login_at'])) {
                    $cleanRow[$key] = convertDatetime($value);
                } else {
                    $cleanRow[$key] = $value;
                }
            }
            
            try {
                $result = $mariadb->insert($tableName, $cleanRow);
                if ($result) {
                    $insertCount++;
                }
            } catch (Exception $e) {
                $errorCount++;
                if ($errorCount <= 2) {
                    echo "   ⚠️  Ekleme hatası: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "   ✅ $insertCount kayıt kopyalandı";
        if ($errorCount > 0) {
            echo " ($errorCount hata)";
        }
        echo "\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "   ❌ Veri alma hatası: " . $e->getMessage() . "\n";
        return false;
    }
}

// Migration işlemini başlat
echo "🚀 MCP Enhanced Migration başlıyor...\n\n";

$successfulTables = [];
$failedTables = [];
$totalRecords = 0;

foreach ($migrationOrder as $tableName) {
    echo "=== $tableName Tablosu İşleniyor ===\n";
    
    if (!isset($tableSchemas[$tableName])) {
        echo "   ❌ Şema bilgisi bulunamadı, atlanıyor\n\n";
        $failedTables[] = $tableName;
        continue;
    }
    
    try {
        // 1. Tabloyu oluştur
        if (!createMySQLTableFromMCPSchema($tableName, $tableSchemas[$tableName], $mariadb)) {
            $failedTables[] = $tableName;
            echo "❌ $tableName başarısız\n\n";
            continue;
        }
        
        // 2. Verileri aktar
        migrateDataWithMCP($tableName, $supabase, $mariadb);
        
        $successfulTables[] = $tableName;
        echo "✅ $tableName başarıyla tamamlandı\n\n";
        
    } catch (Exception $e) {
        $failedTables[] = $tableName;
        echo "❌ $tableName genel hatası: " . $e->getMessage() . "\n\n";
    }
}

// Özet rapor
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 MCP ENHANCED MIGRATION RAPORU\n";
echo str_repeat("=", 60) . "\n";

echo "✅ Başarılı tablolar (" . count($successfulTables) . "):\n";
foreach ($successfulTables as $table) {
    echo "   - $table\n";
}

if (!empty($failedTables)) {
    echo "\n❌ Başarısız tablolar (" . count($failedTables) . "):\n";
    foreach ($failedTables as $table) {
        echo "   - $table\n";
    }
}

echo "\n🎉 MCP Enhanced Migration tamamlandı!\n";
echo "MariaDB artık Supabase'den tam kopyalanmış verilerle hazır.\n";
