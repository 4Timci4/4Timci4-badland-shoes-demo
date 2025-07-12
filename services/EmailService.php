<?php

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/SMTP.php';
require_once __DIR__ . '/../services/SettingsService.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $settingsService;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->settingsService = new SettingsService();
        $this->configureMailer();
    }

    private function configureMailer()
    {
        try {
            $settings = $this->getEmailSettings();

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

    public function sendEmail($to, $subject, $body, $altBody = '')
    {
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
        return $this->settingsService->getSettingsByGroup('email');
    }

    public function updateEmailSettings($settings)
    {
        return $this->settingsService->updateMultipleSettings($settings, 'email');
    }

    public function sendTestEmail($to)
    {
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
}