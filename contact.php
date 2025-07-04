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
<section class="bg-primary text-white py-12 text-center mb-12">
    <div class="max-w-7xl mx-auto px-5">
        <h1 class="text-5xl font-bold mb-4">İletişim</h1>
        <p class="text-xl">Bize ulaşın</p>
    </div>
</section>

<!-- İletişim Bilgileri ve Form -->
<section class="py-0 pb-12">
    <div class="max-w-7xl mx-auto px-5">
        <?php if($message_sent): ?>
            <div class="max-w-2xl mx-auto text-center py-12 px-8 bg-light rounded-lg shadow-lg">
                <i class="fas fa-check-circle text-6xl text-green-500 mb-5"></i>
                <h3 class="text-3xl font-bold mb-4 text-secondary">Mesajınız Başarıyla Gönderildi!</h3>
                <p class="text-gray-600 mb-6">En kısa sürede size geri dönüş yapacağız.</p>
                <a href="contact.php" class="inline-block px-8 py-3 bg-primary text-white rounded-full font-semibold hover:bg-pink-600 transition-colors duration-300">Yeni Mesaj Gönder</a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-10 mb-12">
                <div class="flex-1">
                    <h2 class="text-3xl font-bold mb-5 text-secondary">İletişim Bilgileri</h2>
                    <p class="text-gray-600 mb-8 leading-relaxed">Herhangi bir sorunuz, öneriniz veya geri bildiriminiz mi var? Aşağıdaki bilgiler aracılığıyla bize ulaşabilir veya iletişim formunu doldurabilirsiniz.</p>
                    
                    <div class="flex gap-5 mb-6">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2 text-secondary">Adres</h3>
                            <p class="text-gray-600">Bağdat Caddesi No:123<br>Kadıköy, İstanbul</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-5 mb-6">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2 text-secondary">Telefon</h3>
                            <p class="text-gray-600 mb-1">+90 555 123 4567</p>
                            <p class="text-gray-600">+90 216 123 4567</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-5 mb-6">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2 text-secondary">E-posta</h3>
                            <p class="text-gray-600 mb-1">info@schon.com</p>
                            <p class="text-gray-600">support@schon.com</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-5 mb-8">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2 text-secondary">Çalışma Saatleri</h3>
                            <p class="text-gray-600 mb-1">Pazartesi - Cumartesi: 10:00 - 20:00</p>
                            <p class="text-gray-600">Pazar: 12:00 - 18:00</p>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4 text-secondary">Sosyal Medya</h3>
                        <div class="flex gap-4">
                            <a href="#" class="w-10 h-10 bg-light text-secondary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="w-10 h-10 bg-light text-secondary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="w-10 h-10 bg-light text-secondary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="w-10 h-10 bg-light text-secondary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="w-10 h-10 bg-light text-secondary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 bg-white p-8 rounded-lg shadow-lg">
                    <h2 class="text-3xl font-bold mb-5 text-secondary">Bize Mesaj Gönderin</h2>
                    <form action="contact.php" method="post">
                        <div class="mb-5">
                            <label for="name" class="block mb-2 text-secondary font-semibold">Adınız Soyadınız *</label>
                            <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded focus:border-primary focus:outline-none transition-colors duration-300">
                        </div>
                        
                        <div class="mb-5">
                            <label for="email" class="block mb-2 text-secondary font-semibold">E-posta Adresiniz *</label>
                            <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded focus:border-primary focus:outline-none transition-colors duration-300">
                        </div>
                        
                        <div class="mb-5">
                            <label for="subject" class="block mb-2 text-secondary font-semibold">Konu *</label>
                            <input type="text" id="subject" name="subject" required class="w-full px-4 py-3 border border-gray-300 rounded focus:border-primary focus:outline-none transition-colors duration-300">
                        </div>
                        
                        <div class="mb-6">
                            <label for="message" class="block mb-2 text-secondary font-semibold">Mesajınız *</label>
                            <textarea id="message" name="message" rows="5" required class="w-full px-4 py-3 border border-gray-300 rounded focus:border-primary focus:outline-none transition-colors duration-300"></textarea>
                        </div>
                        
                        <button type="submit" name="submit" class="w-full px-8 py-3 bg-primary text-white rounded font-semibold hover:bg-pink-600 transition-colors duration-300">Mesaj Gönder</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Harita -->
