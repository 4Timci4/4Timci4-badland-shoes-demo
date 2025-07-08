<?php
/**
 * Supabase Image Upload Test Sayfası
 */

// Hata ayıklama aktif
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SecurityManager sınıfının dahil edildiğinden emin ol
require_once 'lib/SecurityManager.php';
require_once 'config/env.php';
require_once 'lib/SupabaseImageManager.php';

// Temp dizini kontrolü
$temp_dir = sys_get_temp_dir() . '/bandland_temp_images/';
if (!file_exists($temp_dir)) {
    mkdir($temp_dir, 0755, true);
    echo "<p>Temp dizini oluşturuldu: $temp_dir</p>";
}

// Test başlığı
echo "<h2>Supabase Image Upload Test</h2>";

// Sistem gereksinimlerini kontrol et
$imageManager = SupabaseImageManager::getInstance();
$requirements = $imageManager->checkSystemRequirements();

echo "<h3>Sistem Gereksinimleri:</h3>";
echo "<ul>";
foreach ($requirements as $req => $status) {
    $statusText = $status ? '✅ Mevcut' : '❌ Eksik';
    echo "<li><strong>$req:</strong> $statusText</li>";
}
echo "</ul>";

// Supabase bağlantı bilgileri
echo "<h3>Supabase Konfigürasyon:</h3>";
echo "<ul>";
echo "<li><strong>URL:</strong> " . SUPABASE_URL . "</li>";
echo "<li><strong>API Key:</strong> " . substr(SUPABASE_KEY, 0, 10) . "...</li>";
echo "<li><strong>DB Type:</strong> " . DB_TYPE . "</li>";
echo "</ul>";

// Dosya yükleme formu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    echo "<h3>Upload Sonucu:</h3>";
    
    try {
        $file = $_FILES['test_image'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo "<p style='color: red;'>Upload hatası: " . $file['error'] . "</p>";
        } else {
            $result = $imageManager->uploadAndOptimize($file, [
                'prefix' => 'test_',
                'generate_thumbnail' => true,
                'generate_webp' => true
            ]);
            
            if ($result['success']) {
                echo "<p style='color: green;'>✅ Upload başarılı!</p>";
                echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "</pre>";
                
                // Resmi göster
                if (isset($result['original']['url'])) {
                    echo "<h4>Yüklenen Resim:</h4>";
                    echo "<img src='" . htmlspecialchars($result['original']['url']) . "' style='max-width: 300px; border: 1px solid #ccc;' alt='Test Image'>";
                }
            } else {
                echo "<p style='color: red;'>❌ Upload başarısız!</p>";
                echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Hata: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Supabase Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .form-group { margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h3>Resim Upload Testi:</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="test_image">Test resmi seçin:</label><br>
                <input type="file" name="test_image" id="test_image" accept="image/*" required>
            </div>
            <div class="form-group">
                <button type="submit">Upload Test Et</button>
            </div>
        </form>
        
        <h3>Not:</h3>
        <ul>
            <li>GD extension eksikse sadece original dosya yüklenecek</li>
            <li>Supabase bucket: <strong>product-images</strong></li>
            <li>Desteklenen formatlar: JPG, PNG, GIF, WebP</li>
            <li>Maksimum dosya boyutu: 10MB</li>
        </ul>
    </div>
</body>
</html>
