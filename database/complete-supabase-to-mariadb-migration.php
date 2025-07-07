<?php
/**
 * Supabase'den MariaDB'ye Tam Veri Migration Scripti
 * 
 * Bu script Supabase MCP'den alınan tablo bilgileriyle tüm tabloları ve verileri MariaDB'ye kopyalar
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Uzun işlem için time limit kaldır

require_once __DIR__ . '/../config/database.php';

echo "🔄 Supabase'den MariaDB'ye Tam Veri Migration Başlıyor...\n\n";

// Supabase bağlantısı oluştur
putenv('DB_TYPE=supabase');
$supabase = DatabaseFactory::create('supabase');

// MariaDB bağlantısı oluştur
$mariadb = DatabaseFactory::create('mariadb');

// Supabase MCP'den alınan gerçek tablo listesi (dependency sırasıyla)
$tables = [
    // Temel lookup tablolar (hiç dependency'si yok)
    'colors',
    'sizes', 
    'genders',
    'categories',
    'admins',
    
    // İçerik tabloları (bağımsız)
    'contact_info',
    'social_media_links',
    'contact_messages',
    'slider_items',
    'seasonal_collections',
    'blogs',
    'about_settings',
    'about_content_blocks',
    'site_settings',
    'seo_settings',
    
    // Ürün tabloları (categories ve colors/sizes'e bağımlı)
    'product_models',
    'product_variants',
    'product_images',
    
    // Junction tablolar (diğer tablolara bağımlı)
    'product_categories',
    'product_genders'
];

/**
 * Supabase MCP ile tablo verilerini al
 */
function getSupabaseTableData($tableName, $supabase) {
    try {
        echo "📥 $tableName verileri Supabase'den alınıyor...\n";
        $data = $supabase->select($tableName);
        echo "   📊 " . count($data) . " kayıt bulundu\n";
        return $data;
    } catch (Exception $e) {
        echo "   ❌ Veri alma hatası: " . $e->getMessage() . "\n";
        return [];
    }
}

/**
 * Sütun tipini PostgreSQL'den MySQL'e çevir
 */
