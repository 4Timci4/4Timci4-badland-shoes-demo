<?php

class SEOManager
{
    private static $instance = null;
    private $db;
    private $meta_tags = [];
    private $structured_data = [];
    private $all_settings = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        require_once __DIR__ . '/DatabaseFactory.php';
        $this->db = database();
        $this->loadAllSettings();
    }

    private function loadAllSettings()
    {
        $defaults = [
            'site_name' => 'Bandland Shoes',
            'site_description' => 'Türkiye\'nin en kaliteli ayakkabı markası. Modern tasarım, konfor ve dayanıklılığı bir araya getiren ayakkabı koleksiyonları.',
            'site_url' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            'default_image' => '/assets/images/og-default.jpg',
            'twitter_username' => '@bandlandshoes',
            'facebook_app_id' => '',
            'language' => 'tr',
            'locale' => 'tr_TR',
            'author' => 'Bandland Shoes',
            'robots' => 'index, follow'
        ];

        if (!$this->db) {
            $this->all_settings = $defaults;
            return;
        }

        try {
            $site_settings_raw = $this->db->select('site_settings', [], '*');
            $seo_settings_raw = $this->db->select('seo_settings', [], '*');

            $site_settings = [];
            foreach ($site_settings_raw as $setting) {
                $site_settings[$setting['setting_key']] = $setting['setting_value'];
            }

            $seo_settings = [];
            foreach ($seo_settings_raw as $setting) {
                $seo_settings[$setting['setting_key']] = $setting['setting_value'];
            }

            $this->all_settings = array_merge($defaults, $site_settings, $seo_settings);
        } catch (Exception $e) {
            error_log('Failed to load settings from DB: ' . $e->getMessage());
            $this->all_settings = $defaults;
        }
    }

    public function setTitle($title, $append_site_name = true)
    {
        if ($append_site_name && $title !== $this->getSetting('site_name')) {
            $title = $title . ' | ' . $this->getSetting('site_name');
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
            'title' => $this->meta_tags['title'] ?? $this->getSetting('site_name'),
            'description' => $this->meta_tags['description'] ?? $this->getSetting('site_description'),
            'image' => $this->getSetting('default_image'),
            'url' => $this->getCurrentURL(),
            'type' => 'website',
            'site_name' => $this->getSetting('site_name'),
            'locale' => $this->getSetting('og_locale', 'tr_TR')
        ];

        $this->meta_tags['og'] = array_merge($defaults, $data);
        return $this;
    }

    public function setTwitterCard($data)
    {
        $defaults = [
            'card' => 'summary_large_image',
            'title' => $this->meta_tags['title'] ?? $this->getSetting('site_name'),
            'description' => $this->meta_tags['description'] ?? $this->getSetting('site_description'),
            'image' => $this->getSetting('default_image'),
            'site' => $this->getSetting('twitter_username')
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

    public function addOrganizationSchema()
    {
        if ($this->getSetting('schema_enabled', 'false') !== 'true') {
            return $this;
        }

        $schema = [
            '@type' => $this->getSetting('schema_organization_type', 'Organization'),
            'name' => $this->getSetting('schema_organization_name', $this->getSetting('site_name')),
            'url' => $this->getSetting('schema_organization_url', $this->getSetting('site_url')),
            'logo' => $this->getSetting('schema_organization_logo', $this->getSetting('site_url') . '/assets/images/logo.png'),
            'description' => $this->getSetting('schema_organization_description', $this->getSetting('site_description')),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $this->getSetting('schema_organization_phone'),
                'contactType' => 'customer service',
                'email' => $this->getSetting('schema_organization_email'),
                'availableLanguage' => 'Turkish'
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'TR',
                'addressLocality' => 'Istanbul',
                'streetAddress' => $this->getSetting('schema_organization_address')
            ]
        ];

        $this->addStructuredData($schema['@type'], $schema);

        // Add Website Schema with search action
        $this->addStructuredData('WebSite', [
            'url' => $this->getSetting('site_url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $this->getSetting('site_url') . '/products.php?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ]);

        return $this;
    }

    public function addProductSchema($product_data)
    {
        $defaults = [
            '@type' => 'Product',
            'brand' => [
                '@type' => 'Brand',
                'name' => $this->getSetting('site_name')
            ],
            'offers' => [
                '@type' => 'Offer',
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $this->getSetting('site_name')
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
                'name' => $this->getSetting('site_name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->getSetting('site_url') . '/assets/images/logo.png'
                ]
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $this->getSetting('author')
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

    private function getSetting($key, $default = '')
    {
        return $this->all_settings[$key] ?? $default;
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
        if (!isset($this->meta_tags['og'])) {
            $this->setOpenGraph([]);
        }
        if (!isset($this->meta_tags['twitter'])) {
            $this->setTwitterCard([]);
        }

        $html = "\n";

        $title = $this->meta_tags['title'] ?? $this->getSetting('default_title', $this->getSetting('site_name'));
        if ($title) {
            $html .= '<title>' . htmlspecialchars($title) . "</title>\n";
        }

        $description = $this->meta_tags['description'] ?? $this->getSetting('default_description', $this->getSetting('site_description'));
        if ($description) {
            $html .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . "\n";
        }

        $robots = $this->meta_tags['robots'] ?? $this->getSetting('robots', 'index, follow');
        if ($robots) {
            $html .= '<meta name="robots" content="' . htmlspecialchars($robots) . '">' . "\n";
        }

        $html .= '<meta name="language" content="' . htmlspecialchars($this->getSetting('language', 'tr')) . '">' . "\n";
        $html .= '<meta property="og:locale" content="' . htmlspecialchars($this->getSetting('og_locale', 'tr_TR')) . '">' . "\n";

        $author = $this->meta_tags['author'] ?? $this->getSetting('author', 'Bandland Shoes');
        if ($author) {
            $html .= '<meta name="author" content="' . htmlspecialchars($author) . '">' . "\n";
        }

        if ($this->getSetting('canonical_enabled', 'true') === 'true' && isset($this->meta_tags['canonical'])) {
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

        $xml .= $this->addSitemapURL($this->getSetting('site_url'), date('Y-m-d'), 'daily', '1.0');

        $default_pages = [
            '/products.php' => ['weekly', '0.9'],
            '/about.php' => ['monthly', '0.7'],
            '/blog.php' => ['weekly', '0.8'],
            '/contact.php' => ['monthly', '0.6']
        ];

        foreach ($default_pages as $page => $settings) {
            $xml .= $this->addSitemapURL(
                $this->getSetting('site_url') . $page,
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
        $robots .= "Sitemap: " . $this->getSetting('site_url') . "/sitemap.xml\n";

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
