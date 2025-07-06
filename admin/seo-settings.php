<?php
/**
 * Admin Panel - SEO Ayarları
 * SEO meta bilgileri ve sosyal medya ayarlarını yönetme sayfası
 * 
 * Bu dosya artık modüler yapıya sahiptir:
 * - Controller'lar: admin/controllers/
 * - View'ler: admin/views/seo/
 */

// Sayfa bilgileri
$page_title = 'SEO Ayarları';
$breadcrumb_items = [
    ['title' => 'Ayarlar', 'url' => '#', 'icon' => 'fas fa-cog'],
    ['title' => 'SEO Ayarları', 'url' => 'seo-settings.php', 'icon' => 'fas fa-search']
];

// Gerekli CSS ve JS
$additional_css = [];
$additional_js = [];

// Header dahil et
include 'includes/header.php';

// Ana SEO view'ini dahil et
include 'views/seo/index.php';

// Footer dahil et
include 'includes/footer.php';
?>
