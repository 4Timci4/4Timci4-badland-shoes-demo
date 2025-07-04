<?php include 'includes/header.php'; ?>

<?php
// Blog ID'sini al
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Örnek blog yazıları (Normalde veritabanından çekilir)
$all_blog_posts = [
    [
        'id' => 1, 
        'title' => '2025 Yaz Sezonunun Gözde Ayakkabıları', 
        'excerpt' => 'Bu yaz hem rahatlığı hem de şıklığı bir araya getiren en trend ayakkabı modellerini sizler için derledik.', 
        'content' => '<p>2025 yaz sezonu, ayakkabı dünyasında yenilikçi tasarımlar ve cesur renklerin öne çıktığı bir dönem olarak karşımıza çıkıyor. Bu sezon ayakkabı trendlerinde minimalist tasarımlardan gösterişli modellere kadar geniş bir yelpaze sunuluyor.</p><h3>1. Platform Sandalet ve Terlikler</h3><p>90\'ların nostaljik havası, platform sandalet ve terliklerin geri dönüşüyle devam ediyor. Özellikle pastel tonlardaki platform sandaletler ve kalın tabanlı terlikler, 2025 yazının öne çıkan parçaları arasında yer alıyor.</p>',
        'image' => 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80', 
        'date' => '15 Haziran 2025', 
        'category' => 'Trendler',
        'tags' => ['Yaz Modası', 'Trendler', 'Sandalet']
    ],
    [
        'id' => 2, 
        'title' => 'Ayak Sağlığınız İçin Doğru Ayakkabı Nasıl Seçilir?', 
        'excerpt' => 'Gün boyu konfor ve sağlık için ayakkabı seçerken dikkat etmeniz gereken önemli noktaları inceliyoruz.', 
        'content' => '<p>Ayakkabı seçimi, sadece estetik bir konu değil, aynı zamanda sağlığımızı da doğrudan etkileyen önemli bir faktördür. Yanlış ayakkabı seçimi, ayak ağrılarından başlayarak sırt ve bel problemlerine kadar uzanan birçok sağlık sorununa yol açabilir.</p><h3>Ayak Sağlığı ve Ayakkabı İlişkisi</h3><p>Ayaklarımız vücudumuzu taşıyan en önemli yapılardır ve günde ortalama 8.000-10.000 adım attığımızı düşünürsek, doğru ayakkabı seçiminin önemi daha iyi anlaşılır.</p>',
        'image' => 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80', 
        'date' => '02 Haziran 2025', 
        'category' => 'Sağlık',
        'tags' => ['Ayak Sağlığı', 'Doğru Ayakkabı', 'Sağlık']
    ],
    // Diğer yazılar da benzer şekilde 'content' alanı eklenerek devam edebilir...
];

// Tüm blog yazılarını bir dizide toplayalım (ID'leri anahtar olarak kullanarak)
$blog_posts_by_id = [];
foreach ($all_blog_posts as $post) {
    $blog_posts_by_id[$post['id']] = $post;
}

// Eğer blog yazısı bulunamazsa geri yönlendir
if (!isset($blog_posts_by_id[$blog_id])) {
    header("Location: blog.php");
    exit;
}

// Blog yazısı bilgilerini al
$post = $blog_posts_by_id[$blog_id];
?>

<!-- Blog Detay -->
<section class="blog-detail">
    <div class="container">
        <article>
            <header class="blog-header">
                <a href="/blog.php?category=<?php echo urlencode($post['category']); ?>" class="category"><?php echo $post['category']; ?></a>
                <h1><?php echo $post['title']; ?></h1>
                <div class="blog-meta">
                    <span class="date"><i class="far fa-calendar-alt"></i> <?php echo $post['date']; ?></span>
                </div>
            </header>
            
            <div class="blog-featured-image">
                <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>">
            </div>
            
            <div class="blog-content">
                <?php echo $post['content']; ?>
            </div>
            
            <footer class="blog-footer">
                <?php if (isset($post['tags']) && !empty($post['tags'])): ?>
                <div class="blog-tags">
                    <h3>Etiketler</h3>
                    <div class="tags">
                        <?php foreach($post['tags'] as $tag): ?>
                            <a href="/blog.php?tag=<?php echo urlencode($tag); ?>" class="tag"><?php echo $tag; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="blog-share">
                    <h3>Bu Yazıyı Paylaş</h3>
                    <div class="social-share">
                        <a href="#" class="facebook" title="Facebook'ta Paylaş"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="twitter" title="Twitter'da Paylaş"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="linkedin" title="LinkedIn'de Paylaş"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="whatsapp" title="WhatsApp'ta Paylaş"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="email" title="E-posta ile Gönder"><i class="far fa-envelope"></i></a>
                    </div>
                </div>
            </footer>
        </article>
        
        <?php
        // Benzer yazıları bul
        $related_posts = [];
        foreach($all_blog_posts as $related_post) {
            if ($related_post['id'] != $post['id'] && $related_post['category'] == $post['category']) {
                $related_posts[] = $related_post;
            }
            if (count($related_posts) >= 2) break; // En fazla 2 benzer yazı göster
        }
        
        if (!empty($related_posts)):
        ?>
        <aside class="related-posts">
            <h2>Benzer Yazılar</h2>
            <div class="related-grid">
                <?php foreach($related_posts as $related): ?>
                    <a href="/blog-detail.php?id=<?php echo $related['id']; ?>" class="related-card">
                        <div class="related-image">
                            <img src="<?php echo $related['image']; ?>" alt="<?php echo $related['title']; ?>">
                        </div>
                        <div class="related-content">
                            <h3><?php echo $related['title']; ?></h3>
                            <div class="date"><?php echo $related['date']; ?></div>
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
