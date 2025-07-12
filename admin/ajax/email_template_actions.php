<?php
require_once __DIR__ . '/../config/auth.php';
check_admin_auth();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/EmailService.php';

header('Content-Type: application/json');

// CSRF token kontrolü
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz CSRF token.']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'send_test_email':
        send_test_email();
        break;
    case 'save_draft':
        save_draft();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Geçersiz eylem.']);
        break;
}

function send_test_email() {
    $to = $_POST['to_email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $body_html = $_POST['body_html'] ?? '';

    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Geçerli bir test e-posta adresi girin.']);
        exit;
    }

    try {
        $emailService = new EmailService();
        
        // Test e-postası için genel yer tutucuları hazırla
        $placeholders = [
            'fullName' => 'Test Kullanıcısı',
            'reset_link' => '#',
            'activation_link' => '#',
            // Diğer genel değişkenler EmailService içinde zaten dolduruluyor
        ];
        
        $final_subject = $emailService->replacePlaceholders($subject, $placeholders);
        $final_body = $emailService->replacePlaceholders($body_html, $placeholders);

        $result = $emailService->sendEmail($to, $final_subject, $final_body);

        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => "Test e-postası başarıyla {$to} adresine gönderildi."]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Test e-postası gönderilemedi: ' . $result['message']]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
    }
}

function save_draft() {
    $template_id = $_POST['template_id'] ?? 0;
    $subject = $_POST['subject'] ?? '';
    $body_html = $_POST['body_html'] ?? '';
    $body_text = $_POST['body_text'] ?? '';

    if (empty($template_id)) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz şablon ID.']);
        exit;
    }

    try {
        database()->update('email_templates', [
            'subject' => $subject,
            'body_html' => $body_html,
            'body_text' => $body_text,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $template_id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Taslak başarıyla kaydedildi.',
            'last_saved' => date('H:i:s')
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Taslak kaydedilirken bir hata oluştu: ' . $e->getMessage()]);
    }
}