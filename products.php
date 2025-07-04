<?php include 'includes/header.php'; ?>

<!-- Ürünler Sayfası Banner -->
<section class="bg-primary text-white py-12 text-center mb-12">
    <div class="max-w-7xl mx-auto px-5">
        <h1 class="text-5xl font-bold mb-4">Ürünlerimiz</h1>
        <p class="text-xl">Kaliteli ve şık ayakkabı modelleri</p>
    </div>
</section>

<!-- Ürün Filtreleme -->
<section class="py-8 bg-white border-b">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-wrap justify-center gap-4">
            <button class="filter-btn px-6 py-3 rounded-full font-semibold transition-all duration-300 bg-primary text-white" data-filter="all">Tümü</button>
            <button class="filter-btn px-6 py-3 rounded-full font-semibold transition-all duration-300 bg-gray-200 text-gray-700 hover:bg-primary hover:text-white" data-filter="erkek">Erkek</button>
            <button class="filter-btn px-6 py-3 rounded-full font-semibold transition-all duration-300 bg-gray-200 text-gray-700 hover:bg-primary hover:text-white" data-filter="kadın">Kadın</button>
            <button class="filter-btn px-6 py-3 rounded-full font-semibold transition-all duration-300 bg-gray-200 text-gray-700 hover:bg-primary hover:text-white" data-filter="çocuk">Çocuk</button>
            <button class="filter-btn px-6 py-3 rounded-full font-semibold transition-all duration-300 bg-gray-200 text-gray-700 hover:bg-primary hover:text-white" data-filter="spor">Spor</button>
        </div>
    </div>
</section>

<!-- Ürünler Listesi -->
<section class="py-16 bg-light">
    <div class="max-w-7xl mx-auto px-5">
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

<script>
// Filtreleme işlemi
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Aktif filtre butonunu güncelle
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            this.classList.remove('bg-gray-200', 'text-gray-700');
            this.classList.add('bg-primary', 'text-white');
            
            // Ürünleri filtrele
            const filter = this.dataset.filter;
            const products = document.querySelectorAll('[data-category]');
            
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

<?php include 'includes/footer.php'; ?>