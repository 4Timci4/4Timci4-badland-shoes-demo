<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/services/ContactService.php';
$contactService = new ContactService();
$footer_info = $contactService->getFooterInfo();
?>
</main>
<footer class="bg-dark text-white pt-12 pb-5">
    <div class="max-w-8xl mx-auto px-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div class="space-y-4">
                <h2 class="text-2xl font-semibold">
                    <?= htmlspecialchars($footer_info['footer']['site_title'] ?? 'Schön') ?><span
                        class="text-primary">.</span></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?= htmlspecialchars($footer_info['footer']['site_description'] ?? '') ?></p>
                <div class="flex space-x-4 mt-5">
                    <?php foreach ($footer_info['social_links'] as $social): ?>
                        <a href="<?= htmlspecialchars($social['url']) ?>"
                            class="inline-block text-white text-xl hover:text-primary transition-colors duration-300">
                            <i class="<?= htmlspecialchars($social['icon_class']) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-5">Hızlı Erişim</h3>
                <ul class="space-y-2">
                    <li><a href="<?= htmlspecialchars($footer_info['links']['home_url'] ?? '/index.php') ?>"
                            class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['home_text'] ?? 'Ana Sayfa') ?></a>
                    </li>
                    <li><a href="<?= htmlspecialchars($footer_info['links']['products_url'] ?? '/products.php') ?>"
                            class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['products_text'] ?? 'Ürünler') ?></a>
                    </li>
                    <li><a href="<?= htmlspecialchars($footer_info['links']['about_url'] ?? '/about.php') ?>"
                            class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['about_text'] ?? 'Hakkımızda') ?></a>
                    </li>
                    <li><a href="<?= htmlspecialchars($footer_info['links']['blog_url'] ?? '/blog.php') ?>"
                            class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['blog_text'] ?? 'Blog') ?></a>
                    </li>
                    <li><a href="<?= htmlspecialchars($footer_info['links']['contact_url'] ?? '/contact.php') ?>"
                            class="text-gray-300 hover:text-primary transition-colors duration-300"><?= htmlspecialchars($footer_info['links']['contact_text'] ?? 'İletişim') ?></a>
                    </li>
                </ul>
            </div>
            <div class="space-y-4">
                <h3 class="text-lg font-semibold mb-5">İletişim</h3>
                <div class="space-y-3">
                    <p class="text-gray-300"><i class="fas fa-map-marker-alt text-primary mr-2"></i>
                        <?= $footer_info['contact']['address'] ?></p>
                    <p class="text-gray-300"><i class="fas fa-phone text-primary mr-2"></i>
                        <?= htmlspecialchars($footer_info['contact']['phone']) ?></p>
                    <p class="text-gray-300"><i class="fas fa-envelope text-primary mr-2"></i>
                        <?= htmlspecialchars($footer_info['contact']['email']) ?></p>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-600 pt-5">
            <p class="text-center text-gray-400">
                <?= htmlspecialchars($footer_info['footer']['copyright_text'] ?? '© 2025 Schön. Tüm hakları saklıdır.') ?>
            </p>
        </div>
    </div>
</footer>
<?php

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

    class PageTransition {
        constructor() {
            this.init();
        }

        init() {

            if (document.body.hasAttribute('data-disable-page-transitions')) {
                return;
            }


            this.fadeInPage();


            this.attachLinkHandlers();


            this.initMobileMenu();


            this.handleBrowserNavigation();
        }

        fadeInPage() {

            window.addEventListener('load', () => {
                document.body.classList.add('loaded');
            });


            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {

                    setTimeout(() => {
                        document.body.classList.add('loaded');
                    }, 100);
                });
            } else {

                document.body.classList.add('loaded');
            }
        }

        attachLinkHandlers() {

            const internalLinks = document.querySelectorAll('a[href]');

            internalLinks.forEach(link => {
                const href = link.getAttribute('href');


                if (link.hasAttribute('data-no-transition')) {
                    return;
                }


                if (href && (href.includes('logout.php') || href.includes('user/profile.php') || href.includes('about.php') ||
                    href.includes('blog.php') || href.includes('contact.php') || href.includes('products.php') ||
                    href.includes('index.php') || href === '/' || href === '/index.php')) {
                    return;
                }


                if (this.isInternalLink(href)) {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.navigateToPage(href);
                    });
                }
            });
        }

        isInternalLink(href) {

            if (!href ||
                href.startsWith('#') ||
                href.startsWith('http') ||
                href.startsWith('mailto:') ||
                href.startsWith('tel:') ||
                href.startsWith('javascript:')) {
                return false;
            }


            if (href.includes('user/profile.php') ||
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

            const loader = document.querySelector('.page-loading');
            loader.classList.add('active');


            document.body.classList.add('fade-out');


            setTimeout(() => {
                window.location.href = url;
            }, 400);
        }

        initMobileMenu() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', () => {
                    const isHidden = mobileMenu.classList.contains('hidden');

                    if (isHidden) {

                        mobileMenu.classList.remove('hidden');
                        setTimeout(() => {
                            mobileMenu.classList.add('show');
                        }, 10);
                    } else {

                        mobileMenu.classList.remove('show');
                        setTimeout(() => {
                            mobileMenu.classList.add('hidden');
                        }, 300);
                    }
                });


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

            window.addEventListener('popstate', () => {

                const loader = document.querySelector('.page-loading');
                loader.classList.add('active');


                document.body.classList.add('fade-out');
            });


            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {

                    document.body.classList.add('loaded');
                }
            });
        }
    }


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


    function initFormTransitions() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {

                const loader = document.querySelector('.page-loading');
                if (loader) {
                    setTimeout(() => {
                        loader.classList.add('active');
                    }, 100);
                }
            });
        });
    }


    document.addEventListener('DOMContentLoaded', () => {
        new PageTransition();
        initSmoothScrolling();
        initFormTransitions();
    });


    function preloadPages() {
        const links = document.querySelectorAll('nav a[href]');
        links.forEach(link => {
            link.addEventListener('mouseenter', function () {
                const href = this.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('http')) {

                    const linkElement = document.createElement('link');
                    linkElement.rel = 'prefetch';
                    linkElement.href = href;
                    document.head.appendChild(linkElement);
                }
            });
        });
    }


    window.addEventListener('load', preloadPages);
</script>

<script src="/assets/js/script.js"></script>

<!-- Modal Bileşeni -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/views/components/modal.php'; ?>
</body>

</html>