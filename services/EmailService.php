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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }
    
    /**
     * PHPMailer'ı yapılandır
     */
    private function configureMailer() {
        try {
            // Server ayarları
            $this->mailer->isSMTP();
            $this->mailer->Host = MAIL_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = MAIL_USERNAME;
            $this->mailer->Password = MAIL_PASSWORD;
            $this->mailer->SMTPSecure = MAIL_ENCRYPTION;
            $this->mailer->Port = MAIL_PORT;
            
            // Karakter kodlaması
            $this->mailer->CharSet = 'UTF-8';
            
            // Gönderen bilgileri
            $this->mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            
            // Debug ayarları (development ortamında)
            if (APP_ENV === 'development') {
                $this->mailer->SMTPDebug = SMTP::DEBUG_OFF; // Hata ayıklama için DEBUG_SERVER olarak değiştirilebilir
                $this->mailer->Debugoutput = 'html';
            }
            
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
            throw $e;
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
        $subject = 'Bandland Shoes - Hoş Geldiniz!';
        
        $htmlBody = $this->getRegistrationEmailTemplate($fullName);
        $textBody = $this->getRegistrationEmailTextTemplate($fullName);
        
        return $this->sendEmail($email, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Kayıt onay e-posta şablonu (HTML)
     * 
     * @param string $fullName Kullanıcının tam adı
     * @return string HTML e-posta içeriği
     */
    private function getRegistrationEmailTemplate($fullName) {
        return '
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Hoş Geldiniz - Bandland Shoes</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 3px solid #2563eb;
                    padding-bottom: 20px;
                }
                .logo {
                    font-size: 28px;
                    font-weight: bold;
                    color: #2563eb;
                    margin-bottom: 10px;
                }
                .welcome-message {
                    font-size: 18px;
                    color: #16a34a;
                    margin-bottom: 20px;
                }
                .content {
                    margin-bottom: 30px;
                }
                .button {
                    display: inline-block;
                    background-color: #2563eb;
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    margin: 20px 0;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #e5e7eb;
                    color: #6b7280;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">🦶 Bandland Shoes</div>
                    <div class="welcome-message">Hoş Geldiniz!</div>
                </div>
                
                <div class="content">
                    <p>Merhaba <strong>' . htmlspecialchars($fullName) . '</strong>,</p>
                    
                    <p>Bandland Shoes ailesine katıldığınız için çok mutluyuz! Hesabınız başarıyla oluşturulmuş ve artık tüm özelliklerimizden yararlanabilirsiniz.</p>
                    
                    <p><strong>Neler yapabilirsiniz:</strong></p>
                    <ul>
                        <li>💕 Favori ürünlerinizi kaydedin</li>
                        <li>🛒 Hızlıca alışveriş yapın</li>
                        <li>📊 Sipariş geçmişinizi takip edin</li>
                        <li>🎯 Size özel fırsatlardan haberdar olun</li>
                    </ul>
                    
                    <div style="text-align: center;">
                        <a href="' . (APP_ENV === 'development' ? 'http://localhost' : 'https://badlandshoes.com.tr') . '/products.php" class="button">Alışverişe Başla</a>
                    </div>
                    
                    <p>Herhangi bir sorunuz varsa, bizimle iletişime geçmekten çekinmeyin.</p>
                </div>
                
                <div class="footer">
                    <p>Bu e-postayı Bandland Shoes hesabınızı oluşturduğunuz için aldınız.</p>
                    <p>© ' . date('Y') . ' Bandland Shoes. Tüm hakları saklıdır.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Kayıt onay e-posta şablonu (Metin)
     * 
     * @param string $fullName Kullanıcının tam adı
     * @return string Metin e-posta içeriği
     */
    private function getRegistrationEmailTextTemplate($fullName) {
        return "
Merhaba {$fullName},

Bandland Shoes ailesine hoş geldiniz!

Hesabınız başarıyla oluşturulmuş ve artık tüm özelliklerimizden yararlanabilirsiniz.

Neler yapabilirsiniz:
- Favori ürünlerinizi kaydedin
- Hızlıca alışveriş yapın  
- Sipariş geçmişinizi takip edin
- Size özel fırsatlardan haberdar olun

Alışverişe başlamak için: " . (APP_ENV === 'development' ? 'http://localhost' : 'https://badlandshoes.com.tr') . "/products.php

Herhangi bir sorunuz varsa, bizimle iletişime geçmekten çekinmeyin.

© " . date('Y') . " Bandland Shoes. Tüm hakları saklıdır.
        ";
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
}