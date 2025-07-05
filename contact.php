<?php include 'includes/header.php'; ?>

<?php
// DEBUG: Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ContactService'i dahil et
require_once 'config/database.php';
$contactService = contactService();

// DEBUG: Veritabanı bağlantısını test et
echo "<!-- DEBUG: Veritabanı test ediliyor... -->";
try {
    $testResponse = supabase()->request('contact_info?select=*&limit=1');
    echo "<!-- DEBUG: Veritabanı bağlantısı başarılı - Kayıt sayısı: " . count($testResponse['body'] ?? []) . " -->";
} catch (Exception $e) {
    echo "<!-- DEBUG: Veritabanı hatası: " . $e->getMessage() . " -->";
}

// İletişim bilgilerini ve sosyal medya linklerini getir
$contactInfo = $contactService->getContactInfo();
$socialMediaLinks = $contactService->getSocialMediaLinks();

// DEBUG: Gelen verileri kontrol et
echo "<!-- DEBUG: Contact Info Keys: " . implode(', ', array_keys($contactInfo)) . " -->";
echo "<!-- DEBUG: Social Links Count: " . count($socialMediaLinks) . " -->";

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
        // ContactService ile formu işle
        $formData = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ];
        
        $result = $contactService->submitContactForm($formData);
        if($result) {
            $message_sent = true;
        }
    }
}
?>

<!-- İletişim Banner -->
<section class="bg-primary text-white py-12 text-center mb-12">
    <div class="max-w-7xl mx-auto px-5">
        <h1 class="text-5xl font-bold mb-4"><?php echo htmlspecialchars($contactInfo['banner']['title']); ?></h1>
        <p class="text-xl"><?php echo htmlspecialchars($contactInfo['banner']['subtitle']); ?></p>
    </div>
</section>

<!-- İletişim Bilgileri ve Form -->
<section class="py-0 pb-12">
    <div class="max-w-7xl mx-auto px-5">
        <?php if($message_sent): ?>
            <div class="max-w-2xl mx-auto text-center py-12 px-8 bg-light rounded-lg shadow-lg">
                <i class="fas fa-check-circle text-6xl text-green-500 mb-5"></i>
                <h3 class="text-3xl font-bold mb-4 text-secondary"><?php echo htmlspecialchars($contactInfo['form']['success_title']); ?></h3>
                <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($contactInfo['form']['success_message']); ?></p>
                <a href="contact.php" class="inline-block px-8 py-3 bg-primary text-white rounded-full font-semibold hover:bg-pink-600 transition-colors duration-300"><?php echo htmlspecialchars($contactInfo['form']['success_button']); ?></a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-10 mb-12">
                <div class="flex-1">
                    <h2 class="text-3xl font-bold mb-5 text-secondary"><?php echo htmlspecialchars($contactInfo['contact']['title']); ?></h2>
                    <p class="text-gray-600 mb-8 leading-relaxed"><?php echo htmlspecialchars($contactInfo['contact']['description']); ?></p>
                    
                    <div class="flex gap-5 mb-6">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2 text-secondary">Adres</h3>
                            <p class="text-gray-600"><?php echo $contactInfo['contact']['address']; ?></p>
                        </div>
                    </div>
                    
                    <div class="flex gap-5 mb-6">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2 text-secondary">Telefon</h3>
                            <p class="text-gray-600 mb-1"><?php echo htmlspecialchars($contactInfo['contact']['phone1']); ?></p>
                            <p class="text-gray-600"><?php echo htmlspecialchars($contactInfo['contact']['phone2']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex gap-5 mb-6">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2 text-secondary">E-posta</h3>
                            <p class="text-gray-600 mb-1"><?php echo htmlspecialchars($contactInfo['contact']['email1']); ?></p>
                            <p class="text-gray-600"><?php echo htmlspecialchars($contactInfo['contact']['email2']); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex gap-5 mb-8">
                        <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2 text-secondary">Çalışma Saatleri</h3>
                            <p class="text-gray-600 mb-1"><?php echo htmlspecialchars($contactInfo['contact']['working_hours1']); ?></p>
                            <p class="text-gray-600"><?php echo htmlspecialchars($contactInfo['contact']['working_hours2']); ?></p>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4 text-secondary">Sosyal Medya</h3>
                        <div class="flex gap-4">
                            <?php foreach($socialMediaLinks as $link): ?>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" class="w-10 h-10 bg-light text-secondary rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-300">
                                    <i class="<?php echo htmlspecialchars($link['icon_class']); ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 bg-white p-8 rounded-lg shadow-lg">
                    <h2 class="text-3xl font-bold mb-5 text-secondary"><?php echo htmlspecialchars($contactInfo['form']['title']); ?></h2>
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
<section class="py-12 bg-light pb-20">
    <div class="max-w-7xl mx-auto px-5">
        <h2 class="text-3xl font-bold mb-8 text-secondary text-center"><?php echo htmlspecialchars($contactInfo['map']['title']); ?></h2>
        <div class="rounded-lg overflow-hidden shadow-lg">
            <iframe src="<?php echo htmlspecialchars($contactInfo['map']['embed_code']); ?>" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
