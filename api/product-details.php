<?php
// Ürün ID'sini al
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Veritabanı bağlantısı
require_once 'config/database.php';

// Veritabanından ürün bilgilerini çek
$product_data_result = get_product_model($product_id);
$product_data = $product_data_result ? $product_data_result[0] : null;

// Eğer ürün bulunamazsa geri yönlendir
if (!$product_data) {
    header("Location: products.php");
    exit;
}

// Ürün verileri bulunduktan sonra diğer verileri çek
$product_variants = get_product_variants($product_id);
$product_images = get_product_images($product_id);

// Ürün bilgileri veritabanından çekildi
?>
<?php include 'includes/header.php'; ?>

<?php
// Ürün bilgilerini al
$product = $product_data;

// Ürün özelliklerini hazırla
$features = explode("\n", $product['description']);
if (count($features) < 3) {
    $features = [
        'Yüksek kaliteli malzeme',
        'Konforlu iç yapı',
        'Dayanıklı dış taban',
        'Modern tasarım',
        'Günlük kullanım için ideal'
    ];
}

// Renkleri hazırla
$colors = [];
foreach ($product_variants as $variant) {
    $color_id = $variant['color_id'];
    $color_info = null;
    
    // Renk bilgisini bul
    foreach (get_colors() as $c) {
        if ($c['id'] == $color_id) {
            $color_info = $c;
            break;
        }
    }
    
    if ($color_info && !in_array($color_info, $colors)) {
        $colors[] = $color_info;
    }
}

// Bedenleri hazırla
$sizes = [];
foreach ($product_variants as $variant) {
    $size = $variant['size_id'];
    if (!in_array($size, $sizes)) {
        $sizes[] = $size;
    }
}
?>

