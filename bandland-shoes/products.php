<?php include 'includes/header.php'; ?>

<!-- Ürünler Sayfası Banner -->
<section class="page-banner">
    <div class="container">
        <h1>Ürünlerimiz</h1>
        <p>Kaliteli ve şık ayakkabı modelleri</p>
    </div>
</section>

<!-- Ürün Filtreleme -->
<section class="filter-section">
    <div class="container">
        <div class="filters">
            <button class="filter-btn active" data-filter="all">Tümü</button>
            <button class="filter-btn" data-filter="erkek">Erkek</button>
            <button class="filter-btn" data-filter="kadın">Kadın</button>
            <button class="filter-btn" data-filter="çocuk">Çocuk</button>
            <button class="filter-btn" data-filter="spor">Spor</button>
        </div>
    </div>
</section>

<!-- Ürünler Listesi -->
<section class="products-section">
    <div class="container">
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
                ],
                [
                    'id' => 5,
                    'name' => 'Çocuk Spor Ayakkabı',
                    'price' => 799.99,
                    'image' => 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8a2lkcyUyMHNob2VzfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60',
                    'category' => 'çocuk'
                ],
                [
                    'id' => 6,
                    'name' => 'Koşu Ayakkabısı',
                    'price' => 1599.99,
                    'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8c3BvcnQlMjBzaG9lc3xlbnwwfHwwfHw%3D&auto=format&fit=crop&w=500&q=60',
                    'category' => 'spor'
                ],
                [
                    'id' => 7,
                    'name' => 'Babet',
                    'price' => 899.99,
                    'image' => 'https://images.unsplash.com/photo-1545128485-c400e7702796?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8ZmxhdCUyMHNob2VzfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60',
                    'category' => 'kadın'
                ],
                [
                    'id' => 8,
                    'name' => 'Klasik Oxford',
                    'price' => 1899.99,
                    'image' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8b3hmb3JkJTIwc2hvZXN8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
                    'category' => 'erkek'
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

<script>
// Filtreleme işlemi
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Aktif filtre butonunu güncelle
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Ürünleri filtrele
            const filter = this.dataset.filter;
            const products = document.querySelectorAll('.product-card');
            
            products.forEach(product => {
                if (filter === 'all' || product.dataset.category === filter) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        });
    });
});
</script>

<link rel="stylesheet" href="/assets/css/products.css">

<?php include 'includes/footer.php'; ?>