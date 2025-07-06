<?php
// Footer bilgilerini getir
require_once $_SERVER['DOCUMENT_ROOT'] . '/services/ContactService.php';
$contactService = new ContactService();
$footer_info = $contactService->getFooterInfo();
?>
    </main>
    <footer class="bg-dark text-white pt-12 pb-5">
        <div class="max-w-7xl mx-auto px-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold"><?= htmlspecialchars($footer_info['footer']['site_title'] ?? 'Schön') ?><span class="text-primary">.</span></h2>
                    <p class="text-gray-300 leading-relaxed"><?= htmlspecialchars($footer_info['footer']['site_description'] ?? '') ?></p>
                    <div class="flex space-x-4 mt-5">
                        <?php foreach($footer_info['social_links'] as $social): ?>
                            <a href="<?= htmlspecialchars($social['url']) ?>" class="inline-block text-white text-xl hover:text-primary transition-colors duration-300">
                                <i class="<?= htmlspecialchars($social['icon_class']) ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold mb-5">Hızlı Erişim</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= htmlspecialchars($footer_info['links']['home_url'] ?? '/index.php') ?>" class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['home_text'] ?? 'Ana Sayfa') ?></a></li>
                        <li><a href="<?= htmlspecialchars($footer_info['links']['products_url'] ?? '/products.php') ?>" class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['products_text'] ?? 'Ürünler') ?></a></li>
                        <li><a href="<?= htmlspecialchars($footer_info['links']['about_url'] ?? '/about.php') ?>" class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['about_text'] ?? 'Hakkımızda') ?></a></li>
                        <li><a href="<?= htmlspecialchars($footer_info['links']['blog_url'] ?? '/blog.php') ?>" class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['blog_text'] ?? 'Blog') ?></a></li>
                        <li><a href="<?= htmlspecialchars($footer_info['links']['contact_url'] ?? '/contact.php') ?>" class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['contact_text'] ?? 'İletişim') ?></a></li>
                    </ul>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold mb-5">İletişim</h3>
                    <div class="space-y-3">
                        <p class="text-gray-300"><i class="fas fa-map-marker-alt text-primary mr-2"></i> <?= $footer_info['contact']['address'] ?></p>
                        <p class="text-gray-300"><i class="fas fa-phone text-primary mr-2"></i> <?= htmlspecialchars($footer_info['contact']['phone']) ?></p>
                        <p class="text-gray-300"><i class="fas fa-envelope text-primary mr-2"></i> <?= htmlspecialchars($footer_info['contact']['email']) ?></p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-600 pt-5">
                <p class="text-center text-gray-400"><?= htmlspecialchars($footer_info['footer']['copyright_text'] ?? '© 2025 Schön. Tüm hakları saklıdır.') ?></p>
            </div>
        </div>
    </footer>
    <script src="/assets/js/script.js"></script>
</body>
</html>