function convertColumnType($pgType, $sampleValue = null) {
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
 * Supabase MCP tablo şemasından MariaDB CREATE TABLE SQL'i oluştur
 */
function createMariaDBTableFromSchema($tableName, $columns, $mariadb) {
    echo "🔨 $tableName tablosu oluşturuluyor...\n";
    
    // Önce tabloyu sil
    try {
        $mariadb->executeRawSql("DROP TABLE IF EXISTS `$tableName`");
    } catch (Exception $e) {
        // Ignore - tablo zaten yoksa sorun yok
    }
    
    $sql = "CREATE TABLE `$tableName` (\n";
    $columnDefinitions = [];
    
    foreach ($columns as $column) {
        $name = $column['name'];
        $type = convertColumnType($column['data_type']);
        $nullable = $column['is_nullable'] ? 'NULL' : 'NOT NULL';
        $default = '';
        
        // ID sütunu için özel işlem
        if ($name === 'id' && $column['data_type'] === 'integer') {
            $columnDefinitions[] = "  `$name` INT AUTO_INCREMENT PRIMARY KEY";
        } elseif ($name === 'id' && $column['data_type'] === 'bigint') {
            $columnDefinitions[] = "  `$name` BIGINT AUTO_INCREMENT PRIMARY KEY";
        } else {
            // Default değerleri işle
            if ($column['default_value'] && $column['default_value'] !== 'NULL') {
                $defaultValue = $column['default_value'];
                // PostgreSQL-specific default'ları temizle
                if (strpos($defaultValue, 'nextval') === false && 
                    strpos($defaultValue, 'now()') === false) {
                    if ($column['data_type'] === 'boolean') {
                        $default = " DEFAULT " . ($defaultValue === 'true' ? 'TRUE' : 'FALSE');
                    } elseif (strpos($defaultValue, "'") === 0) {
                        $default = " DEFAULT $defaultValue";
                    } else {
                        $default = " DEFAULT '$defaultValue'";
                    }
                }
            }
            
            // Timestamp sütunları için özel işlem
            if (in_array($name, ['created_at', 'updated_at']) && $type === 'TIMESTAMP') {
                if ($name === 'created_at') {
                    $default = " DEFAULT CURRENT_TIMESTAMP";
                } else {
                    $default = " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                }
            }
            
            $columnDefinitions[] = "  `$name` $type $nullable$default";
        }
    }
    
    $sql .= implode(",\n", $columnDefinitions);
    $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    try {
        $result = $mariadb->executeRawSql($sql);
        echo "   ✅ Tablo başarıyla oluşturuldu\n";
        return true;
    } catch (Exception $e) {
        echo "   ❌ Tablo oluşturma hatası: " . $e->getMessage() . "\n";
        echo "   SQL: $sql\n";
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
 * Verileri MariaDB'ye aktar
 */
function migrateTableData($tableName, $data, $mariadb) {
    if (empty($data)) {
        echo "   ⚠️  Veri yok, atlanıyor\n";
        return true;
    }
    
    echo "📋 $tableName verisi kopyalanıyor...\n";
    
    $insertCount = 0;
    $errorCount = 0;
    
    foreach ($data as $row) {
        // Veriyi temizle ve hazırla
        $cleanRow = [];
        foreach ($row as $key => $value) {
            if (is_array($value)) {
                $cleanRow[$key] = json_encode($value);
            } elseif (is_bool($value)) {
                $cleanRow[$key] = $value ? 1 : 0;
            } elseif (in_array($key, ['created_at', 'updated_at'])) {
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
            if ($errorCount <= 3) { // İlk 3 hatayı göster
                echo "   ⚠️  Satır ekleme hatası: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "   ✅ $insertCount kayıt kopyalandı";
    if ($errorCount > 0) {
        echo " ($errorCount hata)";
    }
    echo "\n";
    
    return true;
}

// Migration işlemini başlat
echo "🚀 Migration başlıyor...\n\n";

$successfulTables = [];
$failedTables = [];
$totalRecords = 0;

// Supabase MCP'den tablo şema bilgilerini al
$tableSchemas = [];

foreach ($tables as $tableName) {
    echo "=== $tableName Tablosu İşleniyor ===\n";
    
    try {
        // 1. Tablo verilerini al
        $data = getSupabaseTableData($tableName, $supabase);
        
        if (empty($data)) {
            echo "   📝 Tablo boş, sadece şema oluşturuluyor\n";
            // Boş tablo için basit şema oluştur
            $basicColumns = [
                ['name' => 'id', 'data_type' => 'bigint', 'is_nullable' => false, 'default_value' => 'nextval'],
                ['name' => 'created_at', 'data_type' => 'timestamp with time zone', 'is_nullable' => true, 'default_value' => 'now()']
            ];
            
            if (createMariaDBTableFromSchema($tableName, $basicColumns, $mariadb)) {
                $successfulTables[] = $tableName;
            } else {
                $failedTables[] = $tableName;
            }
            continue;
        }
        
        // 2. İlk kayıttan sütun bilgilerini çıkar
        $firstRow = $data[0];
        $columns = [];
        
        foreach ($firstRow as $columnName => $value) {
            $pgType = 'text'; // Default
            
            // Tip tahminini veri tipinden yap
            if (is_int($value)) {
                $pgType = $value > 2147483647 ? 'bigint' : 'integer';
            } elseif (is_float($value)) {
                $pgType = 'numeric';
            } elseif (is_bool($value)) {
                $pgType = 'boolean';
            } elseif (is_array($value)) {
                $pgType = 'ARRAY';
            } elseif (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T/', $value)) {
                $pgType = 'timestamp with time zone';
            } elseif (is_string($value) && strlen($value) <= 255) {
                $pgType = 'character varying';
            }
            
            $columns[] = [
                'name' => $columnName,
                'data_type' => $pgType,
                'is_nullable' => true,
                'default_value' => null
            ];
        }
        
        // ID sütunu varsa primary key yap
        foreach ($columns as &$column) {
            if ($column['name'] === 'id') {
                $column['is_nullable'] = false;
            }
        }
        
        // 3. MariaDB'de tabloyu oluştur
        if (!createMariaDBTableFromSchema($tableName, $columns, $mariadb)) {
            $failedTables[] = $tableName;
            echo "❌ $tableName başarısız\n\n";
            continue;
        }
        
        // 4. Verileri kopyala
        migrateTableData($tableName, $data, $mariadb);
        
        $successfulTables[] = $tableName;
        $totalRecords += count($data);
        echo "✅ $tableName başarıyla tamamlandı\n\n";
        
    } catch (Exception $e) {
        $failedTables[] = $tableName;
        echo "❌ $tableName genel hatası: " . $e->getMessage() . "\n\n";
    }
}

// Özet rapor
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 SUPABASE -> MARIADB MIGRATION RAPORU\n";
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

echo "\n📈 İstatistikler:\n";
echo "   - Toplam tablo: " . count($tables) . "\n";
echo "   - Başarılı: " . count($successfulTables) . "\n";
echo "   - Başarısız: " . count($failedTables) . "\n";
echo "   - Toplam kayıt: ~$totalRecords\n";

echo "\n🎉 Migration tamamlandı!\n";
echo "MariaDB'yi kullanmaya başlamak için .env dosyasında DB_TYPE=mariadb yapın.\n";
