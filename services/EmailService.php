<?php
/**
 * E-posta Gönderim Servisi
 * 
 * PHPMailer kullanarak güvenli e-posta gönderimi sağlar
 */

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer-6.9.1/src/SMTP.php';
require_once __DIR__ . '/../services/SettingsService.php'; // SettingsService'i dahil et

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    private $settingsService;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->settingsService = new SettingsService();
        $this->configureMailer();
    }
    
    /**
     * PHPMailer'ı veritabanındaki ayarlarla yapılandır
     */
    private function configureMailer() {
        try {
            $settings = $this->getEmailSettings();

            // Server ayarları
            $this->mailer->isSMTP();
            $this->mailer->Host = $settings['mail_host'] ?? '';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $settings['mail_username'] ?? '';
            $this->mailer->Password = $settings['mail_password'] ?? '';
            $this->mailer->SMTPSecure = $settings['mail_encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $settings['mail_port'] ?? 587;
            
            // Karakter kodlaması
            $this->mailer->CharSet = 'UTF-8';
            
            // Gönderen bilgileri
            $this->mailer->setFrom($settings['mail_from_address'] ?? '', $settings['mail_from_name'] ?? '');
            
            // Debug ayarları (development ortamında)
            if (APP_ENV === 'development') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_OFF; // Hata ayıklama için DEBUG_SERVER olarak değiştirilebilir
                $this->mailer->Debugoutput = 'html';
            }
            
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
            // throw $e; // Uygulamanın çökmesini önlemek için hatayı yakala ama fırlatma
        }
    }
    
    /**
     * Genel e-posta gönderme metodu
     * 
     * @param string $to Alıcı e-posta adresi
     * @param string $subject Konu
     * @param string $body HTML içerik
     * @param string $altBody Alternatif metin içerik (opsiyonel)
     * @return array Başarı durumu ve mesaj
     */
    public function sendEmail($to, $subject, $body, $altBody = '') {
        try {
            // Alıcı
            $this->mailer->addAddress($to);
            
            // İçerik
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
    
    /**
     * Kayıt onay e-postası gönder
     *
     * @param string $email Kullanıcı e-posta adresi
     * @param string $firstName Kullanıcı adı
     * @param string $lastName Kullanıcı soyadı
     * @return array Başarı durumu ve mesaj
     */
    public function sendRegistrationConfirmation($email, $firstName, $lastName) {
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

    /**
     * Şifre sıfırlama e-postası gönder
     *
     * @param string $email Kullanıcı e-posta adresi
     * @param string $token Şifre sıfırlama token'ı
     * @param string $firstName Kullanıcı adı
     * @param string $lastName Kullanıcı soyadı
     * @return array Başarı durumu ve mesaj
     */
    public function sendPasswordResetEmail($email, $token, $firstName = '', $lastName = '') {
        $fullName = trim($firstName . ' ' . $lastName);
        if (empty($fullName)) {
            $fullName = 'Değerli Müşterimiz';
        }
        
        $template = $this->getEmailTemplate('password_reset');
        
        if (!$template) {
            return ['success' => false, 'message' => 'Şifre sıfırlama e-posta şablonu bulunamadı.'];
        }

        // Şifre sıfırlama linkini oluştur
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

    /**
     * Veritabanından belirli bir e-posta şablonunu getirir.
     * @param string $templateName Şablonun adı (örn: registration_confirmation)
     * @return array|null Şablon verisi veya bulunamazsa null
     */
    private function getEmailTemplate($templateName) {
        try {
            $db = database();
            $template = $db->select('email_templates', ['name' => $templateName], '*', ['limit' => 1]);
            return $template ? $template[0] : null;
        } catch (Exception $e) {
            error_log("Email template fetch error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Metindeki yer tutucuları değiştirir.
     * @param string $content Değiştirilecek metin
     * @param array $placeholders Değiştirilecek anahtar-değer çiftleri
     * @return string Değiştirilmiş metin
     */
    public function replacePlaceholders($content, $placeholders) {
        foreach ($placeholders as $key => $value) {
            $content = str_replace('{{' . $key . '}}', htmlspecialchars($value), $content);
        }
        // Genel yer tutucular
        $content = str_replace('{{site_url}}', (APP_ENV === 'development' ? 'http://localhost:3000' : 'https://badlandshoes.com.tr'), $content);
        $content = str_replace('{{contact_url}}', (APP_ENV === 'development' ? 'http://localhost:3000/contact.php' : 'https://badlandshoes.com.tr/contact.php'), $content);
        $content = str_replace('{{current_year}}', date('Y'), $content);
        return $content;
    }

    /**
     * SMTP bağlantısını test et
     *
     * @return array Test sonucu
     */
    public function testConnection() {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            return ['success' => true, 'message' => 'SMTP bağlantısı başarılı.'];
        } catch (Exception $e) {
            error_log("SMTP connection test failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'SMTP bağlantısı başarısız: ' . $e->getMessage()];
        }
    }

    /**
     * E-posta ayarlarını veritabanından getirir.
     * @return array E-posta ayarları
     */
    public function getEmailSettings() {
        return $this->settingsService->getSettingsByGroup('email');
    }

    /**
     * E-posta ayarlarını veritabanında günceller.
     * @param array $settings Güncellenecek ayarlar
     * @return bool Başarı durumu
     */
    public function updateEmailSettings($settings) {
        return $this->settingsService->updateMultipleSettings($settings, 'email');
    }

    /**
     * Test e-postası gönderir.
     * @param string $to Test e-postasının gönderileceği adres
     * @return array Başarı durumu ve mesaj
     */
    public function sendTestEmail($to) {
        $subject = 'SMTP Test E-postası';
        $body = '
            <h1>SMTP Ayarları Başarılı!</h1>
            <p>Bu e-posta, sitenizdeki SMTP ayarlarının doğru bir şekilde yapılandırıldığını doğrulamak için gönderilmiştir.</p>
            <p>Tebrikler, e-posta gönderim sisteminiz çalışıyor!</p>
        ';
        $altBody = 'SMTP Ayarları Başarılı! Bu e-posta, sitenizdeki SMTP ayarlarının doğru bir şekilde yapılandırıldığını doğrulamak için gönderilmiştir.';
        
        // Ayarları yeniden yükle, çünkü kaydedildikten hemen sonra test ediliyor olabilir
        $this->configureMailer();
        
        return $this->sendEmail($to, $subject, $body, $altBody);
    }

    /**
     * Şablona göre örnek yer tutucu verileri döndürür.
     * @param string $templateName Şablonun adı
     * @return array Örnek veri dizisi
     */
    public function getSamplePlaceholders($templateName) {
        $placeholders = [
            'fullName' => 'Ayşe Yılmaz',
            // Genel değişkenler her zaman eklenir
        ];

        switch ($templateName) {
            case 'password_reset':
                $placeholders['reset_link'] = (APP_ENV === 'development' ? 'http://localhost:3000' : 'https://badlandshoes.com.tr') . '/reset-password.php?token=ornek_test_tokeni_123456';
                break;
            case 'registration_confirmation':
                // Bu şablon için özel bir değişken yok, sadece fullName yeterli.
                break;
            // Diğer şablonlar için buraya case'ler eklenebilir
        }

        return $placeholders;
    }
}