<!-- Breadcrumb -->
<section class="bg-gray-50 py-4 border-b">
    <div class="max-w-7xl mx-auto px-5">
        <nav class="text-sm">
            <ol class="flex items-center space-x-2 text-gray-500">
                <li><a href="/" class="hover:text-primary transition-colors">Ana Sayfa</a></li>
                <li class="text-gray-400">></li>
                <li><a href="/products.php" class="hover:text-primary transition-colors">Ürünler</a></li>
                <li class="text-gray-400">></li>
                <li class="text-secondary font-medium"><?php echo $product['name']; ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Ürün Detay -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Sol Taraf - Ürün Görselleri -->
            <div class="space-y-4">
                <div class="main-image bg-gray-100 rounded-lg overflow-hidden aspect-square">
                    <img id="main-product-image"
                         src="<?php echo isset($product_images[0]['image_url']) ? $product_images[0]['image_url'] : ''; ?>"
                         alt="<?php echo isset($product_images[0]['alt_text']) ? $product_images[0]['alt_text'] : $product['name']; ?>"
                         class="w-full h-full object-cover">
                </div>
                
                <div class="thumbnail-images grid grid-cols-3 gap-3">
                    <?php foreach($product_images as $index => $image): ?>
                        <div class="thumbnail bg-gray-100 rounded-lg overflow-hidden aspect-square cursor-pointer border-2 <?php echo $index === 0 ? 'border-primary' : 'border-transparent hover:border-gray-300'; ?>"
                             onclick="changeMainImage('<?php echo $image['image_url']; ?>', this)">
                            <img src="<?php echo $image['image_url']; ?>"
                                 alt="<?php echo isset($image['alt_text']) ? $image['alt_text'] : $product['name']; ?>"
                                 class="w-full h-full object-cover">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Sağ Taraf - Ürün Bilgileri -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-3xl font-bold text-secondary mb-2"><?php echo $product['name']; ?></h1>
                    <p class="text-gray-600"><?php echo $product['category_name']; ?></p>
                </div>
                
                <div class="price-section">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl font-bold text-secondary">₺ <?php echo number_format($product['base_price'], 2); ?></span>
                        <?php
                        $has_discount = false;
                        $original_price = 0;
                        
                        foreach ($product_variants as $variant) {
                            if (isset($variant['original_price']) && $variant['original_price'] > 0) {
                                $has_discount = true;
                                $original_price = $variant['original_price'];
                                break;
                            }
                        }
                        
                        if ($has_discount):
                        ?>
                            <span class="text-xl text-gray-400 line-through">₺ <?php echo number_format($original_price, 2); ?></span>
                            <span class="bg-green-500 text-white text-sm px-2 py-1 rounded">İndirim</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="description">
                    <p class="text-gray-700 leading-relaxed"><?php echo $product['description']; ?></p>
                </div>
                
                <!-- Renk Seçimi -->
                <div class="color-selection">
                    <h3 class="text-lg font-semibold text-secondary mb-3">Renk Seçimi:</h3>
                    <div class="flex gap-3">
                        <?php foreach($colors as $index => $color): ?>
                            <button class="color-option w-10 h-10 rounded-full border-2 <?php echo $index === 0 ? 'border-secondary' : 'border-gray-300'; ?> hover:border-secondary transition-colors"
                                    style="background-color: <?php echo $color['hex_code']; ?>"
                                    data-color-id="<?php echo $color['id']; ?>"
                                    data-color-name="<?php echo $color['name']; ?>"
                                    title="<?php echo $color['name']; ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Seçili renk: <span id="selected-color"><?php echo !empty($colors) ? $colors[0]['name'] : '-'; ?></span></p>
                </div>
                
                <!-- Beden Seçimi -->
                <div class="size-selection">
                    <h3 class="text-lg font-semibold text-secondary mb-3">Beden Seçimi:</h3>
                    <div class="grid grid-cols-4 gap-3">
                        <?php foreach($sizes as $size): ?>
                            <button class="size-option px-4 py-3 border border-gray-300 rounded-lg hover:border-primary hover:bg-primary hover:text-white transition-all text-center font-medium"
                                    data-size="<?php echo $size; ?>">
                                <?php echo $size; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Seçili beden: <span id="selected-size">-</span></p>
                </div>
                
                <!-- Miktar -->
                <div class="quantity-section">
                    <h3 class="text-lg font-semibold text-secondary mb-3">Miktar:</h3>
                    <div class="flex items-center gap-3">
                        <button id="decrease-qty" class="w-10 h-10 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="10" class="w-16 h-10 text-center border border-gray-300 rounded-lg">
                        <button id="increase-qty" class="w-10 h-10 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">+</button>
                    </div>
                </div>
                
                <!-- Stok Durumu -->
                <div class="stock-info">
                    <p class="text-sm text-green-600" id="stock-status">Stok durumu: Seçim yapın</p>
                </div>
                
                <!-- Aksiyon Butonları -->
                <div class="action-buttons space-y-3">
                    <button id="add-to-cart" class="w-full bg-primary text-white py-4 rounded-lg font-semibold hover:bg-pink-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed" disabled>
                        Sepete Ekle
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Ürün Detay Tabları -->
        <div class="mt-16">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8">
                    <button class="tab-button py-4 px-1 border-b-2 border-primary text-primary font-medium" data-tab="description">
                        Açıklama
                    </button>
                    <button class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="features">
                        Özellikler
                    </button>
                    <button class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="reviews">
                        Yorumlar
                    </button>
                </nav>
            </div>
            
            <div class="tab-content mt-8">
                <div id="description" class="tab-pane">
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?php echo $product['description']; ?></p>
                        <p class="text-gray-700 leading-relaxed mt-4">
                            Bu ürün, yüksek kaliteli malzemeler kullanılarak üretilmiştir. Günlük kullanım için ideal olan bu ayakkabı, 
                            hem konfor hem de stil arayanlar için mükemmel bir seçimdir.
                        </p>
                    </div>
                </div>
                
                <div id="features" class="tab-pane hidden">
                    <ul class="space-y-3">
                        <?php foreach($features as $feature): ?>
                            <li class="flex items-center gap-3">
                                <i class="fas fa-check text-green-500"></i>
                                <span class="text-gray-700"><?php echo $feature; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div id="reviews" class="tab-pane hidden">
                    <div class="space-y-6">
                        <!-- Örnek Yorumlar -->
                        <div class="border-b border-gray-200 pb-6">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-secondary">Ahmet Y.</h4>
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <span class="text-sm text-gray-500 ml-2">5.0</span>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500 ml-auto">2 gün önce</span>
                            </div>
                            <p class="text-gray-700">Çok kaliteli bir ürün. Tam beklediğim gibi çıktı. Uygun fiyata yüksek kalite. Kesinlikle tavsiye ederim.</p>
                        </div>
                        
                        <div class="border-b border-gray-200 pb-6">
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-secondary">Zeynep K.</h4>
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="far fa-star text-gray-300"></i>
                                        <span class="text-sm text-gray-500 ml-2">4.0</span>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500 ml-auto">1 hafta önce</span>
                            </div>
                            <p class="text-gray-700">Ürün güzel ancak kargo biraz geç geldi. Kalitesi ve işçiliği beklediğimden iyi çıktı.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benzer Ürünler -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-5">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-secondary mb-4">Benzer Ürünler</h2>
            <p class="text-gray-600">Beğenebileceğiniz diğer modellerimiz</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            // Benzer ürünleri göster (aynı kategoriden)
            $category_id = isset($product['category_id']) ? $product['category_id'] : 0;
            $similar_products = [];
            
            // Aynı kategorideki diğer ürünleri getir
            $all_products = get_product_models(4, 0, null, null);
            
            foreach($all_products as $p) {
                if ($p['id'] != $product_id && isset($p['category_id']) && $p['category_id'] == $category_id) {
                    $similar_products[] = $p;
                }
                if (count($similar_products) >= 4) break;
            }
            
            foreach($similar_products as $p): 
            ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                    <div class="aspect-square bg-gray-100">
                        <?php
                        // Ürün görseli
                        $product_image = '';
                        $product_images_data = get_product_images($p['id']);
                        if (!empty($product_images_data)) {
                            $product_image = $product_images_data[0]['image_url'];
                        }
                        ?>
                        <img src="<?php echo $product_image; ?>"
                             alt="<?php echo $p['name']; ?>"
                             class="w-full h-full object-cover">
                    </div>
                    <div class="p-4 text-center">
                        <h3 class="font-semibold text-secondary mb-2"><?php echo $p['name']; ?></h3>
                        <div class="text-xl font-bold text-secondary">₺ <?php echo number_format($p['base_price'], 2); ?></div>
                        <a href="/product-details.php?id=<?php echo $p['id']; ?>"
                           class="inline-block mt-3 bg-primary text-white px-6 py-2 rounded-lg hover:bg-pink-600 transition-colors mx-auto text-center w-full">
                            Ürün Detayı
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ürün varyantları
    const productVariants = <?php echo json_encode($product_variants); ?>;
    const productColors = <?php echo json_encode($colors); ?>;
    
    let selectedColor = null;
    let selectedSize = null;
    
    // Ana resim değiştirme
    window.changeMainImage = function(imageUrl, thumbnail) {
        document.getElementById('main-product-image').src = imageUrl;
        
        // Thumbnail border'larını güncelle
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('border-primary');
            thumb.classList.add('border-transparent');
        });
        thumbnail.classList.remove('border-transparent');
        thumbnail.classList.add('border-primary');
    };
    
    // Renk seçimi
    document.querySelectorAll('.color-option').forEach(button => {
        button.addEventListener('click', function() {
            // Önceki seçimi temizle
            document.querySelectorAll('.color-option').forEach(btn => {
                btn.classList.remove('border-secondary');
                btn.classList.add('border-gray-300');
            });
            
            // Yeni seçimi işaretle
            this.classList.remove('border-gray-300');
            this.classList.add('border-secondary');
            
            selectedColor = parseInt(this.dataset.colorId);
            document.getElementById('selected-color').textContent = this.dataset.colorName;
            
            updateStockStatus();
        });
    });
    
    // Beden seçimi
    document.querySelectorAll('.size-option').forEach(button => {
        button.addEventListener('click', function() {
            // Önceki seçimi temizle
            document.querySelectorAll('.size-option').forEach(btn => {
                btn.classList.remove('bg-primary', 'text-white', 'border-primary');
                btn.classList.add('border-gray-300');
            });
            
            // Yeni seçimi işaretle
            this.classList.remove('border-gray-300');
            this.classList.add('bg-primary', 'text-white', 'border-primary');
            
            selectedSize = parseInt(this.dataset.size);
            document.getElementById('selected-size').textContent = selectedSize;
            
            updateStockStatus();
        });
    });
    
    // Stok durumunu güncelle
    function updateStockStatus() {
        const addToCartBtn = document.getElementById('add-to-cart');
        const stockStatus = document.getElementById('stock-status');
        
        if (selectedColor && selectedSize) {
            const variant = productVariants.find(v => 
                v.color_id === selectedColor && v.size === selectedSize
            );
            
            if (variant && variant.stock > 0) {
                stockStatus.textContent = `Stok durumu: ${variant.stock} adet mevcut`;
                stockStatus.className = 'text-sm text-green-600';
                addToCartBtn.disabled = false;
                addToCartBtn.textContent = 'Sepete Ekle';
            } else {
                stockStatus.textContent = 'Stok durumu: Tükendi';
                stockStatus.className = 'text-sm text-red-600';
                addToCartBtn.disabled = true;
                addToCartBtn.textContent = 'Stokta Yok';
            }
        } else {
            stockStatus.textContent = 'Stok durumu: Renk ve beden seçin';
            stockStatus.className = 'text-sm text-gray-600';
            addToCartBtn.disabled = true;
            addToCartBtn.textContent = 'Seçim Yapın';
        }
    }
    
    // Miktar kontrolleri
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-qty');
    const increaseBtn = document.getElementById('increase-qty');
    
    decreaseBtn.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });
    
    increaseBtn.addEventListener('click', function() {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue < 10) {
            quantityInput.value = currentValue + 1;
        }
    });
    
    // Sepete ekle
    document.getElementById('add-to-cart').addEventListener('click', function() {
        if (selectedColor && selectedSize) {
            const quantity = parseInt(quantityInput.value);
            const colorName = productColors.find(c => c.id === selectedColor).name;
            
            alert(`Sepete eklendi:\n${quantity} adet <?php echo $product['name']; ?>\nRenk: ${colorName}\nBeden: ${selectedSize}`);
        }
    });
    
    // Tab sistemi
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Tüm tab butonlarını pasif yap
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Aktif tab butonunu işaretle
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-primary', 'text-primary');
            
            // Tüm tab içeriklerini gizle
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });
            
            // Seçili tab içeriğini göster
            document.getElementById(tabId).classList.remove('hidden');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
