<?php
require_once 'config/database.php';
require_once 'services/BlogService.php';

// Blog ID'sini al
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($blog_id <= 0) {
    header("Location: blog.php");
    exit;
}

// Blog servisini başlat
$blogService = new BlogService();

// Blog yazısını getir
$post = $blogService->get_post_by_id($blog_id);

// Eğer blog yazısı bulunamazsa blog sayfasına yönlendir
if (!$post) {
    header("Location: blog.php");
    exit;
}

// Benzer yazıları getir
$related_posts = $blogService->get_related_posts($blog_id, $post['category'], 3);

// Tags'ları düzenle (PostgreSQL array formatından PHP array'e)
$tags = [];
if (!empty($post['tags'])) {
    if (is_string($post['tags'])) {
        // PostgreSQL array formatından çevir: {tag1,tag2} -> [tag1, tag2]
        $tags_string = str_replace(['{', '}'], ['', ''], $post['tags']);
        $tags = array_map('trim', explode(',', $tags_string));
    } elseif (is_array($post['tags'])) {
        $tags = $post['tags'];
    }
}

// SEO Manager'ı dahil et ve blog detay için ayarla
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/SEOManager.php';
$seo = seo();

$current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Blog yazısı için SEO ayarları
$seo->setTitle($post['title'])
    ->setDescription($post['excerpt'] ?? substr(strip_tags($post['content'] ?? ''), 0, 160))
    ->setKeywords(array_merge($tags, ['blog', 'ayakkabı', $post['category']]))
    ->setCanonical($current_url)
    ->setOpenGraph([
        'type' => 'article',
        'image' => $post['image_url'] ?? '/assets/images/og-default.jpg',
        'article:published_time' => date('c', strtotime($post['created_at'])),
        'article:author' => 'Bandland Shoes',
        'article:section' => $post['category']
    ])
    ->setTwitterCard([
        'image' => $post['image_url'] ?? '/assets/images/og-default.jpg'
    ]);

// Article schema markup
$article_schema = [
    'headline' => $post['title'],
    'description' => $post['excerpt'] ?? substr(strip_tags($post['content'] ?? ''), 0, 200),
    'image' => [
        '@type' => 'ImageObject',
        'url' => $post['image_url'] ?? '/assets/images/og-default.jpg'
    ],
    'datePublished' => date('c', strtotime($post['created_at'])),
    'dateModified' => date('c', strtotime($post['created_at'])),
    'wordCount' => str_word_count(strip_tags($post['content'] ?? '')),
    'keywords' => implode(', ', $tags)
];

$seo->addArticleSchema($article_schema);

