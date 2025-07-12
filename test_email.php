<?php
/**
 * E-posta Test Dosyası
 * 
 * EmailService'i test etmek için kullanılır
 * Bu dosyayı tarayıcıda açarak e-posta gönderimini test edebilirsiniz
 */

require_once 'services/EmailService.php';

// Sadece development ortamında çalışsın
if (!defined('APP_ENV') || APP_ENV !== 'development') {
    die('Bu test dosyası sadece development ortamında çalışır.');
}

$message = '';
$messageType = '';

// Form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testType = $_POST['test_type'] ?? '';
    $recipientEmail = $_POST['recipient_email'] ?? '';
    
    if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'Lütfen geçerli bir e-posta adresi girin.';
        $messageType = 'error';
    } else {
        try {
            $emailService = new EmailService();
            
            if ($testType === 'connection') {
                // SMTP bağlantı testi
                $result = $emailService->testConnection();
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                
            } elseif ($testType === 'registration') {
                // Kayıt e-postası testi
                $result = $emailService->sendRegistrationConfirmation(
                    $recipientEmail,
                    'Test',
                    'Kullanıcı'
                );
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                
            } elseif ($testType === 'custom') {
                // Özel e-posta testi
                $subject = $_POST['subject'] ?? 'Test E-postası';
                $body = $_POST['body'] ?? '<h1>Bu bir test e-postasıdır</h1><p>Bandland Shoes e-posta sistemi çalışıyor!</p>';
                
                $result = $emailService->sendEmail($recipientEmail, $subject, $body);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
            
        } catch (Exception $e) {
            $message = 'Hata: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-posta Test Aracı - Bandland Shoes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2563eb;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="email"], 
        input[type="text"], 
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        textarea {
            height: 100px;
            resize: vertical;
        }
        
        button {
            background-color: #2563eb;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        
        button:hover {
            background-color: #1d4ed8;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .test-section {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .test-section h3 {
            margin-top: 0;
            color: #374151;
        }
        
        .custom-fields {
            display: none;
        }
        
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🦶 Bandland Shoes - E-posta Test Aracı</h1>
        
        <div class="warning">
            <strong>⚠️ Dikkat:</strong> Bu test aracı sadece development ortamında çalışır. Gerçek SMTP bilgilerinizi kullandığından dikkatli olun.
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="recipient_email">Alıcı E-posta Adresi:</label>
                <input type="email" id="recipient_email" name="recipient_email" 
                       value="<?php echo htmlspecialchars($_POST['recipient_email'] ?? ''); ?>" 
                       required placeholder="test@example.com">
            </div>
            
            <div class="test-section">
                <h3>🔌 SMTP Bağlantı Testi</h3>
                <p>Sadece SMTP sunucusuna bağlantıyı test eder, e-posta göndermez.</p>
                <button type="submit" name="test_type" value="connection">Bağlantıyı Test Et</button>
            </div>
            
            <div class="test-section">
                <h3>🎉 Kayıt E-postası Testi</h3>
                <p>Gerçek kayıt e-postası şablonunu test eder.</p>
                <button type="submit" name="test_type" value="registration">Kayıt E-postası Gönder</button>
            </div>
            
            <div class="test-section">
                <h3>✉️ Özel E-posta Testi</h3>
                <p>Kendi içeriğinizi belirleyerek e-posta gönderir.</p>
                
                <div class="form-group">
                    <label for="subject">Konu:</label>
                    <input type="text" id="subject" name="subject" 
                           value="<?php echo htmlspecialchars($_POST['subject'] ?? 'Test E-postası'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="body">İçerik (HTML):</label>
                    <textarea id="body" name="body" placeholder="<h1>Test</h1><p>Bu bir test e-postasıdır.</p>"><?php echo htmlspecialchars($_POST['body'] ?? '<h1>Bu bir test e-postasıdır</h1><p>Bandland Shoes e-posta sistemi çalışıyor!</p>'); ?></textarea>
                </div>
                
                <button type="submit" name="test_type" value="custom">Özel E-posta Gönder</button>
            </div>
        </form>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 14px;">
            <strong>Mevcut SMTP Ayarları:</strong><br>
            Sunucu: <?php echo defined('MAIL_HOST') ? MAIL_HOST : 'Tanımlanmamış'; ?><br>
            Port: <?php echo defined('MAIL_PORT') ? MAIL_PORT : 'Tanımlanmamış'; ?><br>
            Kullanıcı: <?php echo defined('MAIL_USERNAME') ? MAIL_USERNAME : 'Tanımlanmamış'; ?><br>
            Şifreleme: <?php echo defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : 'Tanımlanmamış'; ?>
        </div>
    </div>
</body>
</html>