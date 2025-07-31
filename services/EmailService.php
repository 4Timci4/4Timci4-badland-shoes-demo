<?php

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/SMTP.php';
require_once __DIR__ . '/../services/SettingsService.php';
require_once __DIR__ . '/../lib/DatabaseFactory.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $settingsService;
    private $db;

    public function __construct()
    {
        $this->db = database();
        $this->mailer = new PHPMailer(true);
        $this->settingsService = new SettingsService();
        
        if ($this->db) {
            $this->configureMailer();
        }
    }

    private function configureMailer()
    {
        if (!$this->db) {
            return; // Demo modunda SMTP yapılandırması gerekmiyor
        }
        
        try {
            $settings = $this->getEmailSettings();

            if (empty($settings['mail_host']) || empty($settings['mail_from_address'])) {
                return;
            }

            $this->mailer->isSMTP();
            $this->mailer->Host = $settings['mail_host'] ?? '';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $settings['mail_username'] ?? '';
            $this->mailer->Password = $settings['mail_password'] ?? '';
            $this->mailer->SMTPSecure = $settings['mail_encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $settings['mail_port'] ?? 587;

            $this->mailer->CharSet = 'UTF-8';

            $this->mailer->setFrom($settings['mail_from_address'] ?? '', $settings['mail_from_name'] ?? '');

            if (APP_ENV === 'development') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
                $this->mailer->Debugoutput = 'html';
            }

        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
        }
    }

    public function sendEmail($to, $subject, $body, $altBody = ''): array
    {
        if (!$this->db) {
            return $this->getDemoEmailResponse($to, $subject);
        }
        
        try {
            $this->mailer->addAddress($to);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody;

            $this->mailer->send();
            $this->mailer->clearAddresses();

            return ['success' => true, 'message' => 'E-posta başarıyla gönderildi.'];

        } catch (Exception $e) {
            error_log("Email send error: " . $e->getMessage());
            $this->mailer->clearAddresses();
            return ['success' => false, 'message' => 'E-posta gönderilemedi: ' . $e->getMessage()];
        }
    }

    public function sendRegistrationConfirmation($email, $firstName, $lastName)
    {
        if (!$this->db) {
            return $this->getDemoRegistrationResponse($email, $firstName, $lastName);
        }
        
        $fullName = trim($firstName . ' ' . $lastName);
        $template = $this->getEmailTemplate('registration_confirmation');

        if (!$template) {
            return ['success' => false, 'message' => 'Kayıt onay e-posta şablonu bulunamadı.'];
        }

        $placeholders = ['fullName' => $fullName];
        $subject = $this->replacePlaceholders($template['subject'], $placeholders);
        $htmlBody = $this->replacePlaceholders($template['body_html'], $placeholders);
        $textBody = $this->replacePlaceholders($template['body_text'], $placeholders);

        return $this->sendEmail($email, $subject, $htmlBody, $textBody);
    }

    public function sendPasswordResetEmail($email, $token, $firstName = '', $lastName = '')
    {
        if (!$this->db) {
            return $this->getDemoPasswordResetResponse($email, $token, $firstName, $lastName);
        }
        
        $fullName = trim($firstName . ' ' . $lastName);
        if (empty($fullName)) {
            $fullName = 'Değerli Müşterimiz';
        }

        $template = $this->getEmailTemplate('password_reset');

        if (!$template) {
            return ['success' => false, 'message' => 'Şifre sıfırlama e-posta şablonu bulunamadı.'];
        }

        $resetLink = (APP_ENV === 'development' ? 'http://localhost:3000' : 'https://badlandshoes.com.tr') . '/reset-password.php?token=' . urlencode($token);

        $placeholders = [
            'fullName' => $fullName,
            'reset_link' => $resetLink
        ];

        $subject = $this->replacePlaceholders($template['subject'], $placeholders);
        $htmlBody = $this->replacePlaceholders($template['body_html'], $placeholders);
        $textBody = $this->replacePlaceholders($template['body_text'], $placeholders);

        return $this->sendEmail($email, $subject, $htmlBody, $textBody);
    }

    private function getEmailTemplate($templateName)
    {
        if (!$this->db) {
            return $this->getDemoEmailTemplate($templateName);
        }
        
        try {
            $db = database();
            $template = $db->select('email_templates', ['name' => $templateName], '*', ['limit' => 1]);
            return $template ? $template[0] : null;
        } catch (Exception $e) {
            error_log("Email template fetch error: " . $e->getMessage());
            return null;
        }
    }

    public function replacePlaceholders($content, $placeholders)
    {
        foreach ($placeholders as $key => $value) {
            $content = str_replace('{{' . $key . '}}', htmlspecialchars($value), $content);
        }
        $content = str_replace('{{site_url}}', (APP_ENV === 'development' ? 'http://localhost:3000' : 'https://badlandshoes.com.tr'), $content);
        $content = str_replace('{{contact_url}}', (APP_ENV === 'development' ? 'http://localhost:3000/contact.php' : 'https://badlandshoes.com.tr/contact.php'), $content);
        $content = str_replace('{{current_year}}', date('Y'), $content);
        return $content;
    }

    public function testConnection()
    {
        if (!$this->db) {
            return $this->getDemoConnectionTest();
        }
        
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            return ['success' => true, 'message' => 'SMTP bağlantısı başarılı.'];
        } catch (Exception $e) {
            error_log("SMTP connection test failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'SMTP bağlantısı başarısız: ' . $e->getMessage()];
        }
    }

    public function getEmailSettings()
    {
        if (!$this->db) {
            return $this->getDemoEmailSettings();
        }
        
        return $this->settingsService->getSettingsByGroup('email');
    }

    public function updateEmailSettings($settings)
    {
        if (!$this->db) {
            return false; // Demo modunda ayar güncellemesi devre dışı
        }
        
        return $this->settingsService->updateMultipleSettings($settings, 'email');
    }

    public function sendTestEmail($to): array
    {
        if (!$this->db) {
            return $this->getDemoTestEmail($to);
        }
        
        $subject = 'SMTP Test E-postası';
        $body = '
            <h1>SMTP Ayarları Başarılı!</h1>
            <p>Bu e-posta, sitenizdeki SMTP ayarlarının doğru bir şekilde yapılandırıldığını doğrulamak için gönderilmiştir.</p>
            <p>Tebrikler, e-posta gönderim sisteminiz çalışıyor!</p>
        ';
        $altBody = 'SMTP Ayarları Başarılı! Bu e-posta, sitenizdeki SMTP ayarlarının doğru bir şekilde yapılandırıldığını doğrulamak için gönderilmiştir.';

        $this->configureMailer();

        return $this->sendEmail($to, $subject, $body, $altBody);
    }

    public function getSamplePlaceholders($templateName)
    {
        $placeholders = [
            'fullName' => 'Ayşe Yılmaz',
        ];

        switch ($templateName) {
            case 'password_reset':
                $placeholders['reset_link'] = (APP_ENV === 'development' ? 'http://localhost:3000' : 'https://badlandshoes.com.tr') . '/reset-password.php?token=ornek_test_tokeni_123456';
                break;
            case 'registration_confirmation':
                break;
        }

        return $placeholders;
    }

    /**
     * Demo email gönderme yanıtı
     */
    private function getDemoEmailResponse($to, $subject)
    {
        error_log("DEMO MODE: Email gönderildi - To: $to, Subject: $subject");
        return [
            'success' => true,
            'message' => 'E-posta başarıyla gönderildi (Demo Modu)',
            'demo_info' => [
                'to' => $to,
                'subject' => $subject,
                'sent_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Demo kayıt onayı yanıtı
     */
    private function getDemoRegistrationResponse($email, $firstName, $lastName)
    {
        $fullName = trim($firstName . ' ' . $lastName);
        error_log("DEMO MODE: Kayıt onayı emaili - User: $fullName, Email: $email");
        
        return [
            'success' => true,
            'message' => 'Kayıt onayı e-postası başarıyla gönderildi (Demo Modu)',
            'demo_info' => [
                'to' => $email,
                'user_name' => $fullName,
                'template' => 'registration_confirmation',
                'sent_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Demo şifre sıfırlama yanıtı
     */
    private function getDemoPasswordResetResponse($email, $token, $firstName, $lastName)
    {
        $fullName = trim($firstName . ' ' . $lastName);
        if (empty($fullName)) {
            $fullName = 'Değerli Müşterimiz';
        }
        
        error_log("DEMO MODE: Şifre sıfırlama emaili - User: $fullName, Email: $email, Token: $token");
        
        return [
            'success' => true,
            'message' => 'Şifre sıfırlama e-postası başarıyla gönderildi (Demo Modu)',
            'demo_info' => [
                'to' => $email,
                'user_name' => $fullName,
                'reset_token' => $token,
                'template' => 'password_reset',
                'sent_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Demo email şablonları
     */
    private function getDemoEmailTemplate($templateName)
    {
        $templates = [
            'registration_confirmation' => [
                'id' => 1,
                'name' => 'registration_confirmation',
                'subject' => 'Hoş Geldiniz {{fullName}}! Kayıt İşleminiz Tamamlandı',
                'body_html' => '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                        <h1 style="color: #333;">Hoş Geldiniz!</h1>
                        <p>Merhaba {{fullName}},</p>
                        <p>Bandland Shoes ailesine hoş geldiniz! Kayıt işleminiz başarıyla tamamlanmıştır.</p>
                        <p>Artık sitemizde bulunan özel tekliflerden ve kampanyalardan faydalanabilirsiniz.</p>
                        <p>İyi alışverişler dileriz!</p>
                        <hr>
                        <p><small>Bu email {{current_year}} yılında {{site_url}} adresinden gönderilmiştir.</small></p>
                    </div>
                ',
                'body_text' => 'Merhaba {{fullName}}, Bandland Shoes ailesine hoş geldiniz! Kayıt işleminiz başarıyla tamamlanmıştır.',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            'password_reset' => [
                'id' => 2,
                'name' => 'password_reset',
                'subject' => 'Şifre Sıfırlama Talebi - {{fullName}}',
                'body_html' => '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                        <h1 style="color: #333;">Şifre Sıfırlama</h1>
                        <p>Merhaba {{fullName}},</p>
                        <p>Hesabınız için şifre sıfırlama talebinde bulunuldu.</p>
                        <p>Şifrenizi sıfırlamak için aşağıdaki linke tıklayın:</p>
                        <p><a href="{{reset_link}}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Şifremi Sıfırla</a></p>
                        <p>Bu link 1 saat süreyle geçerlidir.</p>
                        <p>Eğer bu talebi siz yapmadıysanız, bu emaili görmezden gelebilirsiniz.</p>
                        <hr>
                        <p><small>Bu email {{current_year}} yılında {{site_url}} adresinden gönderilmiştir.</small></p>
                    </div>
                ',
                'body_text' => 'Merhaba {{fullName}}, Hesabınız için şifre sıfırlama talebinde bulunuldu. Şifrenizi sıfırlamak için bu linke gidin: {{reset_link}}',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        return $templates[$templateName] ?? null;
    }

    /**
     * Demo SMTP bağlantı testi
     */
    private function getDemoConnectionTest()
    {
        return [
            'success' => true,
            'message' => 'SMTP bağlantısı başarılı (Demo Modu - Gerçek bağlantı yapılmadı)',
            'demo_info' => [
                'test_time' => date('Y-m-d H:i:s'),
                'mode' => 'demo'
            ]
        ];
    }

    /**
     * Demo email ayarları
     */
    private function getDemoEmailSettings()
    {
        return [
            'mail_host' => 'smtp.example.com',
            'mail_port' => '587',
            'mail_username' => 'demo@example.com',
            'mail_password' => '***masked***',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@bandlandshoes.com',
            'mail_from_name' => 'Bandland Shoes',
            'mail_enabled' => '1'
        ];
    }

    /**
     * Demo test email yanıtı
     */
    private function getDemoTestEmail($to)
    {
        error_log("DEMO MODE: Test emaili gönderildi - To: $to");
        
        return [
            'success' => true,
            'message' => 'Test e-postası başarıyla gönderildi (Demo Modu)',
            'demo_info' => [
                'to' => $to,
                'subject' => 'SMTP Test E-postası',
                'test_time' => date('Y-m-d H:i:s'),
                'mode' => 'demo'
            ]
        ];
    }
}