// Breadcrumb schema
$breadcrumbs = [
    ['name' => 'Ana Sayfa', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/'],
    ['name' => 'Blog', 'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/blog.php'],
    ['name' => $post['title'], 'url' => $current_url]
];
$seo->addBreadcrumbSchema($breadcrumbs);

include 'includes/header.php';
?>

<!-- Breadcrumb -->
<section class="bg-gray-50 py-4 border-b">
    <div class="max-w-7xl mx-auto px-5">
        <nav class="text-sm">
            <ol class="flex items-center space-x-2 text-gray-500">
                <li><a href="/" class="hover:text-primary transition-colors">Ana Sayfa</a></li>
                <li class="text-gray-400">></li>
                <li><a href="/blog.php" class="hover:text-primary transition-colors">Blog</a></li>
                <li class="text-gray-400">></li>
                <li class="text-secondary font-medium"><?php echo htmlspecialchars($post['title']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Blog Detail -->
<article class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-5">
        
        <!-- Blog Header -->
        <header class="mb-12">
            <!-- Kategori ve Tarih -->
            <div class="flex items-center space-x-4 text-sm text-gray-600 mb-6">
                <span class="blog-category-badge inline-block px-3 py-1 rounded-full font-medium">
                    <?php echo htmlspecialchars($post['category']); ?>
                </span>
                <span class="flex items-center">
                    <i class="far fa-calendar-alt mr-2"></i>
                    <?php echo date('d F Y', strtotime($post['created_at'])); ?>
                </span>
                <span class="flex items-center">
                    <i class="far fa-clock mr-2"></i>
                    <?php 
                    // Okuma süresi hesapla (ortalama 200 kelime/dakika)
                    $word_count = str_word_count(strip_tags($post['content'] ?? ''));
                    $reading_time = max(1, ceil($word_count / 200));
                    echo $reading_time . ' dk okuma';
                    ?>
                </span>
            </div>
            
            <!-- Başlık -->
            <h1 class="text-4xl md:text-5xl font-bold text-secondary leading-tight mb-6">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
            
            <!-- Özet -->
            <?php if (!empty($post['excerpt'])): ?>
                <p class="text-xl text-gray-600 leading-relaxed mb-8">
                    <?php echo htmlspecialchars($post['excerpt']); ?>
                </p>
            <?php endif; ?>
            
            <!-- Sosyal Medya Paylaşım -->
            <div class="flex items-center justify-between border-t border-b border-gray-200 py-6">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-700">Paylaş:</span>
                    <div class="flex space-x-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" 
                           class="social-share-button bg-blue-600 text-white hover:bg-blue-700">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                           target="_blank" 
                           class="social-share-button bg-sky-500 text-white hover:bg-sky-600">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" 
                           class="social-share-button bg-blue-700 text-white hover:bg-blue-800">
                            <i class="fab fa-linkedin-in text-sm"></i>
                        </a>
                        <button onclick="copyToClipboard('<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>')" 
                                class="social-share-button bg-gray-600 text-white hover:bg-gray-700">
                            <i class="fas fa-link text-sm"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Etiketler -->
                <?php if (!empty($tags)): ?>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Etiketler:</span>
                        <div class="flex flex-wrap gap-1">
                            <?php foreach($tags as $tag): ?>
                                <a href="/blog.php?tag=<?php echo urlencode(trim($tag)); ?>" 
                                   class="blog-tag inline-block px-2 py-1 text-xs rounded">
                                    #<?php echo htmlspecialchars(trim($tag)); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>
        
        <!-- Featured Image -->
        <?php if (!empty($post['image_url'])): ?>
            <div class="blog-featured-image mb-12">
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($post['title']); ?>" 
                     class="w-full h-96 object-cover">
            </div>
        <?php endif; ?>
        
        <!-- Blog Content -->
        <div class="prose prose-lg max-w-none">
            <?php 
            // İçeriği güvenli bir şekilde render et
            $content = $post['content'] ?? '';
            
            // Basit HTML temizleme (gerçek uygulamada HTMLPurifier kullanılmalı)
            $allowed_tags = '<p><h1><h2><h3><h4><h5><h6><strong><em><ul><ol><li><a><img><br><blockquote>';
            $content = strip_tags($content, $allowed_tags);
            
            // Linkleri güvenli hale getir
            $content = preg_replace_callback(
                '/<a\s+href="([^"]*)"[^>]*>/i',
                function($matches) {
                    $href = htmlspecialchars($matches[1]);
                    return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">';
                },
                $content
            );
            
            // Resimleri responsive yap
            $content = preg_replace(
                '/<img([^>]*)>/i',
                '<img$1 class="w-full h-auto rounded-lg shadow-md my-6">',
                $content
            );
            
            echo $content;
            ?>
        </div>
        
        <!-- Article Footer -->
        <footer class="mt-16 pt-8 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Son güncelleme: <?php echo date('d F Y', strtotime($post['created_at'])); ?>
                </div>
                
                <!-- Tekrar Paylaş -->
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-700">Bu yazıyı beğendiniz mi?</span>
                    <div class="flex space-x-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" 
                           class="text-blue-600 hover:text-blue-700">
                            <i class="fab fa-facebook text-lg"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                           target="_blank" 
                           class="text-sky-500 hover:text-sky-600">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                           target="_blank" 
                           class="text-blue-700 hover:text-blue-800">
                            <i class="fab fa-linkedin text-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</article>

<!-- İlgili Yazılar -->
<?php if (!empty($related_posts)): ?>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-secondary mb-4">İlgili Yazılar</h2>
            <p class="text-gray-600">Benzer konulardaki diğer yazılarımız</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($related_posts as $related_post): ?>
                <article class="related-post-card">
                    <a href="/blog-detail.php?id=<?php echo $related_post['id']; ?>" class="block">
                        <div class="post-image">
                            <img src="<?php echo htmlspecialchars($related_post['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($related_post['title']); ?>" 
                                 class="w-full h-48 object-cover">
                        </div>
                    </a>
                    <div class="p-6">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-3">
                            <span class="inline-block px-2 py-1 bg-primary/10 text-primary rounded-full text-xs">
                                <?php echo htmlspecialchars($related_post['category']); ?>
                            </span>
                            <span><?php echo date('d.m.Y', strtotime($related_post['created_at'])); ?></span>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary mb-3 line-clamp-2">
                            <a href="/blog-detail.php?id=<?php echo $related_post['id']; ?>" 
                               class="hover:text-primary transition-colors">
                                <?php echo htmlspecialchars($related_post['title']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                            <?php echo htmlspecialchars($related_post['excerpt']); ?>
                        </p>
                        <a href="/blog-detail.php?id=<?php echo $related_post['id']; ?>" 
                           class="inline-flex items-center text-primary hover:text-primary/80 font-medium text-sm">
                            Devamını Oku
                            <i class="fas fa-arrow-right ml-2 text-xs"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="/blog.php" 
               class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                <i class="fas fa-list mr-2"></i>
                Tüm Blog Yazıları
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Blog Detail CSS -->
<link rel="stylesheet" href="/assets/css/blog-detail.css">

<!-- JavaScript -->
<script>
// Link kopyalama fonksiyonu
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Optimize edilmiş bildirim göster
        const notification = document.createElement('div');
        notification.innerHTML = '<i class="fas fa-check mr-2"></i>Link kopyalandı!';
        notification.className = 'notification fixed top-4 right-4 text-white px-4 py-2 rounded-lg shadow-lg z-50 flex items-center';
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }).catch(function() {
        alert('Link kopyalanamadı. Lütfen manuel olarak kopyalayın.');
    });
}

// Optimize edilmiş scroll ve progress bar
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
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
    
    // Enhanced reading progress bar
    const progressBar = document.createElement('div');
    progressBar.className = 'reading-progress';
    progressBar.style.width = '0%';
    document.body.appendChild(progressBar);
    
    // Throttled scroll event for better performance
    let isScrolling = false;
    window.addEventListener('scroll', () => {
        if (!isScrolling) {
            requestAnimationFrame(() => {
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                progressBar.style.width = Math.min(100, Math.max(0, scrolled)) + '%';
                isScrolling = false;
            });
            isScrolling = true;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
