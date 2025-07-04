<?php include 'includes/header.php'; ?>

<!-- Blog Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Blog</h1>
        <p>Ayakkabı dünyası hakkında en güncel bilgiler</p>
    </div>
</section>

<!-- Blog Listesi -->
<section class="blog-section">
    <div class="container">
        <div class="blog-grid">
            <?php
            // Blog yazıları (gerçek uygulamada veritabanından çekilir)
            $blog_posts = [
                [
                    'id' => 1,
                    'title' => '2025 Yaz Ayakkabı Trendleri',
                    'excerpt' => 'Bu yazımızda 2025 yazında öne çıkan ayakkabı trendlerini ve sezonun en popüler modellerini inceliyoruz.',
                    'image' => 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NXx8c2hvZXMlMjBzdW1tZXJ8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
                    'date' => '15 Haziran 2025',
                    'author' => 'Zeynep Kaya',
                    'category' => 'Trendler'
                ],
                [
                    'id' => 2,
                    'title' => 'Doğru Ayakkabı Seçiminin Önemi',
                    'excerpt' => 'Ayak sağlığınız için doğru ayakkabı seçiminin neden önemli olduğunu ve nelere dikkat etmeniz gerektiğini anlatıyoruz.',
                    'image' => 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8M3x8c2hvZXMlMjBoZWFsdGh8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
                    'date' => '02 Haziran 2025',
                    'author' => 'Dr. Ahmet Öz',
                    'category' => 'Sağlık'
                ],
                [
                    'id' => 3,
                    'title' => 'Ayakkabı Bakımı İçin 10 İpucu',
                    'excerpt' => 'Ayakkabılarınızın ömrünü uzatmak ve her zaman güzel görünmelerini sağlamak için uygulayabileceğiniz bakım ipuçları.',
                    'image' => 'https://images.unsplash.com/photo-1553341640-4ba55ddee5c4?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTF8fHNob2UlMjBjYXJlfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60',
                    'date' => '25 Mayıs 2025',
                    'author' => 'Mehmet Öztürk',
                    'category' => 'Bakım'
                ],
                [
                    'id' => 4,
                    'title' => 'Spor Ayakkabı Seçim Rehberi',
                    'excerpt' => 'Farklı spor dalları için en uygun ayakkabı seçimi nasıl yapılır? Spor performansınızı artıracak doğru ayakkabı önerileri.',
                    'image' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NHx8c3BvcnQlMjBzaG9lc3xlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60',
                    'date' => '18 Mayıs 2025',
                    'author' => 'Serkan Yılmaz',
                    'category' => 'Spor'
                ],
                [
                    'id' => 5,
                    'title' => 'İkonlaşmış Ayakkabı Modelleri ve Hikayeleri',
                    'excerpt' => 'Moda tarihine damga vurmuş ikonik ayakkabı modellerinin ilginç hikayeleri ve günümüzdeki etkileri.',
                    'image' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8M3x8aWNvbmljJTIwc2hvZXN8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
                    'date' => '10 Mayıs 2025',
                    'author' => 'Ayşe Demir',
                    'category' => 'Tarih'
                ],
                [
                    'id' => 6,
                    'title' => 'Vegan Ayakkabı Üretimi ve Sürdürülebilirlik',
                    'excerpt' => 'Ayakkabı sektöründe sürdürülebilirlik ve vegan üretim trendleri. Çevre dostu ayakkabı markaları ve önerileri.',
                    'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8c3VzdGFpbmFibGUlMjBzaG9lc3xlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60',
                    'date' => '01 Mayıs 2025',
                    'author' => 'Deniz Yıldız',
                    'category' => 'Sürdürülebilirlik'
                ]
            ];
            
            foreach($blog_posts as $post): 
            ?>
                <div class="blog-card">
                    <div class="blog-image">
                        <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>">
                        <div class="category"><?php echo $post['category']; ?></div>
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="date"><i class="far fa-calendar"></i> <?php echo $post['date']; ?></span>
                            <span class="author"><i class="far fa-user"></i> <?php echo $post['author']; ?></span>
                        </div>
                        <h2><?php echo $post['title']; ?></h2>
                        <p><?php echo $post['excerpt']; ?></p>
                        <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="read-more">Devamını Oku <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Sayfalama -->
        <div class="pagination">
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#" class="next">Sonraki <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
</section>

<link rel="stylesheet" href="/assets/css/blog.css">

<?php include 'includes/footer.php'; ?>