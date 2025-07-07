<?php
/**
 * Supabase'den MariaDB'ye Veri Migration Scripti
 * 
 * Bu script Supabase'deki tüm tabloları ve verileri MariaDB'ye kopyalar
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

echo "🔄 Supabase'den MariaDB'ye Veri Migration Başlıyor...\n\n";

// Supabase bağlantısı oluştur
putenv('DB_TYPE=supabase');
$supabase = DatabaseFactory::create('supabase');

// MariaDB bağlantısı oluştur
$mariadb = DatabaseFactory::create('mariadb');

// Migration edilecek tablolar ve sıraları (foreign key dependencies için)
$tables = [
    // Temel tablolar (bağımlılığı olmayanlar)
    'colors',
    'sizes',
    'genders',
    'categories',
    'contact_info',
    'social_media_links',
    'sliders',
    'seasonal_collections',
    'blogs',
    'contact_messages',
    'about_content',
    'about_team',
    'about_values',
    'settings',
    
    // Ürün tabloları (kategorilere bağımlı)
    'product_models',
    'product_variants',
    'product_images',
    
    // Admin tabloları
    'admin_users'
];

/**
 * Supabase'deki tablo yapısını analiz et ve MariaDB için SQL oluştur
 */
function analyzeSupabaseTable($tableName, $supabase) {
    echo "📊 $tableName tablosu analiz ediliyor...\n";
    
    try {
        // Tablo verilerini al
        $data = $supabase->select($tableName, [], '*', ['limit' => 1]);
        
        if (empty($data)) {
            echo "⚠️  $tableName tablosu boş veya erişilemiyor\n";
            return null;
        }
        
        $sampleRow = $data[0];
        $columns = [];
        
        foreach ($sampleRow as $columnName => $value) {
            $type = getMariaDBType($value);
            $columns[$columnName] = $type;
        }
        
        return $columns;
        
    } catch (Exception $e) {
        echo "❌ $tableName tablo analizi hatası: " . $e->getMessage() . "\n";
        return null;
    }
}

/**
 * PHP değerinden MariaDB sütun tipini tahmin et
 */
function getMariaDBType($value) {
    if (is_null($value)) {
        return 'TEXT'; // Default olarak TEXT
    }
    
    if (is_int($value)) {
        return 'INT';
    }
    
    if (is_float($value)) {
        return 'DECIMAL(10,2)';
    }
    
    if (is_bool($value)) {
        return 'BOOLEAN';
    }
    
    if (is_string($value)) {
        $length = strlen($value);
        if ($length <= 255) {
            return 'VARCHAR(255)';
        } else {
            return 'TEXT';
        }
    }
    
    if (is_array($value)) {
        return 'JSON';
    }
    
    // Tarih kontrolü
    if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
        if (strpos($value, 'T') !== false || strpos($value, ' ') !== false) {
            return 'TIMESTAMP';
        } else {
            return 'DATE';
        }
    }
    
    return 'TEXT';
}

/**
 * MariaDB tablosu oluştur
 */
