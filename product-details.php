<?php
// Controller'ı yükle (veri işleme)
require_once 'includes/product-controller.php';

// Header'ı ekle
include 'includes/header.php';

// View bileşenlerini yükle
include 'views/product/breadcrumb.php';
include 'views/product/product-detail.php';
include 'views/product/product-tabs.php';
include 'views/product/similar-products.php';

// JavaScript'i ekle
include 'views/product/product-scripts.php';

// Footer'ı ekle
include 'includes/footer.php';
?>
