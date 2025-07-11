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
    <?php
    // SEO structured data'yı render et
    if (isset($seo)) {
        echo $seo->renderStructuredData();
    }
    ?>
    
    <!-- Loading Spinner -->
    <div class="page-loading">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Smooth Page Transition JavaScript -->
    <script>
        // Page transition system
        class PageTransition {
            constructor() {
                this.init();
            }
            
            init() {
                // Page transitions devre dışı mı kontrol et
                if (document.body.hasAttribute('data-disable-page-transitions')) {
                    return; // Page transition sistemini başlatma
                }
                
                // Sayfa yüklendiğinde fade-in efekti
                this.fadeInPage();
                
                // İç linklere event listener ekle
                this.attachLinkHandlers();
                
                // Mobile menu toggle
                this.initMobileMenu();
                
                // Back/forward button handling
                this.handleBrowserNavigation();
            }
            
            fadeInPage() {
                // Sayfa tamamen yüklendiğinde
                window.addEventListener('load', () => {
                    document.body.classList.add('loaded');
                });
                
                // DOM hazır olduğunda (daha hızlı)
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => {
                        // Kısa bir gecikme ile daha smooth görünüm
                        setTimeout(() => {
                            document.body.classList.add('loaded');
                        }, 100);
                    });
                } else {
                    // Zaten yüklenmişse hemen göster
                    document.body.classList.add('loaded');
                }
            }
            
            attachLinkHandlers() {
                // Tüm iç linkleri seç (aynı domain içerisinde)
                const internalLinks = document.querySelectorAll('a[href]');
                
                internalLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    
                    // ÖNCE data-no-transition attributeini kontrol et (en yüksek öncelik)
                    if (link.hasAttribute('data-no-transition')) {
                        return; // Bu link için transition yapma
                    }
                    
                    // Logout, profile, blog, contact, products, index ve about linklerini kesinlikle hariç tut
                    if (href && (href.includes('logout.php') || href.includes('profile.php') || href.includes('about.php') ||
                        href.includes('blog.php') || href.includes('contact.php') || href.includes('products.php') ||
                        href.includes('index.php') || href === '/' || href === '/index.php')) {
                        return; // Bu linkler için transition yapma
                    }
                    
                    // İç link kontrolü (external linkler hariç)
                    if (this.isInternalLink(href)) {
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.navigateToPage(href);
                        });
                    }
                });
            }
            
            isInternalLink(href) {
                // Boş, anchor (#), external (http), mailto, tel linklerini hariç tut
                if (!href ||
                    href.startsWith('#') ||
                    href.startsWith('http') ||
                    href.startsWith('mailto:') ||
                    href.startsWith('tel:') ||
                    href.startsWith('javascript:')) {
                    return false;
                }
                
                // Profil sayfası, about, blog, contact, products, index ve logout linklerini KESİNLİKLE hariç tut (session koruması)
                if (href.includes('profile.php') ||
                    href.includes('logout.php') ||
                    href.includes('login.php') ||
                    href.includes('about.php') ||
                    href.includes('blog.php') ||
                    href.includes('contact.php') ||
                    href.includes('products.php') ||
                    href.includes('index.php') ||
                    href === '/' ||
                    href === '/index.php') {
                    return false;
                }
                
                return true;
            }
            
            navigateToPage(url) {
                // Loading spinner göster
                const loader = document.querySelector('.page-loading');
                loader.classList.add('active');
                
                // Fade out efekti
                document.body.classList.add('fade-out');
                
                // Kısa animasyon sonrası yönlendir
                setTimeout(() => {
                    window.location.href = url;
                }, 400); // CSS transition süresi ile aynı
            }
            
            initMobileMenu() {
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');
                
                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', () => {
                        const isHidden = mobileMenu.classList.contains('hidden');
                        
                        if (isHidden) {
                            // Menüyü göster
                            mobileMenu.classList.remove('hidden');
                            setTimeout(() => {
                                mobileMenu.classList.add('show');
                            }, 10); // Kısa gecikme ile smooth animation
                        } else {
                            // Menüyü gizle
                            mobileMenu.classList.remove('show');
                            setTimeout(() => {
                                mobileMenu.classList.add('hidden');
                            }, 300); // Animation süresi
                        }
                    });
                    
                    // Menü dışına tıklanınca kapat
                    document.addEventListener('click', (e) => {
                        if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                            if (!mobileMenu.classList.contains('hidden')) {
                                mobileMenu.classList.remove('show');
                                setTimeout(() => {
                                    mobileMenu.classList.add('hidden');
                                }, 300);
                            }
                        }
                    });
                }
            }
            
            handleBrowserNavigation() {
                // Browser back/forward button handling
                window.addEventListener('popstate', () => {
                    // Loading spinner göster
                    const loader = document.querySelector('.page-loading');
                    loader.classList.add('active');
                    
                    // Sayfa yeniden yüklenecek, smooth geçiş için hazırla
                    document.body.classList.add('fade-out');
                });
                
                // Page visibility API ile sekme değişimini handle et
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        // Sekme aktif olduğunda smooth gösterim
                        document.body.classList.add('loaded');
                    }
                });
            }
        }
        
        // Smooth scrolling for anchor links
        function initSmoothScrolling() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }
        
        // Form submissions için smooth handling
        function initFormTransitions() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Form submit edilirken loading göster
                    const loader = document.querySelector('.page-loading');
                    if (loader) {
                        setTimeout(() => {
                            loader.classList.add('active');
                        }, 100);
                    }
                });
            });
        }
        
        // Initialize everything
        document.addEventListener('DOMContentLoaded', () => {
            new PageTransition();
            initSmoothScrolling();
            initFormTransitions();
        });
        
        // Performance optimization - preload next pages
        function preloadPages() {
            const links = document.querySelectorAll('nav a[href]');
            links.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    const href = this.getAttribute('href');
                    if (href && !href.startsWith('#') && !href.startsWith('http')) {
                        // DNS prefetch
                        const linkElement = document.createElement('link');
                        linkElement.rel = 'prefetch';
                        linkElement.href = href;
                        document.head.appendChild(linkElement);
                    }
                });
            });
        }
        
        // Initialize preloading after page load
        window.addEventListener('load', preloadPages);
    </script>
    
    <script src="/assets/js/script.js"></script>
    
    <!-- Modal Bileşeni -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/views/components/modal.php'; ?>
</body>
</html>
