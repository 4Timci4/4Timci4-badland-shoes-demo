<?php
// Session yönetimini etkinleştir
require_once 'services/AuthService.php';
$authService = new AuthService();

require_once 'config/database.php';
require_once 'services/ContactService.php';
require_once 'lib/SecurityManager.php';

$contactService = new ContactService();
$security = security();
$contact_info = $contactService->getContactInfo();

// Form gönderildi mi kontrol et
$form_submitted = false;
$form_success = false;
$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $form_submitted = true;
    
    // Rate limiting kontrolü
    if (!$security->checkRateLimit('contact_form', 5, 3600)) { // 5 istek/saat
        $form_errors[] = 'Çok fazla istek gönderdiniz. Lütfen bir saat sonra tekrar deneyin.';
    } else {
        // CSRF token kontrolü
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!$security->verifyCSRFToken($csrf_token, 'contact_form')) {
            $form_errors[] = 'Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.';
            $security->logSecurityEvent('csrf_failure', 'Contact form CSRF token verification failed');
        } else {
            // Suspicious activity kontrolü
            $suspicious_alerts = $security->detectSuspiciousActivity($_POST);
            if (!empty($suspicious_alerts)) {
                $form_errors[] = 'Güvenlik nedeniyle form gönderilemiyor. Lütfen girdiğiniz bilgileri kontrol edin.';
                foreach ($suspicious_alerts as $alert) {
                    $security->logSecurityEvent('suspicious_input', 
                        'Suspicious activity detected in contact form', $alert);
                }
            } else {
                // Form verilerini güvenli şekilde al ve doğrula
                $name = $security->sanitizeInput($_POST['name'] ?? '', 'string');
                $email = $security->sanitizeInput($_POST['email'] ?? '', 'email');
                $subject = $security->sanitizeInput($_POST['subject'] ?? '', 'string');
                $message = $security->sanitizeInput($_POST['message'] ?? '', 'string');
                
                // Validation kuralları
                $validation_rules = [
                    'name' => [
                        'required' => true,
                        'min_length' => 2,
                        'max_length' => 100
                    ],
                    'email' => [
                        'required' => true,
                        'email' => true,
                        'max_length' => 255
                    ],
                    'subject' => [
                        'required' => true,
                        'min_length' => 3,
                        'max_length' => 200
                    ],
                    'message' => [
                        'required' => true,
                        'min_length' => 10,
                        'max_length' => 2000
                    ]
                ];
                
                $input_data = [
                    'name' => $name,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message
                ];
                
                $validation_errors = $security->validateInput($input_data, $validation_rules);
                
                if (!empty($validation_errors)) {
                    foreach ($validation_errors as $field_errors) {
                        $form_errors = array_merge($form_errors, $field_errors);
                    }
                } else {
                    // Form verisini gönder
                    $form_data = [
                        'name' => $name,
                        'email' => $email,
                        'subject' => $subject,
                        'message' => $message
                    ];
                    
                    if ($contactService->submitContactForm($form_data)) {
                        $form_success = true;
                        $security->logSecurityEvent('contact_form_success', 'Contact form submitted successfully', [
                            'email' => $email,
                            'subject' => $subject
                        ]);
                    } else {
                        $form_errors[] = 'Mesaj gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
                    }
                }
            }
        }
    }
}

include 'includes/header.php'; 
?>

<!-- Page Banner -->
<section class="bg-gradient-to-r from-primary to-purple-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-5 text-center">
        <h1 class="text-5xl font-bold mb-4"><?php echo htmlspecialchars($contact_info['banner']['title'] ?? 'İletişim'); ?></h1>
        <p class="text-xl text-white/90"><?php echo htmlspecialchars($contact_info['banner']['subtitle'] ?? 'Bizimle iletişime geçin'); ?></p>
    </div>
</section>

