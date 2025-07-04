<?php include 'includes/header.php'; ?>

<?php
// Form gönderildi mi kontrol et
$message_sent = false;
if(isset($_POST['submit'])) {
    // Form verilerini al
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '';
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';
    
    // Basit validasyon
    if(!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        // Gerçek uygulamada burada e-posta gönderimi yapılır
        // mail($to, $subject, $message, $headers);
        
        // Mesaj gönderildi olarak işaretle
        $message_sent = true;
    }
}
?>

<!-- İletişim Banner -->
<section class="page-banner">
    <div class="container">
        <h1>İletişim</h1>
        <p>Bize ulaşın</p>
    </div>
</section>

<!-- İletişim Bilgileri ve Form -->
<section class="contact-section">
    <div class="container">
        <?php if($message_sent): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <h3>Mesajınız Başarıyla Gönderildi!</h3>
                <p>En kısa sürede size geri dönüş yapacağız.</p>
                <a href="contact.php" class="btn">Yeni Mesaj Gönder</a>
            </div>
        <?php else: ?>
            <div class="contact-container">
                <div class="contact-info">
                    <h2>İletişim Bilgileri</h2>
                    <p>Herhangi bir sorunuz, öneriniz veya geri bildiriminiz mi var? Aşağıdaki bilgiler aracılığıyla bize ulaşabilir veya iletişim formunu doldurabilirsiniz.</p>
                    
                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="text">
                            <h3>Adres</h3>
                            <p>Bağdat Caddesi No:123<br>Kadıköy, İstanbul</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="text">
                            <h3>Telefon</h3>
                            <p>+90 555 123 4567</p>
                            <p>+90 216 123 4567</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="text">
                            <h3>E-posta</h3>
                            <p>info@schon.com</p>
                            <p>support@schon.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="text">
                            <h3>Çalışma Saatleri</h3>
                            <p>Pazartesi - Cumartesi: 10:00 - 20:00</p>
                            <p>Pazar: 12:00 - 18:00</p>
                        </div>
                    </div>
                    
                    <div class="social-media">
                        <h3>Sosyal Medya</h3>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h2>Bize Mesaj Gönderin</h2>
                    <form action="contact.php" method="post">
                        <div class="form-group">
                            <label for="name">Adınız Soyadınız *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-posta Adresiniz *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Konu *</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Mesajınız *</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" name="submit" class="btn">Mesaj Gönder</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Harita -->
<section class="map-section">
    <div class="container">
        <h2>Bizi Ziyaret Edin</h2>
        <div class="map-container">
            <!-- Google Maps kullanılarak gerçek harita entegrasyonu yapılabilir -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d24086.521847965187!2d29.040827342981352!3d40.96982862395644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab617a4696a95%3A0xb08d84362e53c232!2zQmHEn2RhdCBDYWRkZXNpLCBLYWTEsWvDtnkvxLBzdGFuYnVs!5e0!3m2!1str!2str!4v1624529096157!5m2!1str!2str" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<!-- Mağazalarımız -->
<section class="stores-section">
    <div class="container">
        <div class="section-title">
            <h2>Mağazalarımız</h2>
            <p>Türkiye genelindeki mağazalarımız</p>
        </div>
        
        <div class="stores-grid">
            <div class="store-card">
                <div class="store-image">
                    <img src="https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTZ8fHNob2UlMjBzdG9yZXxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60" alt="İstanbul Mağazası">
                </div>
                <div class="store-info">
                    <h3>İstanbul - Nişantaşı</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Teşvikiye Mah. Abdi İpekçi Cad. No:45</p>
                    <p><i class="fas fa-phone"></i> +90 212 123 4567</p>
                    <p><i class="fas fa-clock"></i> 10:00 - 20:00</p>
                </div>
            </div>
            
            <div class="store-card">
                <div class="store-image">
                    <img src="https://images.unsplash.com/photo-1626379616459-b4807c9bc5df?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTJ8fHNob2UlMjBzdG9yZXxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60" alt="İstanbul Mağazası">
                </div>
                <div class="store-info">
                    <h3>İstanbul - Kadıköy</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Bağdat Caddesi No:123</p>
                    <p><i class="fas fa-phone"></i> +90 216 123 4567</p>
                    <p><i class="fas fa-clock"></i> 10:00 - 20:00</p>
                </div>
            </div>
            
            <div class="store-card">
                <div class="store-image">
                    <img src="https://images.unsplash.com/photo-1613125479732-14543c793349?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MjB8fHNob2UlMjBzdG9yZXxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60" alt="Ankara Mağazası">
                </div>
                <div class="store-info">
                    <h3>Ankara - Çankaya</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Tunalı Hilmi Cad. No:78</p>
                    <p><i class="fas fa-phone"></i> +90 312 123 4567</p>
                    <p><i class="fas fa-clock"></i> 10:00 - 20:00</p>
                </div>
            </div>
            
            <div class="store-card">
                <div class="store-image">
                    <img src="https://images.unsplash.com/photo-1518293449466-53bbe123add8?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTF8fHNob2UlMjBzdG9yZXxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60" alt="İzmir Mağazası">
                </div>
                <div class="store-info">
                    <h3>İzmir - Alsancak</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Kıbrıs Şehitleri Cad. No:56</p>
                    <p><i class="fas fa-phone"></i> +90 232 123 4567</p>
                    <p><i class="fas fa-clock"></i> 10:00 - 20:00</p>
                </div>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="/assets/css/contact.css">

<?php include 'includes/footer.php'; ?>