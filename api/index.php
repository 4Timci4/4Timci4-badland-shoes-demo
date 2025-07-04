<?php include 'includes/header.php'; ?>

<!-- Hero Slider -->
<section class="relative h-[600px] overflow-hidden">
    <div class="slide active absolute inset-0 w-full h-full opacity-100 transition-opacity duration-1000 flex items-center justify-center text-center text-white bg-primary">
        <div class="slide-content max-w-4xl px-5">
            <h2 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-5">Slider 1</h2>
            <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto">En yeni koleksiyonumuzu keşfedin. Şık ve rahat ayakkabılar ile tarzınızı tamamlayın.</p>
            <a href="/products.php" class="inline-block px-8 py-3 bg-white text-primary rounded-full font-semibold uppercase text-sm tracking-wide hover:bg-secondary hover:text-white transition-all duration-300">Şimdi Alışveriş Yap</a>
        </div>
    </div>
    <div class="slide absolute inset-0 w-full h-full opacity-0 transition-opacity duration-1000 flex items-center justify-center text-center text-white bg-blue-500">
        <div class="slide-content max-w-4xl px-5">
            <h2 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-5">Slider 2</h2>
            <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto">Özel fırsatlar ve indirimler için hemen tıklayın. Sınırlı stok.</p>
            <a href="/products.php" class="inline-block px-8 py-3 bg-white text-blue-500 rounded-full font-semibold uppercase text-sm tracking-wide hover:bg-secondary hover:text-white transition-all duration-300">İndirimleri Keşfet</a>
        </div>
    </div>
    <div class="slide absolute inset-0 w-full h-full opacity-0 transition-opacity duration-1000 flex items-center justify-center text-center text-white bg-green-500">
        <div class="slide-content max-w-4xl px-5">
            <h2 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-5">Slider 3</h2>
            <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto">Yeni sezon ürünlerimiz ile tarzınızı yansıtın. Her tarza uygun ayakkabılar.</p>
            <a href="/products.php" class="inline-block px-8 py-3 bg-white text-green-500 rounded-full font-semibold uppercase text-sm tracking-wide hover:bg-secondary hover:text-white transition-all duration-300">Yeni Sezon</a>
        </div>
    </div>
    
    <div class="slider-dots absolute bottom-8 left-1/2 transform -translate-x-1/2 flex space-x-2">
        <span class="dot w-3 h-3 bg-white bg-opacity-100 rounded-full cursor-pointer"></span>
        <span class="dot w-3 h-3 bg-white bg-opacity-50 rounded-full cursor-pointer"></span>
        <span class="dot w-3 h-3 bg-white bg-opacity-50 rounded-full cursor-pointer"></span>
    </div>
</section>

<!-- Popüler Ürünler -->
<section class="py-20 bg-light">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-3 text-secondary">Popüler Ürünler</h2>
            <p class="text-gray-600">En çok tercih edilen modellerimiz</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
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
                <div class="bg-white rounded-lg overflow-hidden shadow-lg hover:-translate-y-2 transition-transform duration-300" data-category="<?php echo $product['category']; ?>">
                    <div class="h-70 overflow-hidden">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-semibold mb-2 text-secondary"><?php echo $product['name']; ?></h3>
                        <div class="text-xl font-semibold text-primary mb-4"><?php echo number_format($product['price'], 2, ',', '.'); ?> TL</div>
                        <a href="/product-details.php?id=<?php echo $product['id']; ?>" class="block w-full text-center px-6 py-2 bg-primary text-white rounded-full font-semibold hover:bg-pink-600 transition-colors duration-300">Ürün Detayı</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Hakkımızda Kısa Bölüm -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1">
                <img src="https://images.unsplash.com/photo-1552346154-21d32810aba3?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NHx8c2hvZSUyMHN0b3JlfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60" alt="Mağaza" class="w-full rounded-lg shadow-lg">
            </div>
            <div class="flex-1 text-center lg:text-left">
                <h2 class="text-4xl font-bold mb-5 text-secondary">Schön Hakkında</h2>
                <p class="mb-5 text-gray-600 leading-relaxed">2010 yılında kurulan Schön, Türkiye'nin önde gelen ayakkabı markalarından biridir. Kaliteli malzemeler ve işçilik ile üretilen ürünlerimiz, rahatlık ve şıklığı bir araya getirmektedir.</p>
                <p class="mb-8 text-gray-600 leading-relaxed">Misyonumuz, müşterilerimize en kaliteli ayakkabıları en uygun fiyatlarla sunmaktır. Her adımınızda yanınızda olmaktan gurur duyuyoruz.</p>
                <a href="/about.php" class="inline-block px-8 py-3 bg-primary text-white rounded-full font-semibold hover:bg-pink-600 transition-colors duration-300">Daha Fazla Bilgi</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>