<!-- Breadcrumb -->
<section class="bg-gray-50 py-4 border-b">
    <div class="max-w-7xl mx-auto px-5">
        <nav class="text-sm">
            <ol class="flex items-center space-x-2 text-gray-500">
                <li><a href="/" class="hover:text-primary transition-colors">Ana Sayfa</a></li>
                <li class="text-gray-400">></li>
                <li class="text-secondary font-medium">İletişim</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Contact Content -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- İletişim Bilgileri -->
            <div class="space-y-8">
                <div>
                    <h2 class="text-3xl font-bold text-secondary mb-4"><?php echo htmlspecialchars($contact_info['contact']['title'] ?? 'İletişim Bilgileri'); ?></h2>
                    <p class="text-gray-600 leading-relaxed mb-8"><?php echo htmlspecialchars($contact_info['contact']['description'] ?? ''); ?></p>
                </div>
                
                <div class="space-y-6">
                    <!-- Adres -->
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-secondary mb-1">Adres</h3>
                            <p class="text-gray-600"><?php echo $contact_info['contact']['address'] ?? ''; ?></p>
                        </div>
                    </div>
                    
                    <!-- Telefon -->
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-secondary mb-1">Telefon</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($contact_info['contact']['phone1'] ?? ''); ?></p>
                            <?php if (!empty($contact_info['contact']['phone2'])): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($contact_info['contact']['phone2']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- E-posta -->
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-secondary mb-1">E-posta</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($contact_info['contact']['email1'] ?? ''); ?></p>
                            <?php if (!empty($contact_info['contact']['email2'])): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($contact_info['contact']['email2']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Çalışma Saatleri -->
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-secondary mb-1">Çalışma Saatleri</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($contact_info['contact']['working_hours1'] ?? ''); ?></p>
                            <?php if (!empty($contact_info['contact']['working_hours2'])): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($contact_info['contact']['working_hours2']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sosyal Medya -->
                <div class="pt-8">
                    <h3 class="font-semibold text-secondary mb-4">Sosyal Medya</h3>
                    <div class="flex space-x-4">
                        <?php 
                        $social_links = $contactService->getSocialMediaLinks();
                        foreach($social_links as $social): 
                        ?>
                            <a href="<?php echo htmlspecialchars($social['url']); ?>" 
                               target="_blank" 
                               class="w-10 h-10 bg-gray-100 hover:bg-primary hover:text-white rounded-full flex items-center justify-center transition-all duration-300">
                                <i class="<?php echo htmlspecialchars($social['icon_class']); ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- İletişim Formu -->
            <div class="bg-gray-50 p-8 rounded-xl">
                <h2 class="text-3xl font-bold text-secondary mb-6"><?php echo htmlspecialchars($contact_info['form']['title'] ?? 'Bize Mesaj Gönderin'); ?></h2>
                
                <?php if ($form_submitted && $form_success): ?>
                    <!-- Başarı Mesajı -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            <div>
                                <h3 class="font-semibold text-green-800"><?php echo htmlspecialchars($contact_info['form']['success_title'] ?? 'Mesajınız Başarıyla Gönderildi!'); ?></h3>
                                <p class="text-green-700 text-sm"><?php echo htmlspecialchars($contact_info['form']['success_message'] ?? 'En kısa sürede size geri dönüş yapacağız.'); ?></p>
                            </div>
                        </div>
                        <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <?php echo htmlspecialchars($contact_info['form']['success_button'] ?? 'Yeni Mesaj Gönder'); ?>
                        </button>
                    </div>
                <?php elseif ($form_submitted && !empty($form_errors)): ?>
                    <!-- Hata Mesajları -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                            <div>
                                <h3 class="font-semibold text-red-800 mb-2">Lütfen aşağıdaki hataları düzeltin:</h3>
                                <ul class="text-red-700 text-sm space-y-1">
                                    <?php foreach($form_errors as $error): ?>
                                        <li>• <?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!$form_success): ?>
                    <form method="POST" class="space-y-6">
                        <?php echo $security->getCSRFTokenHTML('contact_form'); ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">İsim Soyisim *</label>
                                <input type="text" id="name" name="name" required 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-posta *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Konu *</label>
                            <input type="text" id="subject" name="subject" required 
                                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Mesaj *</label>
                            <textarea id="message" name="message" rows="6" required 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="submit_contact" 
                                class="w-full bg-primary text-white py-3 px-6 rounded-lg font-semibold hover:bg-primary/90 transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Mesaj Gönder
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Harita -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-secondary mb-4"><?php echo htmlspecialchars($contact_info['map']['title'] ?? 'Bizi Ziyaret Edin'); ?></h2>
            <p class="text-gray-600">Mağazamıza kolayca ulaşabilirsiniz.</p>
        </div>
        
        <div class="rounded-xl overflow-hidden shadow-lg">
            <iframe 
                src="<?php echo htmlspecialchars($contact_info['map']['embed_code'] ?? ''); ?>" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
