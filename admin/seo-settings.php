<?php



$page_title = 'SEO Ayarları';
$breadcrumb_items = [
    ['title' => 'Ayarlar', 'url' => '#', 'icon' => 'fas fa-cog'],
    ['title' => 'SEO Ayarları', 'url' => 'seo-settings.php', 'icon' => 'fas fa-search']
];


$additional_css = [];
$additional_js = [];


include 'includes/header.php';


include 'views/seo/index.php';


include 'includes/footer.php';
?>