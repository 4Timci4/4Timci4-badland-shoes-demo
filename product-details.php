<?php include 'includes/header.php'; ?>

<?php
// Ürün ID'sini al
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ürün veritabanı (gerçek uygulamada veritabanından çekilir)
$products = [
    1 => [
        'id' => 1,
        'name' => 'Klasik Deri Ayakkabı',
        'price' => 1299.99,
        'image' => 'https://images.unsplash.com/photo-1560343090-f0409e92791a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NXx8bGVhdGhlciUyMHNob2VzfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60',
        'category' => 'erkek',
        'description' => 'Klasik tarza sahip bu deri ayakkabı, yüksek kaliteli malzemeler kullanılarak üretilmiştir. Hem günlük kullanım hem de özel günler için uygundur.',
        'features' => [
            'Hakiki deri',
            'El yapımı',
            'Kaymaz taban',
            'Nefes alabilen iç astar',
            'Uzun ömürlü'
        ],
        'sizes' => [40, 41, 42, 43, 44, 45],
        'colors' => ['Siyah', 'Kahverengi', 'Bordo']
    ],
    2 => [
        'id' => 2,
        'name' => 'Casual Spor Ayakkabı',
        'price' => 999.99,
        'image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8Mnx8c25lYWtlcnN8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
        'category' => 'erkek',
        'description' => 'Modern ve şık tasarıma sahip bu spor ayakkabı, günlük kullanım için idealdir. Konforlu yapısı sayesinde uzun süre rahatlıkla kullanabilirsiniz.',
        'features' => [
            'Hafif yapı',
            'Hava yastıklı taban',
            'Nefes alabilen üst malzeme',
            'Darbe emici iç taban',
            'Esnek yapı'
        ],
        'sizes' => [39, 40, 41, 42, 43, 44],
        'colors' => ['Beyaz', 'Siyah', 'Gri', 'Mavi']
    ],
    3 => [
        'id' => 3,
        'name' => 'Topuklu Ayakkabı',
        'price' => 1499.99,
        'image' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MXx8aGlnaCUyMGhlZWxzfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60',
        'category' => 'kadın',
        'description' => 'Zarif ve şık tasarıma sahip bu topuklu ayakkabı, özel davetler ve iş toplantıları için mükemmel bir seçimdir. Konforlu iç yapısı sayesinde uzun süre rahatlıkla kullanabilirsiniz.',
        'features' => [
            'Yüksek kalite suni deri',
            'Ergonomik iç taban',
            'Kaymaz dış taban',
            'Stabil topuk yapısı',
            'Şık tasarım'
        ],
        'sizes' => [35, 36, 37, 38, 39, 40],
        'colors' => ['Siyah', 'Kırmızı', 'Bej', 'Lacivert']
    ],
    4 => [
        'id' => 4,
        'name' => 'Günlük Bot',
        'price' => 1799.99,
        'image' => 'https://images.unsplash.com/photo-1608256246200-53e635b5b65f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8OXx8Ym9vdHN8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
        'category' => 'kadın',
        'description' => 'Soğuk hava koşullarında bile sıcak tutacak bu günlük bot, hem şık hem de fonksiyonel bir tasarıma sahiptir. Su geçirmez özelliği sayesinde yağmurlu havalarda da kullanabilirsiniz.',
        'features' => [
            'Su geçirmez dış yüzey',
            'Sıcak tutan iç astar',
            'Kaymaz taban',
            'Dayanıklı yapı',
            'Uzun ömürlü'
        ],
        'sizes' => [36, 37, 38, 39, 40],
        'colors' => ['Siyah', 'Kahverengi', 'Haki']
    ]
];

// Eğer ürün bulunamazsa geri yönlendir
if (!isset($products[$product_id])) {
    header("Location: products.php");
    exit;
}

// Ürün bilgilerini al
$product = $products[$product_id];
?>

