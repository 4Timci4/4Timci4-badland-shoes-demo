<?php

require_once __DIR__ . '/api_bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/SettingsService.php';

$settingsService = new SettingsService();

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = $settingsService->getSiteSetting('blogs_per_page', 6);
$category = isset($_GET['category']) ? trim($_GET['category']) : null;
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;

try {
    $blogService = blogService();
    $blogData = $blogService->get_posts($page, $perPage, $category, $tag);

    $posts = $blogData['posts'] ?? [];
    $totalPosts = $blogData['total'] ?? 0;
    $totalPages = $blogData['pages'] ?? 0;

} catch (Exception $e) {
    $posts = [];
    $totalPosts = 0;
    $totalPages = 0;
}

// Blog kartlarını oluştur
ob_start();

if (empty($posts)) {
    echo '<div class="col-span-full">';
    echo '    <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">';
    echo '        <div class="max-w-md mx-auto">';
    echo '            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
    echo '                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />';
    echo '            </svg>';
    echo '            <h3 class="mt-2 text-lg font-medium text-gray-900">Henüz Blog Yazısı Yok</h3>';
    echo '            <p class="mt-1 text-gray-500">';
    if ($category || $tag) {
        echo '                Bu kriterlere uygun yazı bulunamadı. Farklı filtreler deneyin.';
    } else {
        echo '                Henüz blog yazısı eklenmemiş. Yakında harika içerikler paylaşacağız!';
    }
    echo '            </p>';
    if ($category || $tag) {
        echo '            <div class="mt-4">';
        echo '                <a href="?" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark">';
        echo '                    Tüm Yazıları Gör';
        echo '                </a>';
        echo '            </div>';
    }
    echo '        </div>';
    echo '    </div>';
    echo '</div>';
} else {
    foreach ($posts as $post) {
        $post = (array) $post;

        $post['id'] = $post['id'] ?? 0;
        $post['title'] = $post['title'] ?? 'Başlık Yok';
        $post['excerpt'] = $post['excerpt'] ?? 'Özet yok...';
        $post['image_url'] = $post['image_url'] ?? '/assets/images/placeholder.svg';
        $post['category'] = $post['category'] ?? 'Genel';
        $post['created_at'] = $post['created_at'] ?? date('Y-m-d H:i:s');

        echo '<div class="blog-card bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">';
        echo '    <a href="/blog-detail.php?id=' . $post['id'] . '" class="block">';
        echo '        <div class="blog-image relative h-60">';
        echo '            <img src="' . htmlspecialchars($post['image_url']) . '" alt="' . htmlspecialchars($post['title']) . '" class="w-full h-full object-cover" onerror="this.src=\'/assets/images/placeholder.svg\'">';
        echo '            <div class="category absolute top-4 left-4 px-3 py-1 text-xs font-semibold text-white rounded-full uppercase tracking-wide">';
        echo '                ' . htmlspecialchars($post['category']);
        echo '            </div>';
        echo '        </div>';
        echo '    </a>';
        echo '    <div class="blog-content p-6">';
        echo '        <div class="blog-meta flex items-center gap-4 text-sm text-gray-500 mb-4">';
        echo '            <span class="date flex items-center gap-2">';
        echo '                <i class="far fa-calendar-alt text-primary"></i>';
        echo '                ' . date('d F Y', strtotime($post['created_at']));
        echo '            </span>';
        echo '        </div>';
        echo '        <h2 class="font-display text-xl font-semibold mb-3 leading-tight">';
        echo '            <a href="/blog-detail.php?id=' . $post['id'] . '" class="text-gray-900 hover:text-primary transition-colors duration-300">';
        echo '                ' . htmlspecialchars($post['title']);
        echo '            </a>';
        echo '        </h2>';
        echo '        <p class="text-gray-600 mb-4 line-clamp-3">' . htmlspecialchars($post['excerpt']) . '</p>';
        echo '        <a href="/blog-detail.php?id=' . $post['id'] . '" class="read-more inline-flex items-center gap-2 text-primary font-semibold hover:gap-3 transition-all duration-300">';
        echo '            Devamını Oku <i class="fas fa-arrow-right text-sm"></i>';
        echo '        </a>';
        echo '    </div>';
        echo '</div>';
    }
}

$blog_html = ob_get_clean();

// Pagination oluştur
ob_start();

if ($totalPages > 1) {
    if ($page > 1) {
        $prev_params = array_filter(['page' => $page - 1, 'category' => $category, 'tag' => $tag]);
        echo '<a href="?' . http_build_query($prev_params) . '" class="prev flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-300">';
        echo '    <i class="fas fa-chevron-left text-sm"></i>';
        echo '    <span>Önceki</span>';
        echo '</a>';
    } else {
        echo '<span class="prev disabled flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-200 rounded-full text-gray-400 cursor-not-allowed">';
        echo '    <i class="fas fa-chevron-left text-sm"></i>';
        echo '    <span>Önceki</span>';
        echo '</span>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        $page_params = array_filter(['page' => $i, 'category' => $category, 'tag' => $tag]);
        $active_class = ($i == $page) ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 hover:border-gray-400';
        echo '<a href="?' . http_build_query($page_params) . '" class="w-10 h-10 flex items-center justify-center rounded-full border font-semibold transition-all duration-300 ' . $active_class . '">';
        echo '    ' . $i;
        echo '</a>';
    }

    if ($page < $totalPages) {
        $next_params = array_filter(['page' => $page + 1, 'category' => $category, 'tag' => $tag]);
        echo '<a href="?' . http_build_query($next_params) . '" class="next flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-full text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-300">';
        echo '    <span>Sonraki</span>';
        echo '    <i class="fas fa-chevron-right text-sm"></i>';
        echo '</a>';
    } else {
        echo '<span class="next disabled flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-200 rounded-full text-gray-400 cursor-not-allowed">';
        echo '    <span>Sonraki</span>';
        echo '    <i class="fas fa-chevron-right text-sm"></i>';
        echo '</span>';
    }
}

$pagination_html = ob_get_clean();

header('Content-Type: application/json');
echo json_encode([
    'blog_html' => $blog_html,
    'pagination_html' => $pagination_html,
    'total_posts' => $totalPosts,
    'total_pages' => $totalPages,
    'current_page' => $page
]);