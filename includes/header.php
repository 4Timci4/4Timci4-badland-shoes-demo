<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schön | Türkiye'nin En Kaliteli Ayakkabı Markası</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/index.php">
                    <h1>schön<span>.</span></h1>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="/index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Ana Sayfa</a></li>
                    <li><a href="/products.php" class="<?php echo ($current_page == 'products.php') ? 'active' : ''; ?>">Ürünler</a></li>
                    <li><a href="/about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">Hakkımızda</a></li>
                    <li><a href="/blog.php" class="<?php echo ($current_page == 'blog.php') ? 'active' : ''; ?>">Blog</a></li>
                    <li><a href="/contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">İletişim</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>