<!-- Ürün Detay -->
<section class="product-detail">
    <div class="container">
        <div class="product-container">
            <div class="product-images">
                <div class="main-image">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                </div>
                <div class="thumbnail-images">
                    <!-- Daha fazla ürün resmi eklenebilir -->
                    <img src="<?php echo $product['image']; ?>" alt="Thumbnail 1" class="active">
                    <img src="<?php echo $product['image']; ?>" alt="Thumbnail 2">
                    <img src="<?php echo $product['image']; ?>" alt="Thumbnail 3">
                </div>
            </div>
            <div class="product-info">
                <h1><?php echo $product['name']; ?></h1>
                <div class="price"><?php echo number_format($product['price'], 2, ',', '.'); ?> TL</div>
                
                <div class="description">
                    <p><?php echo $product['description']; ?></p>
                </div>
                
                <div class="product-options">
                    <div class="size-options">
                        <h3>Beden:</h3>
                        <div class="options">
                            <?php foreach($product['sizes'] as $size): ?>
                                <button class="option-btn"><?php echo $size; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="color-options">
                        <h3>Renk:</h3>
                        <div class="options">
                            <?php foreach($product['colors'] as $color): ?>
                                <button class="option-btn"><?php echo $color; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="quantity">
                    <h3>Adet:</h3>
                    <div class="quantity-input">
                        <button class="decrease">-</button>
                        <input type="number" value="1" min="1" max="10">
                        <button class="increase">+</button>
                    </div>
                </div>
                
                <div class="product-actions">
                    <button class="btn add-to-cart">Sepete Ekle</button>
                    <button class="btn-outline add-to-favorites"><i class="far fa-heart"></i></button>
                </div>
            </div>
        </div>
        
        <div class="product-tabs">
            <div class="tabs">
                <button class="tab-btn active" data-tab="description">Açıklama</button>
                <button class="tab-btn" data-tab="features">Özellikler</button>
                <button class="tab-btn" data-tab="reviews">Yorumlar</button>
            </div>
            
            <div class="tab-content">
                <div class="tab-pane active" id="description">
                    <p><?php echo $product['description']; ?></p>
                </div>
                
                <div class="tab-pane" id="features">
                    <ul class="features-list">
                        <?php foreach($product['features'] as $feature): ?>
                            <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="tab-pane" id="reviews">
                    <div class="reviews-container">
                        <!-- Örnek Yorum -->
                        <div class="review">
                            <div class="review-header">
                                <div class="reviewer">Ahmet Y.</div>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <div class="review-date">15.03.2025</div>
                            </div>
                            <div class="review-content">
                                <p>Çok kaliteli bir ürün. Tam beklediğim gibi çıktı. Uygun fiyata yüksek kalite. Kesinlikle tavsiye ederim.</p>
                            </div>
                        </div>
                        
                        <div class="review">
                            <div class="review-header">
                                <div class="reviewer">Zeynep K.</div>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <div class="review-date">02.04.2025</div>
                            </div>
                            <div class="review-content">
                                <p>Ürün güzel ancak kargo biraz geç geldi. Kalitesi ve işçiliği beklediğimden iyi çıktı.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benzer Ürünler -->
<section class="products-section">
    <div class="container">
        <div class="section-title">
            <h2>Benzer Ürünler</h2>
            <p>Beğenebileceğiniz diğer modellerimiz</p>
        </div>
        
        <div class="products-grid">
            <?php
            // Benzer ürünleri göster (aynı kategoriden)
            $similar_products = [];
            foreach($products as $p) {
                if ($p['id'] != $product_id && $p['category'] == $product['category']) {
                    $similar_products[] = $p;
                }
                if (count($similar_products) >= 4) break; // En fazla 4 benzer ürün göster
            }
            
            foreach($similar_products as $p): 
            ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $p['image']; ?>" alt="<?php echo $p['name']; ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo $p['name']; ?></h3>
                        <div class="price"><?php echo number_format($p['price'], 2, ',', '.'); ?> TL</div>
                        <a href="/product-details.php?id=<?php echo $p['id']; ?>" class="btn">Ürün Detayı</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<link rel="stylesheet" href="/assets/css/product-details.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Küçük resimleri tıklama
    const thumbnails = document.querySelectorAll('.thumbnail-images img');
    const mainImage = document.querySelector('.main-image img');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Aktif thumbnail'ı güncelle
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Ana resmi güncelle
            mainImage.src = this.src;
        });
    });
    
    // Adet artırma/azaltma
    const decreaseBtn = document.querySelector('.decrease');
    const increaseBtn = document.querySelector('.increase');
    const quantityInput = document.querySelector('.quantity-input input');
    
    decreaseBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        if (value > 1) {
            quantityInput.value = value - 1;
        }
    });
    
    increaseBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        if (value < 10) {
            quantityInput.value = value + 1;
        }
    });
    
    // Seçenek butonları
    const optionBtns = document.querySelectorAll('.option-btn');
    
    optionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Aynı gruptaki diğer butonları bul
            const parentDiv = this.parentElement;
            const buttons = parentDiv.querySelectorAll('.option-btn');
            
            // Aktif butonları güncelle
            buttons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Tab'lar
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Aktif tab butonunu güncelle
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Aktif tab içeriğini güncelle
            const tabId = this.dataset.tab;
            tabPanes.forEach(pane => pane.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Sepete ekle butonu
    const addToCartBtn = document.querySelector('.add-to-cart');
    
    addToCartBtn.addEventListener('click', function() {
        alert('Ürün sepetinize eklendi!');
    });
});
</script>

<?php include 'includes/footer.php'; ?>