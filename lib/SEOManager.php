<?php

class SEOManager
{
    private static $instance = null;
    private $meta_tags = [];
    private $structured_data = [];
    private $default_config = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->setDefaultConfig();
    }

    private function setDefaultConfig()
    {
        $this->default_config = [
            'site_name' => 'Bandland Shoes',
            'site_description' => 'Türkiye\'nin en kaliteli ayakkabı markası. Modern tasarım, konfor ve dayanıklılığı bir araya getiren ayakkabı koleksiyonları.',
            'site_url' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            'default_image' => '/assets/images/og-default.jpg',
            'twitter_username' => '@bandlandshoes',
            'facebook_app_id' => '',
            'language' => 'tr',
            'locale' => 'tr_TR',
            'author' => 'Bandland Shoes',
            'robots' => 'index, follow',
            'googlebot' => 'index, follow',
            'revisit_after' => '1 days'
        ];
    }

    public function setTitle($title, $append_site_name = true)
    {
        if ($append_site_name && $title !== $this->default_config['site_name']) {
            $title = $title . ' | ' . $this->default_config['site_name'];
        }

        $this->meta_tags['title'] = htmlspecialchars($title);
        return $this;
    }

    public function setDescription($description)
    {
        $description = strip_tags($description);
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }

        $this->meta_tags['description'] = htmlspecialchars($description);
        return $this;
    }

    public function setKeywords($keywords)
    {
        if (is_array($keywords)) {
            $keywords = implode(', ', $keywords);
        }

        $this->meta_tags['keywords'] = htmlspecialchars($keywords);
        return $this;
    }

    public function setCanonical($url)
    {
        $this->meta_tags['canonical'] = htmlspecialchars($url);
        return $this;
    }

    public function setRobots($robots)
    {
        $this->meta_tags['robots'] = htmlspecialchars($robots);
        return $this;
    }

    public function setOpenGraph($data)
    {
        $defaults = [
            'title' => $this->meta_tags['title'] ?? $this->default_config['site_name'],
            'description' => $this->meta_tags['description'] ?? $this->default_config['site_description'],
            'image' => $this->default_config['default_image'],
            'url' => $this->getCurrentURL(),
            'type' => 'website',
            'site_name' => $this->default_config['site_name'],
            'locale' => $this->default_config['locale']
        ];

        $this->meta_tags['og'] = array_merge($defaults, $data);
        return $this;
    }

    public function setTwitterCard($data)
    {
        $defaults = [
            'card' => 'summary_large_image',
            'title' => $this->meta_tags['title'] ?? $this->default_config['site_name'],
            'description' => $this->meta_tags['description'] ?? $this->default_config['site_description'],
            'image' => $this->default_config['default_image'],
            'site' => $this->default_config['twitter_username']
        ];

        $this->meta_tags['twitter'] = array_merge($defaults, $data);
        return $this;
    }

    public function addStructuredData($type, $data)
    {
        $structured_data = [
            '@context' => 'https://schema.org',
            '@type' => $type
        ];

        $this->structured_data[] = array_merge($structured_data, $data);
        return $this;
    }

    public function addOrganizationSchema($data = [])
    {
        $defaults = [
            'name' => $this->default_config['site_name'],
            'url' => $this->default_config['site_url'],
            'logo' => $this->default_config['site_url'] . '/assets/images/logo.png',
            'sameAs' => [
                'https://www.facebook.com/bandlandshoes',
                'https://www.instagram.com/bandlandshoes',
                'https://twitter.com/bandlandshoes'
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+90-555-123-4567',
                'contactType' => 'customer service',
                'availableLanguage' => 'Turkish'
            ]
        ];

        return $this->addStructuredData('Organization', array_merge($defaults, $data));
    }

    public function addProductSchema($product_data)
    {
        $defaults = [
            '@type' => 'Product',
            'brand' => [
                '@type' => 'Brand',
                'name' => $this->default_config['site_name']
            ],
            'offers' => [
                '@type' => 'Offer',
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $this->default_config['site_name']
                ]
            ]
        ];

        return $this->addStructuredData('Product', array_merge($defaults, $product_data));
    }

    public function addArticleSchema($article_data)
    {
        $defaults = [
            '@type' => 'Article',
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->default_config['site_name'],
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->default_config['site_url'] . '/assets/images/logo.png'
                ]
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $this->default_config['author']
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->getCurrentURL()
            ]
        ];

        return $this->addStructuredData('Article', array_merge($defaults, $article_data));
    }

    public function addBreadcrumbSchema($breadcrumbs)
    {
        $list_items = [];
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $list_items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url']
            ];
        }

        return $this->addStructuredData('BreadcrumbList', [
            'itemListElement' => $list_items
        ]);
    }

    public function addLocalBusinessSchema($business_data = [])
    {
        $defaults = [
            '@type' => 'LocalBusiness',
            'name' => $this->default_config['site_name'],
            'image' => $this->default_config['site_url'] . '/assets/images/store.jpg',
            'url' => $this->default_config['site_url'],
            'telephone' => '+90-555-123-4567',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Bağdat Caddesi No:123',
                'addressLocality' => 'Kadıköy',
                'addressRegion' => 'İstanbul',
                'postalCode' => '34710',
                'addressCountry' => 'TR'
            ],
            'openingHoursSpecification' => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday'
                ],
                'opens' => '10:00',
                'closes' => '20:00'
            ]
        ];

        return $this->addStructuredData('LocalBusiness', array_merge($defaults, $business_data));
    }

    private function getCurrentURL()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        return $protocol . '://' . $host . $uri;
    }

    public function renderMetaTags()
    {
        $html = "\n";

        if (isset($this->meta_tags['title'])) {
            $html .= '<title>' . $this->meta_tags['title'] . "</title>\n";
        }

        if (isset($this->meta_tags['description'])) {
            $html .= '<meta name="description" content="' . $this->meta_tags['description'] . '">' . "\n";
        }

        if (isset($this->meta_tags['keywords'])) {
            $html .= '<meta name="keywords" content="' . $this->meta_tags['keywords'] . '">' . "\n";
        }

        $robots = $this->meta_tags['robots'] ?? $this->default_config['robots'];
        $html .= '<meta name="robots" content="' . $robots . '">' . "\n";
        $html .= '<meta name="googlebot" content="' . $this->default_config['googlebot'] . '">' . "\n";

        $html .= '<meta name="language" content="' . $this->default_config['language'] . '">' . "\n";
        $html .= '<meta property="og:locale" content="' . $this->default_config['locale'] . '">' . "\n";

        $html .= '<meta name="author" content="' . $this->default_config['author'] . '">' . "\n";

        $html .= '<meta name="revisit-after" content="' . $this->default_config['revisit_after'] . '">' . "\n";

        if (isset($this->meta_tags['canonical'])) {
            $html .= '<link rel="canonical" href="' . $this->meta_tags['canonical'] . '">' . "\n";
        }

        if (isset($this->meta_tags['og'])) {
            foreach ($this->meta_tags['og'] as $property => $content) {
                $html .= '<meta property="og:' . $property . '" content="' . htmlspecialchars($content) . '">' . "\n";
            }
        }

        if (isset($this->meta_tags['twitter'])) {
            foreach ($this->meta_tags['twitter'] as $name => $content) {
                $html .= '<meta name="twitter:' . $name . '" content="' . htmlspecialchars($content) . '">' . "\n";
            }
        }

        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        $html .= '<meta charset="UTF-8">' . "\n";
        $html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">' . "\n";

        return $html;
    }

    public function renderStructuredData()
    {
        if (empty($this->structured_data)) {
            return '';
        }

        $html = "\n" . '<script type="application/ld+json">' . "\n";

        if (count($this->structured_data) === 1) {
            $html .= json_encode($this->structured_data[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            $html .= json_encode($this->structured_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $html .= "\n" . '</script>' . "\n";

        return $html;
    }

    public function generateSitemap($urls = [])
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $xml .= $this->addSitemapURL($this->default_config['site_url'], date('Y-m-d'), 'daily', '1.0');

        $default_pages = [
            '/products.php' => ['weekly', '0.9'],
            '/about.php' => ['monthly', '0.7'],
            '/blog.php' => ['weekly', '0.8'],
            '/contact.php' => ['monthly', '0.6']
        ];

        foreach ($default_pages as $page => $settings) {
            $xml .= $this->addSitemapURL(
                $this->default_config['site_url'] . $page,
                date('Y-m-d'),
                $settings[0],
                $settings[1]
            );
        }

        foreach ($urls as $url_data) {
            $xml .= $this->addSitemapURL(
                $url_data['url'],
                $url_data['lastmod'] ?? date('Y-m-d'),
                $url_data['changefreq'] ?? 'weekly',
                $url_data['priority'] ?? '0.5'
            );
        }

        $xml .= '</urlset>';

        return $xml;
    }

    private function addSitemapURL($url, $lastmod, $changefreq, $priority)
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
        $xml .= "    <changefreq>" . $changefreq . "</changefreq>\n";
        $xml .= "    <priority>" . $priority . "</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }

    public function generateRobotsTxt($custom_rules = [])
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /config/\n";
        $robots .= "Disallow: /lib/\n";
        $robots .= "Disallow: /services/\n";
        $robots .= "Disallow: /includes/\n";
        $robots .= "\n";

        foreach ($custom_rules as $rule) {
            $robots .= $rule . "\n";
        }

        $robots .= "\n";
        $robots .= "Sitemap: " . $this->default_config['site_url'] . "/sitemap.xml\n";

        return $robots;
    }

    public function generateSEOReport($content, $url)
    {
        $report = [
            'score' => 0,
            'issues' => [],
            'suggestions' => [],
            'good_practices' => []
        ];

        if (isset($this->meta_tags['title'])) {
            $title_length = strlen($this->meta_tags['title']);
            if ($title_length >= 30 && $title_length <= 60) {
                $report['good_practices'][] = 'Başlık uzunluğu optimal (30-60 karakter)';
                $report['score'] += 15;
            } elseif ($title_length < 30) {
                $report['issues'][] = 'Başlık çok kısa (30 karakterden az)';
            } else {
                $report['issues'][] = 'Başlık çok uzun (60 karakterden fazla)';
            }
        } else {
            $report['issues'][] = 'Başlık etiketi eksik';
        }

        if (isset($this->meta_tags['description'])) {
            $desc_length = strlen($this->meta_tags['description']);
            if ($desc_length >= 120 && $desc_length <= 160) {
                $report['good_practices'][] = 'Meta description uzunluğu optimal (120-160 karakter)';
                $report['score'] += 15;
            } elseif ($desc_length < 120) {
                $report['suggestions'][] = 'Meta description daha uzun olabilir';
                $report['score'] += 5;
            } else {
                $report['issues'][] = 'Meta description çok uzun (160 karakterden fazla)';
            }
        } else {
            $report['issues'][] = 'Meta description eksik';
        }

        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $content, $matches)) {
            $h1_count = preg_match_all('/<h1[^>]*>/i', $content);
            if ($h1_count === 1) {
                $report['good_practices'][] = 'Tek H1 etiketi mevcut';
                $report['score'] += 10;
            } else {
                $report['issues'][] = 'Birden fazla H1 etiketi bulundu';
            }
        } else {
            $report['issues'][] = 'H1 etiketi eksik';
        }

        $images = preg_match_all('/<img[^>]*>/i', $content, $img_matches);
        $images_with_alt = preg_match_all('/<img[^>]*alt=["\'][^"\']*["\'][^>]*>/i', $content);

        if ($images > 0) {
            $alt_ratio = ($images_with_alt / $images) * 100;
            if ($alt_ratio >= 90) {
                $report['good_practices'][] = 'Resimlerin %' . round($alt_ratio) . '\'si alt text\'e sahip';
                $report['score'] += 10;
            } elseif ($alt_ratio >= 70) {
                $report['suggestions'][] = 'Bazı resimlerde alt text eksik';
                $report['score'] += 5;
            } else {
                $report['issues'][] = 'Çoğu resimde alt text eksik';
            }
        }

        $internal_links = preg_match_all('/<a[^>]*href=["\'][^"\']*["\'][^>]*>/i', $content);
        if ($internal_links >= 3) {
            $report['good_practices'][] = 'Yeterli internal link mevcut';
            $report['score'] += 10;
        } else {
            $report['suggestions'][] = 'Daha fazla internal link eklenebilir';
        }

        $text_content = strip_tags($content);
        $word_count = str_word_count($text_content);

        if ($word_count >= 300) {
            $report['good_practices'][] = 'İçerik uzunluğu yeterli (' . $word_count . ' kelime)';
            $report['score'] += 15;
        } elseif ($word_count >= 150) {
            $report['suggestions'][] = 'İçerik biraz daha uzun olabilir';
            $report['score'] += 8;
        } else {
            $report['issues'][] = 'İçerik çok kısa (' . $word_count . ' kelime)';
        }

        if (!empty($this->structured_data)) {
            $report['good_practices'][] = 'Structured data (Schema.org) mevcut';
            $report['score'] += 15;
        } else {
            $report['suggestions'][] = 'Structured data eklenebilir';
        }

        if (isset($this->meta_tags['og'])) {
            $report['good_practices'][] = 'OpenGraph meta tagları mevcut';
            $report['score'] += 10;
        } else {
            $report['suggestions'][] = 'Social media için OpenGraph tagları eklenebilir';
        }

        $report['score'] = min(100, $report['score']);

        if ($report['score'] >= 80) {
            $report['assessment'] = 'Mükemmel';
        } elseif ($report['score'] >= 60) {
            $report['assessment'] = 'İyi';
        } elseif ($report['score'] >= 40) {
            $report['assessment'] = 'Orta';
        } else {
            $report['assessment'] = 'Geliştirilmeli';
        }

        return $report;
    }
}

function seo()
{
    return SEOManager::getInstance();
}
