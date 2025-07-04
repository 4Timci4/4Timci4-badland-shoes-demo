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
            require_once __DIR__ . '/config/database.php';

            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $tag = isset($_GET['tag']) ? $_GET['tag'] : null;

            $blogData = blogService()->get_posts($page, 6, $category, $tag);
            $posts = $blogData['posts'];
            $pages = $blogData['pages'];
            
            // Debug: Gelen veriyi kontrol et
            // echo '<pre>';
            // print_r($blogData);
            // echo '</pre>';

            if (empty($posts)): ?>
                <p class="col-span-full text-center text-gray-500">Bu kriterlere uygun yazı bulunamadı.</p>
            <?php else:
                foreach($posts as $post): 
                    $post = (array) $post; // Obje ise diziye çevir
            ?>
                <div class="blog-card">
                    <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="block">
                        <div class="blog-image">
                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <div class="category"><?php echo htmlspecialchars($post['category']); ?></div>
                        </div>
                    </a>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span class="date"><i class="far fa-calendar-alt"></i> <?php echo date('d F Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <h2 class="font-display"><a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="hover:text-primary transition-colors duration-300"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="read-more mt-4">
                            Devamını Oku <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php 
                endforeach;
            endif; 
            ?>
        </div>
        
        <!-- Sayfalama -->
        <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo $category ? '&category='.$category : ''; ?><?php echo $tag ? '&tag='.$tag : ''; ?>" class="prev">
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
                <a href="?page=<?php echo $i; ?><?php echo $category ? '&category='.$category : ''; ?><?php echo $tag ? '&tag='.$tag : ''; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo $category ? '&category='.$category : ''; ?><?php echo $tag ? '&tag='.$tag : ''; ?>" class="next">
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
        <?php endif; ?>
    </div>
</section>

<link rel="stylesheet" href="/assets/css/blog.css?v=<?php echo time(); ?>">

<?php include 'includes/footer.php'; ?>
