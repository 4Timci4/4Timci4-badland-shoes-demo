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
        
        <?php
        // Debug mode kontrolü
        $debug = isset($_GET['debug']) && $_GET['debug'] === '1';
        
        try {
            // Gerekli dosyaları dahil et
            require_once __DIR__ . '/config/database.php';
            
            if ($debug) {
                echo '<div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded">';
                echo '<h3 class="font-bold text-yellow-800">Debug Modu Aktif</h3>';
                echo '<p class="text-yellow-700">Supabase bağlantısı test ediliyor...</p>';
                
                // Supabase bağlantısını test et
                $testResponse = supabase()->request('blogs?select=count(*)');
                echo '<pre class="text-sm mt-2">';
                echo 'Bağlantı testi: ' . (empty($testResponse['body']) ? 'BAŞARISIZ' : 'BAŞARILI') . "\n";
                echo 'API Yanıtı: ' . json_encode($testResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo '</pre>';
                echo '</div>';
            }
            
            // Sayfalama ve filtreleme parametreleri
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = 6;
            $category = isset($_GET['category']) ? trim($_GET['category']) : null;
            $tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;
            
            // Blog yazılarını getir
            $blogService = blogService();
            $blogData = $blogService->get_posts($page, $perPage, $category, $tag);
            
            if ($debug) {
                echo '<div class="mb-4 p-4 bg-blue-100 border border-blue-400 rounded">';
                echo '<h3 class="font-bold text-blue-800">Blog Verisi Debug</h3>';
                echo '<pre class="text-sm mt-2">';
                echo 'Sayfa: ' . $page . "\n";
                echo 'Kategori: ' . ($category ?: 'Hepsi') . "\n";
                echo 'Etiket: ' . ($tag ?: 'Hepsi') . "\n";
                echo 'Toplam yazı: ' . $blogData['total'] . "\n";
                echo 'Toplam sayfa: ' . $blogData['pages'] . "\n";
                echo 'Mevcut sayfa yazı sayısı: ' . count($blogData['posts']) . "\n";
                echo 'Blog verisi: ' . json_encode($blogData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo '</pre>';
                echo '</div>';
            }
            
            $posts = $blogData['posts'] ?? [];
            $totalPosts = $blogData['total'] ?? 0;
            $totalPages = $blogData['pages'] ?? 0;
            
        } catch (Exception $e) {
            echo '<div class="mb-4 p-4 bg-red-100 border border-red-400 rounded">';
            echo '<h3 class="font-bold text-red-800">Hata Oluştu</h3>';
            echo '<p class="text-red-700">Blog yazıları yüklenirken bir hata oluştu: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
            
            $posts = [];
            $totalPosts = 0;
            $totalPages = 0;
        }
        ?>
        
        <!-- Blog Filtreleme -->
        <div class="mb-8">
            <div class="flex flex-wrap gap-2 justify-center">
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'tag' => $tag])); ?>" 
                   class="px-4 py-2 rounded-full border <?php echo !$category ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    Tüm Kategoriler
                </a>
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'category' => 'Trendler', 'tag' => $tag])); ?>" 
                   class="px-4 py-2 rounded-full border <?php echo $category === 'Trendler' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    Trendler
                </a>
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'category' => 'Sağlık', 'tag' => $tag])); ?>" 
                   class="px-4 py-2 rounded-full border <?php echo $category === 'Sağlık' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    Sağlık
                </a>
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'category' => 'Moda', 'tag' => $tag])); ?>" 
                   class="px-4 py-2 rounded-full border <?php echo $category === 'Moda' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    Moda
                </a>
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'category' => 'Bakım', 'tag' => $tag])); ?>" 
                   class="px-4 py-2 rounded-full border <?php echo $category === 'Bakım' ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    Bakım
                </a>
            </div>
        </div>
        
        <!-- Blog Yazıları -->
        <div class="blog-grid">
            <?php if (empty($posts)): ?>
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <div class="max-w-md mx-auto">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">Henüz Blog Yazısı Yok</h3>
                            <p class="mt-1 text-gray-500">
                                <?php if ($category || $tag): ?>
                                    Bu kriterlere uygun yazı bulunamadı. Farklı filtreler deneyin.
                                <?php else: ?>
                                    Henüz blog yazısı eklenmemiş. Yakında harika içerikler paylaşacağız!
                                <?php endif; ?>
                            </p>
                            <?php if ($category || $tag): ?>
                                <div class="mt-4">
                                    <a href="?" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark">
                                        Tüm Yazıları Gör
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($posts as $post): 
                    // Veriyi array formatına çevir
                    $post = (array) $post;
                    
                    // Varsayılan değerleri ayarla
                    $post['id'] = $post['id'] ?? 0;
                    $post['title'] = $post['title'] ?? 'Başlık Yok';
                    $post['excerpt'] = $post['excerpt'] ?? 'Özet yok...';
                    $post['image_url'] = $post['image_url'] ?? '/assets/images/default-blog.jpg';
                    $post['category'] = $post['category'] ?? 'Genel';
                    $post['created_at'] = $post['created_at'] ?? date('Y-m-d H:i:s');
                ?>
                    <div class="blog-card">
                        <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="block">
                            <div class="blog-image">
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>"
                                     onerror="this.src='/assets/images/default-blog.jpg'">
                                <div class="category"><?php echo htmlspecialchars($post['category']); ?></div>
                            </div>
                        </a>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span class="date">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date('d F Y', strtotime($post['created_at'])); ?>
                                </span>
                            </div>
                            <h2 class="font-display">
                                <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" 
                                   class="hover:text-primary transition-colors duration-300">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <p class="text-gray-600"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="read-more mt-4">
                                Devamını Oku <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Sayfalama -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_filter(['page' => $page - 1, 'category' => $category, 'tag' => $tag])); ?>" class="prev">
                        <i class="fas fa-chevron-left"></i>
                        <span>Önceki</span>
                    </a>
                <?php else: ?>
                    <span class="prev disabled">
                        <i class="fas fa-chevron-left"></i>
                        <span>Önceki</span>
                    </span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?php echo http_build_query(array_filter(['page' => $i, 'category' => $category, 'tag' => $tag])); ?>" 
                       class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_filter(['page' => $page + 1, 'category' => $category, 'tag' => $tag])); ?>" class="next">
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
        
        <!-- Debug Link -->
        <?php if (!$debug): ?>
            <div class="mt-8 text-center">
                <a href="?debug=1" class="text-sm text-gray-500 hover:text-gray-700">
                    🔧 Debug modunu etkinleştir
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<link rel="stylesheet" href="/assets/css/blog.css?v=<?php echo time(); ?>">

<?php include 'includes/footer.php'; ?>