<section class="py-12 bg-light">
    <div class="max-w-7xl mx-auto px-5">
        <h2 class="text-3xl font-bold mb-8 text-secondary text-center">Bizi Ziyaret Edin</h2>
        <div class="rounded-lg overflow-hidden shadow-lg">
            <!-- Google Maps kullanılarak gerçek harita entegrasyonu yapılabilir -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d24086.521847965187!2d29.040827342981352!3d40.96982862395644!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab617a4696a95%3A0xb08d84362e53c232!2zQmHEn2RhdCBDYWRkZXNpLCBLYWTEsWvDtnkvxLBzdGFuYnVs!5e0!3m2!1str!2str!4v1624529096157!5m2!1str!2str" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<!-- Mağazalarımız -->
<section class="py-12 pb-20">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-bold mb-3 text-secondary">Mağazalarımız</h2>
            <p class="text-gray-600">Türkiye genelindeki mağazalarımız</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white rounded-lg overflow-hidden shadow-lg hover:-translate-y-2 transition-transform duration-300">
                <div class="h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTZ8fHNob2UlMjBzdG9yZXxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60" alt="İstanbul Mağazası" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-4 text-secondary">İstanbul - Nişantaşı</h3>
                    <p class="text-gray-600 mb-2"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Teşvikiye Mah. Abdi İpekçi Cad. No:45</p>
                    <p class="text-gray-600 mb-2"><i class="fas fa-phone text-primary mr-2"></i> +90 212 123 4567</p>
                    <p class="text-gray-600"><i class="fas fa-clock text-primary mr-2"></i> 10:00 - 20:00</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg overflow-hidden shadow-lg hover:-translate-y-2 transition-transform duration-300">
                <div class="h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1626379616459-b4807c9bc5df?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTJ8fHNob2UlMjBzdG9yZXxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60" alt="İstanbul Mağazası" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-4 text-secondary">İstanbul - Kadıköy</h3>
                    <p class="text-gray-600 mb-2"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Bağdat Caddesi No:123</p>
                    <p class="text-gray-600 mb-2"><i class="fas fa-phone text-primary mr-2"></i> +90 216 123 4567</p>
                    <p class="text-gray-600"><i class="fas fa-clock text-primary mr-2"></i> 10:00 - 20:00</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg overflow-hidden shadow-lg hover:-translate-y-2 transition-transform duration-300">
                <div class="h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1613125479732-14543c793349?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MjB8fHNob2UlMjBzdG9yZXxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60" alt="Ankara Mağazası" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-4 text-secondary">Ankara - Çankaya</h3>
                    <p class="text-gray-600 mb-2"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Tunalı Hilmi Cad. No:78</p>
                    <p class="text-gray-600 mb-2"><i class="fas fa-phone text-primary mr-2"></i> +90 312 123 4567</p>
                    <p class="text-gray-600"><i class="fas fa-clock text-primary mr-2"></i> 10:00 - 20:00</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg overflow-hidden shadow-lg hover:-translate-y-2 transition-transform duration-300">
                <div class="h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1518293449466-53bbe123add8?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTF8fHNob2UlMjBzdG9yZXxlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60" alt="İzmir Mağazası" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-semibold mb-4 text-secondary">İzmir - Alsancak</h3>
                    <p class="text-gray-600 mb-2"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Kıbrıs Şehitleri Cad. No:56</p>
                    <p class="text-gray-600 mb-2"><i class="fas fa-phone text-primary mr-2"></i> +90 232 123 4567</p>
                    <p class="text-gray-600"><i class="fas fa-clock text-primary mr-2"></i> 10:00 - 20:00</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>