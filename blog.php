<?php include 'includes/header.php'; ?>

<!-- Blog Banner -->
<section class="page-banner">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl md:text-5xl font-bold font-display">Blog</h1>
        <p class="mt-2 text-lg md:text-xl text-gray-200">Ayakkabı dünyası hakkında en güncel bilgiler, trendler ve ipuçları.</p>
    </div>
</section>

<!-- Blog Listesi -->
<section class="blog-section py-12">
    <div class="container mx-auto px-4">
        <div class="blog-grid">
            <?php
            // Sayfalama için temel değişkenler
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 6; // Sayfa başına gösterilecek yazı sayısı

            // Örnek blog yazıları (Normalde veritabanından çekilir)
            $all_blog_posts = [
                [
                    'id' => 1, 
                    'title' => '2025 Yaz Sezonunun Gözde Ayakkabıları', 
                    'excerpt' => 'Bu yaz hem rahatlığı hem de şıklığı bir araya getiren en trend ayakkabı modellerini sizler için derledik.', 
                    'content' => 'Yaz aylarının gelmesiyle birlikte gardıroplar yenileniyor, peki ayakkabı dolabınız yaza hazır mı? 2025 yaz sezonunda sandaletlerden espadrillere, minimalist terliklerden renkli spor ayakkabılara kadar birçok seçenek öne çıkıyor. Özellikle doğal materyaller ve canlı renklerin hakim olduğu bu sezonda, hem konforlu hem de stil sahibi olmanız mümkün. Bu yazımızda, plajdan ofise, günlük hayattan özel davetlere kadar her ortama uyum sağlayacak ayakkabı modellerini ve kombin önerilerini bulabilirsiniz.',
                    'image' => 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80', 
                    'date' => '15 Haziran 2025', 
                    'category' => 'Trendler'
                ],
                [
                    'id' => 2, 
                    'title' => 'Ayak Sağlığınız İçin Doğru Ayakkabı Nasıl Seçilir?', 
                    'excerpt' => 'Gün boyu konfor ve sağlık için ayakkabı seçerken dikkat etmeniz gereken önemli noktaları inceliyoruz.', 
                    'content' => 'Ayakkabı seçimi, sadece dış görünüşümüzü değil, aynı zamanda genel sağlığımızı da doğrudan etkileyen önemli bir karardır. Yanlış ayakkabı seçimi, ayak ağrılarından duruş bozukluklarına kadar birçok soruna yol açabilir. Peki, doğru ayakkabıyı nasıl seçmeliyiz? Bu yazıda, ayak tipinize uygun modeli bulmaktan, doğru numarayı seçmeye, malzeme kalitesinden taban yapısına kadar dikkat etmeniz gereken tüm detayları uzman görüşleriyle ele alıyoruz. Adımlarınızı daha sağlıklı ve konforlu hale getirmek için rehberimizi okumaya devam edin.',
                    'image' => 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80', 
                    'date' => '02 Haziran 2025', 
                    'category' => 'Sağlık'
                ],
                // Diğer yazılar da benzer şekilde 'content' alanı eklenerek devam edebilir...
            ];

            $total = count($all_blog_posts);
            $pages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $blog_posts = array_slice($all_blog_posts, $offset, $perPage);

            foreach($blog_posts as $post): 
            ?>
                <div class="blog-card">
                    <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="block">
                        <div class="blog-image">
                            <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>">
                            <div class="category"><?php echo $post['category']; ?></div>
                        </div>
                    </a>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="date"><i class="far fa-calendar-alt"></i> <?php echo $post['date']; ?></span>
                        </div>
                        <h2 class="font-display"><a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="hover:text-primary transition-colors duration-300"><?php echo $post['title']; ?></a></h2>
                        <p class="text-gray-600"><?php echo $post['excerpt']; ?></p>
                        <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="read-more mt-4">
                            Devamını Oku <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Sayfalama -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="prev">
                    <i class="fas fa-chevron-left"></i>
                    <span>Önceki</span>
                </a>
            <?php else: ?>
                <span class="prev disabled">
                    <i class="fas fa-chevron-left"></i>
                    <span>Önceki</span>
                </span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="next">
                    <span>Sonraki</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="next disabled">
                    <span>Sonraki</span>
                    <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
</section>

<link rel="stylesheet" href="/assets/css/blog.css?v=<?php echo time(); ?>">

<?php include 'includes/footer.php'; ?>
