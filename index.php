<?php 
include 'includes/header.php'; 
require_once 'services/SliderService.php';

$sliderService = new SliderService();
$slides = $sliderService->getActiveSliders();
?>

<!-- Hero Slider -->
<section class="relative h-[600px] overflow-hidden">
    <?php if (!empty($slides)): ?>
        <?php foreach ($slides as $index => $slide): ?>
            <div 
                class="slide absolute inset-0 w-full h-full transition-opacity duration-1000 <?php echo $index === 0 ? 'opacity-100' : 'opacity-0'; ?>"
                style="background-color: <?php echo htmlspecialchars($slide['bg_color']); ?>;">
                
                <?php if (!empty($slide['image_url'])): ?>
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($slide['image_url']); ?>');"></div>
                    <div class="absolute inset-0 bg-black opacity-40"></div>
                <?php endif; ?>

                <div class="relative z-10 h-full flex items-center justify-center text-center text-white">
                    <div class="slide-content max-w-4xl px-5">
                        <h2 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-5"><?php echo htmlspecialchars($slide['title']); ?></h2>
                        <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto"><?php echo htmlspecialchars($slide['description']); ?></p>
                        <a href="<?php echo htmlspecialchars($slide['button_url']); ?>" class="inline-block px-8 py-3 bg-brand text-secondary rounded-full font-semibold uppercase text-sm tracking-wide hover:bg-opacity-80 transition-all duration-300">
                            <?php echo htmlspecialchars($slide['button_text']); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="slider-dots absolute bottom-8 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20">
            <?php foreach ($slides as $index => $slide): ?>
                <span class="dot w-3 h-3 bg-white <?php echo $index === 0 ? 'bg-opacity-100' : 'bg-opacity-50'; ?> rounded-full cursor-pointer"></span>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="flex items-center justify-center h-full">
            <p class="text-xl text-gray-500">Slider bulunamadı.</p>
        </div>
    <?php endif; ?>
</section>

<!-- Sezonluk Koleksiyonlar -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-display font-bold mb-3 text-secondary">Sezonluk Koleksiyonlar</h2>
            <p class="text-gray-600">Her mevsime özel, stilinizi tamamlayacak tasarımlar.</p>
        </div>

        <!-- İlkbahar/Yaz Koleksiyonu -->
        <div class="flex flex-col md:flex-row items-center gap-12 mb-20">
            <div class="flex-1 text-center">
                <h3 class="text-3xl font-display font-bold mb-4 text-secondary">İlkbahar/Yaz 2025</h3>
                <p class="text-gray-600 leading-relaxed mb-6 max-w-md mx-auto">
                    Doğanın uyanışından ilham alan, canlı renkler ve nefes alan materyallerle tasarlanmış koleksiyonumuzla adımlarınıza enerji katın.
                </p>
                <a href="/products.php?category=ilkbahar-yaz" class="inline-block px-8 py-3 bg-brand text-secondary rounded-full font-semibold hover:bg-opacity-80 transition-all duration-300">
                    Koleksiyonu Gör
                </a>
            </div>
            <div class="flex-1">
                <img src="https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=800&q=80" alt="İlkbahar/Yaz 2025 Koleksiyonu" class="w-full md:w-4/5 mx-auto rounded-lg shadow-xl">
            </div>
        </div>

        <!-- Sonbahar/Kış Koleksiyonu -->
        <div class="flex flex-col md:flex-row-reverse items-center gap-12">
            <div class="flex-1 text-center">
                <h3 class="text-3xl font-display font-bold mb-4 text-secondary">Sonbahar/Kış 2025</h3>
                <p class="text-gray-600 leading-relaxed mb-6 max-w-md mx-auto">
                    Şehrin ritmine ayak uyduran, su geçirmez ve sıcak tutan botlarımızla soğuk havalarda bile stilinizden ödün vermeyin.
                </p>
                <a href="/products.php?category=sonbahar-kis" class="inline-block px-8 py-3 bg-brand text-secondary rounded-full font-semibold hover:bg-opacity-80 transition-all duration-300">
                    Koleksiyonu Gör
                </a>
            </div>
            <div class="flex-1">
                <img src="https://images.unsplash.com/photo-1603145733316-74623f43c351?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=800&q=80" alt="Sonbahar/Kış 2025 Koleksiyonu" class="w-full md:w-4/5 mx-auto rounded-lg shadow-xl">
            </div>
        </div>
    </div>
</section>

<!-- Hakkımızda Kısa Bölüm -->
<?php
require_once 'services/AboutService.php';
$aboutService = new AboutService();
$homeAbout = $aboutService->getHomePageAboutSection();
?>
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1">
                <img src="<?php echo htmlspecialchars($homeAbout['story_image_url'] ?? ''); ?>" alt="Mağaza" class="w-2/3 mx-auto rounded-lg shadow-lg">
            </div>
            <div class="flex-1 text-center lg:text-left">
                <h2 class="text-4xl font-bold mb-5 text-secondary"><?php echo htmlspecialchars($homeAbout['story_content_title'] ?? 'Schön Hakkında'); ?></h2>
                <p class="mb-8 text-gray-600 leading-relaxed"><?php echo htmlspecialchars($homeAbout['story_content_homepage'] ?? ''); ?></p>
                <a href="/about.php" class="inline-block px-8 py-3 bg-brand text-secondary rounded-full font-semibold hover:bg-opacity-80 transition-all duration-300">Daha Fazla Bilgi</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
