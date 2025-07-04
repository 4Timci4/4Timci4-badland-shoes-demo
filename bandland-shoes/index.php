<?php include 'includes/header.php'; ?>

<!-- Hero Slider -->
<section class="hero-slider">
    <div class="slide active">
        <div class="slide-content">
            <h2>Slider 1</h2>
            <p>En yeni koleksiyonumuzu keşfedin. Şık ve rahat ayakkabılar ile tarzınızı tamamlayın.</p>
            <a href="/products.php" class="btn">Şimdi Alışveriş Yap</a>
        </div>
    </div>
    <div class="slide">
        <div class="slide-content">
            <h2>Slider 2</h2>
            <p>Özel fırsatlar ve indirimler için hemen tıklayın. Sınırlı stok.</p>
            <a href="/products.php" class="btn">İndirimleri Keşfet</a>
        </div>
    </div>
    <div class="slide">
        <div class="slide-content">
            <h2>Slider 3</h2>
            <p>Yeni sezon ürünlerimiz ile tarzınızı yansıtın. Her tarza uygun ayakkabılar.</p>
            <a href="/products.php" class="btn">Yeni Sezon</a>
        </div>
    </div>
    
    <div class="slider-dots">
        <span class="dot active"></span>
        <span class="dot"></span>
        <span class="dot"></span>
    </div>
</section>

<!-- Popüler Ürünler -->
<section class="products-section">
    <div class="container">
        <div class="section-title">
            <h2>Popüler Ürünler</h2>
            <p>En çok tercih edilen modellerimiz</p>
        </div>
        
        <div class="products-grid">
            <?php
            // Ürünleri göster (Örnek ürünler - gerçek uygulamada veri tabanından çekilir)
            $products = [
                [
                    'id' => 1,
                    'name' => 'Klasik Deri Ayakkabı',
                    'price' => 1299.99,
                    'image' => 'https://images.unsplash.com/photo-1560343090-f0409e92791a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NXx8bGVhdGhlciUyMHNob2VzfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60',
                    'category' => 'erkek'
                ],
                [
                    'id' => 2,
                    'name' => 'Casual Spor Ayakkabı',
                    'price' => 999.99,
                    'image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8c25lYWtlcnN8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
                    'category' => 'erkek'
                ],
                [
                    'id' => 3,
                    'name' => 'Topuklu Ayakkabı',
                    'price' => 1499.99,
                    'image' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MXx8aGlnaCUyMGhlZWxzfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60',
                    'category' => 'kadın'
                ],
                [
                    'id' => 4,
                    'name' => 'Günlük Bot',
                    'price' => 1799.99,
                    'image' => 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8OXx8Ym9vdHN8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
                    'category' => 'kadın'
                ]
            ];
            
            foreach($products as $product): 
            ?>
                <div class="product-card" data-category="<?php echo $product['category']; ?>">
                    <div class="product-image">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <div class="price"><?php echo number_format($product['price'], 2, ',', '.'); ?> TL</div>
                        <a href="/product-details.php?id=<?php echo $product['id']; ?>" class="btn">Ürün Detayı</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Hakkımızda Kısa Bölüm -->
<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1552346154-21d32810aba3?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NHx8c2hvZSUyMHN0b3JlfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60" alt="Mağaza">
            </div>
            <div class="about-text">
                <h2>Schön Hakkında</h2>
                <p>2010 yılında kurulan Schön, Türkiye'nin önde gelen ayakkabı markalarından biridir. Kaliteli malzemeler ve işçilik ile üretilen ürünlerimiz, rahatlık ve şıklığı bir araya getirmektedir.</p>
                <p>Misyonumuz, müşterilerimize en kaliteli ayakkabıları en uygun fiyatlarla sunmaktır. Her adımınızda yanınızda olmaktan gurur duyuyoruz.</p>
                <a href="/about.php" class="btn">Daha Fazla Bilgi</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>