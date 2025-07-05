<?php include 'includes/header.php'; ?>

<?php
require_once __DIR__ . '/config/database.php';

$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($blog_id === 0) {
    header("Location: /blog.php");
    exit;
}

$post = blogService()->get_post_by_id($blog_id);

if (!$post) {
    header("Location: /blog.php"); // Blog ana sayfasına yönlendir
    exit;
}
$post = (array) $post; // Obje ise diziye çevir
?>

<!-- Blog Detay -->
<section class="blog-detail">
    <div class="container">
        <article>
            <header class="blog-header">
                <a href="/blog.php?category=<?php echo urlencode($post['category']); ?>" class="category"><?php echo htmlspecialchars($post['category']); ?></a>
                <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="blog-meta">
                    <span class="date"><i class="far fa-calendar-alt"></i> <?php echo date('d F Y', strtotime($post['created_at'])); ?></span>
                </div>
            </header>
            
            <div class="blog-featured-image">
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            </div>
            
            <div class="blog-content">
                <?php echo $post['content']; ?>
            </div>
            
            <footer class="blog-footer">
                <?php 
                // Tags alanı array veya string olabilir
                $tags = [];
                if (!empty($post['tags'])) {
                    if (is_array($post['tags'])) {
                        // Eğer tags array ise direkt kullan
                        $tags = $post['tags'];
                    } elseif (is_string($post['tags'])) {
                        // Eğer tags string ise ('{tag1,tag2}' formatında) parse et
                        $tags = str_replace(['{', '}'], '', $post['tags']);
                        $tags = explode(',', $tags);
                    }
                }

                if (!empty($tags)): 
                ?>
                <div class="blog-tags">
                    <h3>Etiketler</h3>
                    <div class="tags">
                        <?php foreach($tags as $tag): ?>
                            <a href="/blog.php?tag=<?php echo urlencode(trim($tag)); ?>" class="tag"><?php echo htmlspecialchars(trim($tag)); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="blog-share">
                    <h3>Bu Yazıyı Paylaş</h3>
                    <div class="social-share">
                        <?php
                        // Mevcut sayfa URL'ini al
                        $current_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                        $share_title = htmlspecialchars($post['title']);
                        $share_text = htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 100) . '...');
                        ?>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($current_url); ?>" target="_blank" class="facebook" title="Facebook'ta Paylaş"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($current_url); ?>&text=<?php echo urlencode($share_title); ?>" target="_blank" class="twitter" title="Twitter'da Paylaş"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($current_url); ?>" target="_blank" class="linkedin" title="LinkedIn'de Paylaş"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://wa.me/?text=<?php echo urlencode($share_title . ' - ' . $current_url); ?>" target="_blank" class="whatsapp" title="WhatsApp'ta Paylaş"><i class="fab fa-whatsapp"></i></a>
                        <a href="mailto:?subject=<?php echo urlencode($share_title); ?>&body=<?php echo urlencode($share_text . "\n\n" . $current_url); ?>" class="email" title="E-posta ile Gönder"><i class="far fa-envelope"></i></a>
                    </div>
                </div>
            </footer>
        </article>
        
        <?php
        // Benzer yazıları bul
        $related_posts = blogService()->get_related_posts($post['id'], $post['category']);
        
        if (!empty($related_posts)):
        ?>
        <aside class="related-posts">
            <h2>Benzer Yazılar</h2>
            <div class="related-grid">
                <?php foreach($related_posts as $related): 
                    $related = (array) $related;
                ?>
                    <a href="/blog-detail.php?id=<?php echo $related['id']; ?>" class="related-card">
                        <div class="related-image">
                            <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                        </div>
                        <div class="related-content">
                            <h3><?php echo htmlspecialchars($related['title']); ?></h3>
                            <div class="date"><?php echo date('d F Y', strtotime($related['created_at'])); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
        <?php endif; ?>
    </div>
</section>

<link rel="stylesheet" href="/assets/css/blog-detail.css?v=<?php echo time(); ?>">

<?php include 'includes/footer.php'; ?>
