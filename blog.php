<?php

require_once 'services/AuthService.php';
$authService = new AuthService();

include 'includes/header.php';
?>

<section class="relative bg-gradient-to-r from-primary to-purple-600 text-white py-16 overflow-hidden">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80'); opacity: 0.3;">
    </div>
    <div class="relative max-w-7xl mx-auto px-5 text-center">
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">Blog</h1>
        <p class="text-xl text-white/90">Ayakkabı dünyası hakkında en güncel bilgiler, trendler ve ipuçları</p>
    </div>
</section>

<section class="blog-section py-12">
    <div class="container mx-auto px-4">

        <?php

        $debug = isset($_GET['debug']) && $_GET['debug'] === '1';

        try {

            require_once __DIR__ . '/config/database.php';

            if ($debug) {
                echo '<div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded">';
                echo '<h3 class="font-bold text-yellow-800">Debug Modu Aktif</h3>';
                echo '<p class="text-yellow-700">Supabase bağlantısı test ediliyor...</p>';


                $testResponse = database()->count('blogs');
                echo '<pre class="text-sm mt-2">';
                echo 'Bağlantı testi: ' . (is_numeric($testResponse) && $testResponse >= 0 ? 'BAŞARILI' : 'BAŞARISIZ') . "\n";
                echo 'Toplam blog sayısı: ' . $testResponse . "\n";
                echo '</pre>';
                echo '</div>';
            }


            require_once 'services/SettingsService.php';
            $settingsService = new SettingsService();

            $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
            $perPage = $settingsService->getSiteSetting('blogs_per_page', 6);
            $category = isset($_GET['category']) ? trim($_GET['category']) : null;
            $tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;


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

        <div class="mb-8">
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'tag' => $tag])); ?>"
                    class="px-6 py-3 rounded-full border font-medium transition-all duration-300 <?php echo !$category ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'; ?>">
                    Tüm Kategoriler
                </a>
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'category' => 'Trendler', 'tag' => $tag])); ?>"
                    class="px-6 py-3 rounded-full border font-medium transition-all duration-300 <?php echo $category === 'Trendler' ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'; ?>">
                    Trendler
                </a>
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'category' => 'Sağlık', 'tag' => $tag])); ?>"
                    class="px-6 py-3 rounded-full border font-medium transition-all duration-300 <?php echo $category === 'Sağlık' ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'; ?>">
                    Sağlık
                </a>
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'category' => 'Moda', 'tag' => $tag])); ?>"
                    class="px-6 py-3 rounded-full border font-medium transition-all duration-300 <?php echo $category === 'Moda' ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'; ?>">
                    Moda
                </a>
                <a href="?<?php echo http_build_query(array_filter(['page' => 1, 'category' => 'Bakım', 'tag' => $tag])); ?>"
                    class="px-6 py-3 rounded-full border font-medium transition-all duration-300 <?php echo $category === 'Bakım' ? 'bg-primary text-white border-primary shadow-md' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'; ?>">
                    Bakım
                </a>
            </div>
        </div>

        <div class="blog-grid">
            <?php if (empty($posts)): ?>
                <div class="col-span-full">
                    <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="max-w-md mx-auto">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
                                    <a href="?"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark">
                                        Tüm Yazıları Gör
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post):

                    $post = (array) $post;


                    $post['id'] = $post['id'] ?? 0;
                    $post['title'] = $post['title'] ?? 'Başlık Yok';
                    $post['excerpt'] = $post['excerpt'] ?? 'Özet yok...';
                    $post['image_url'] = $post['image_url'] ?? '/assets/images/placeholder.svg';
                    $post['category'] = $post['category'] ?? 'Genel';
                    $post['created_at'] = $post['created_at'] ?? date('Y-m-d H:i:s');
                    ?>
                    <div class="blog-card bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <a href="/blog-detail.php?id=<?php echo $post['id']; ?>" class="block">
                            <div class="blog-image relative h-60">
                                <img src="<?php echo htmlspecialchars($post['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover"
                                    onerror="this.src='/assets/images/placeholder.svg'">
                                <div
                                    class="category absolute top-4 left-4 px-3 py-1 text-xs font-semibold text-white rounded-full uppercase tracking-wide">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </div>
                            </div>
                        </a>
                        <div class="blog-content p-6">
                            <div class="blog-meta flex items-center gap-4 text-sm text-gray-500 mb-4">
                                <span class="date flex items-center gap-2">
                                    <i class="far fa-calendar-alt text-primary"></i>
                                    <?php echo date('d F Y', strtotime($post['created_at'])); ?>
                                </span>
                            </div>
                            <h2 class="font-display text-xl font-semibold mb-3 leading-tight">
                                <a href="/blog-detail.php?id=<?php echo $post['id']; ?>"
                                    class="text-gray-900 hover:text-primary transition-colors duration-300">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <p class="text-gray-600 mb-4 line-clamp-3"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <a href="/blog-detail.php?id=<?php echo $post['id']; ?>"
                                class="read-more inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all duration-300">
                                Devamını Oku <i class="fas fa-arrow-right text-sm"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination flex justify-center items-center mt-12 gap-2">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_filter(['page' => $page - 1, 'category' => $category, 'tag' => $tag])); ?>"
                        class="prev flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-300">
                        <i class="fas fa-chevron-left text-sm"></i>
                        <span>Önceki</span>
                    </a>
                <?php else: ?>
                    <span
                        class="prev disabled flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-200 rounded-full text-gray-400 cursor-not-allowed">
                        <i class="fas fa-chevron-left text-sm"></i>
                        <span>Önceki</span>
                    </span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?php echo http_build_query(array_filter(['page' => $i, 'category' => $category, 'tag' => $tag])); ?>"
                        class="w-10 h-10 flex items-center justify-center rounded-full border font-semibold transition-all duration-300 <?php echo ($i == $page) ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_filter(['page' => $page + 1, 'category' => $category, 'tag' => $tag])); ?>"
                        class="next flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-300">
                        <span>Sonraki</span>
                        <i class="fas fa-chevron-right text-sm"></i>
                    </a>
                <?php else: ?>
                    <span
                        class="next disabled flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-200 rounded-full text-gray-400 cursor-not-allowed">
                        <span>Sonraki</span>
                        <i class="fas fa-chevron-right text-sm"></i>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

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