function createMariaDBTable($tableName, $columns, $mariadb) {
    echo "🔨 $tableName tablosu oluşturuluyor...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
    $columnDefinitions = [];
    
    // ID sütunu her tabloda olmalı
    if (!isset($columns['id'])) {
        $columnDefinitions[] = "  `id` INT AUTO_INCREMENT PRIMARY KEY";
    }
    
    foreach ($columns as $columnName => $type) {
        if ($columnName === 'id') {
            $columnDefinitions[] = "  `$columnName` INT AUTO_INCREMENT PRIMARY KEY";
        } elseif (in_array($columnName, ['created_at', 'updated_at'])) {
            $columnDefinitions[] = "  `$columnName` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        } else {
            $null = "NULL";
            $columnDefinitions[] = "  `$columnName` $type $null";
        }
    }
    
    // created_at ve updated_at sütunları yoksa ekle
    if (!isset($columns['created_at'])) {
        $columnDefinitions[] = "  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    }
    if (!isset($columns['updated_at'])) {
        $columnDefinitions[] = "  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    }
    
    $sql .= implode(",\n", $columnDefinitions);
    $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    try {
        $result = $mariadb->executeRawSql($sql);
        echo "✅ $tableName tablosu başarıyla oluşturuldu\n";
        return true;
    } catch (Exception $e) {
        echo "❌ $tableName tablo oluşturma hatası: " . $e->getMessage() . "\n";
        echo "SQL: $sql\n";
        return false;
    }
}

/**
 * Supabase'deki tüm sütunları analiz et
 */
function getAllColumnsFromSupabase($tableName, $supabase) {
    try {
        // Tüm verileri al ve tüm sütunları topla
        $allData = $supabase->select($tableName);
        
        if (empty($allData)) {
            return [];
        }
        
        $allColumns = [];
        foreach ($allData as $row) {
            foreach ($row as $columnName => $value) {
                if (!isset($allColumns[$columnName])) {
                    $allColumns[$columnName] = getMariaDBType($value);
                }
            }
        }
        
        return $allColumns;
        
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Tarih formatını MySQL/MariaDB için dönüştür
 */
function convertDatetime($value) {
    if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
        // ISO 8601 formatından MySQL datetime formatına çevir
        $datetime = new DateTime($value);
        return $datetime->format('Y-m-d H:i:s');
    }
    return $value;
}

/**
 * Verileri Supabase'den MariaDB'ye kopyala
 */
function migrateTableData($tableName, $supabase, $mariadb) {
    echo "📋 $tableName verisi kopyalanıyor...\n";
    
    try {
        // Önce tabloyu DROP ve yeniden oluştur (tüm sütunlarla)
        echo "🔄 $tableName tablosu yeniden oluşturuluyor...\n";
        $mariadb->executeRawSql("DROP TABLE IF EXISTS `$tableName`");
        
        // Tüm sütunları analiz et
        $allColumns = getAllColumnsFromSupabase($tableName, $supabase);
        if (empty($allColumns)) {
            echo "⚠️  $tableName için sütun bilgisi alınamadı\n";
            return false;
        }
        
        // Tabloyu yeniden oluştur
        if (!createMariaDBTable($tableName, $allColumns, $mariadb)) {
            return false;
        }
        
        // Tüm verileri Supabase'den al
        $allData = $supabase->select($tableName);
        
        if (empty($allData)) {
            echo "⚠️  $tableName tablosunda veri yok\n";
            return true;
        }
        
        $insertCount = 0;
        $batchSize = 50;
        $batches = array_chunk($allData, $batchSize);
        
        foreach ($batches as $batch) {
            foreach ($batch as $row) {
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
                    echo "⚠️  Satır ekleme hatası ($tableName): " . $e->getMessage() . "\n";
                    // Detayları sadece ilk birkaç hata için göster
                    if ($insertCount < 3) {
                        echo "Veri: " . json_encode($cleanRow) . "\n";
                    }
                }
            }
        }
        
        echo "✅ $tableName: $insertCount kayıt kopyalandı\n";
        return true;
        
    } catch (Exception $e) {
        echo "❌ $tableName veri kopyalama hatası: " . $e->getMessage() . "\n";
        return false;
    }
}

// Migration işlemini başlat
echo "🚀 Migration başlıyor...\n\n";

$successfulTables = [];
$failedTables = [];

foreach ($tables as $tableName) {
    echo "=== $tableName Tablosu İşleniyor ===\n";
    
    // 1. Supabase'deki tablo yapısını analiz et
    $columns = analyzeSupabaseTable($tableName, $supabase);
    
    if ($columns === null) {
        $failedTables[] = $tableName;
        echo "❌ $tableName tablosu atlandı\n\n";
        continue;
    }
    
    // 2. MariaDB'de tabloyu oluştur
    $tableCreated = createMariaDBTable($tableName, $columns, $mariadb);
    
    if (!$tableCreated) {
        $failedTables[] = $tableName;
        echo "❌ $tableName tablo oluşturma başarısız\n\n";
        continue;
    }
    
    // 3. Verileri kopyala
    $dataMigrated = migrateTableData($tableName, $supabase, $mariadb);
    
    if ($dataMigrated) {
        $successfulTables[] = $tableName;
        echo "✅ $tableName başarıyla tamamlandı\n\n";
    } else {
        $failedTables[] = $tableName;
        echo "❌ $tableName veri kopyalama başarısız\n\n";
    }
}

// Özet rapor
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 MIGRATION RAPORU\n";
echo str_repeat("=", 50) . "\n";

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

echo "\n🎉 Migration tamamlandı!\n";
echo "MariaDB'yi kullanmaya başlamak için .env dosyasında DB_TYPE=mariadb yapın.\n";
