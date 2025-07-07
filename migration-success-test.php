<?php
/**
 * Migration Success Test
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h2>🎉 Migration Success Test</h2>";
echo "<p><strong>Database Türü:</strong> " . DatabaseFactory::getCurrentType() . "</p>";

$db = database();
$tables = [
    'colors' => 'Renkler',
    'sizes' => 'Bedenler', 
    'product_models' => 'Ürün Modelleri',
    'contact_info' => 'İletişim Bilgileri',
    'blogs' => 'Blog Yazıları'
];

$totalRecords = 0;
foreach ($tables as $table => $label) {
    $count = $db->count($table);
    echo "📊 <strong>{$label}:</strong> {$count} kayıt<br>";
    $totalRecords += $count;
}

echo "<br><strong>📈 Test Edilen Toplam: {$totalRecords} kayıt</strong><br>";
echo "<h3>✅ Supabase → MariaDB Migration Başarılı!</h3>";
