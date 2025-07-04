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
$all_colors = get_colors(); // Performans için renkleri bir kez çek
$all_sizes = get_sizes();   // Performans için bedenleri bir kez çek

// Ürün bilgileri veritabanından çekildi
?>
<?php include 'includes/header.php'; ?>

<?php
// Ürün bilgilerini al
$product = $product_data;

// Ürün özelliklerini hazırla
$features = !empty($product['features']) ? explode("\n", $product['features']) : [];

// Renkleri hazırla (Optimize Edilmiş)
$colors = [];
$product_color_ids = array_unique(array_column($product_variants, 'color_id'));
$all_colors_map = array_column($all_colors, null, 'id'); // Renkleri ID'ye göre haritala

foreach ($product_color_ids as $color_id) {
    if (isset($all_colors_map[$color_id])) {
        $colors[] = $all_colors_map[$color_id];
    }
}

// Bedenleri hazırla (Optimize Edilmiş)
$sizes = [];
$product_size_ids = array_unique(array_column($product_variants, 'size_id'));
$all_sizes_map = array_column($all_sizes, null, 'id'); // Bedenleri ID'ye göre haritala

foreach ($product_size_ids as $size_id) {
    if (isset($all_sizes_map[$size_id])) {
        $sizes[] = $all_sizes_map[$size_id];
    }
}
// Bedenleri değere göre sırala
usort($sizes, function($a, $b) {
    return strnatcmp($a['size_value'], $b['size_value']);
});
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
    <div class="max-w-6xl mx-auto px-5">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Sol Taraf - Ürün Görselleri -->
            <div class="lg:col-span-1 space-y-4">
                <div class="main-image bg-gray-100 rounded-lg overflow-hidden aspect-square">
                    <img id="main-product-image"
                         src="<?php echo isset($product_images[0]['image_url']) ? $product_images[0]['image_url'] : ''; ?>"
                         alt="<?php echo isset($product_images[0]['alt_text']) ? $product_images[0]['alt_text'] : $product['name']; ?>"
                         class="w-full h-full object-cover">
                </div>
                
                <div class="thumbnail-images grid grid-cols-4 gap-4">
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
            <div class="lg:col-span-2 space-y-5">
                <div>
                    <h1 class="text-2xl font-bold text-secondary mb-2"><?php echo $product['name']; ?></h1>
                    <p class="text-gray-600"><?php echo $product['category_name']; ?></p>
                </div>
                
                <div class="price-section">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-bold text-secondary">₺ <?php echo number_format($product['base_price'], 2); ?></span>
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
                            <span class="text-lg text-gray-400 line-through">₺ <?php echo number_format($original_price, 2); ?></span>
                            <span class="bg-green-500 text-white text-sm px-2 py-1 rounded">İndirim</span>
                        <?php endif; ?>
                    </div>
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
                    <div class="flex flex-wrap" style="max-width: 320px;">
                        <?php foreach($sizes as $size): ?>
                            <button class="size-option w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:border-primary hover:bg-primary hover:text-white transition-all font-medium m-1"
                                    data-size="<?php echo $size['id']; ?>"
                                    data-size-value="<?php echo $size['size_value']; ?>">
                                <?php echo $size['size_value']; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Seçili beden: <span id="selected-size">-</span></p>
                </div>
                

                <!-- Aksiyon Butonları -->
                <div class="action-buttons space-y-3 mt-4">
                    <button id="add-to-cart" 
                            class="w-1/2 bg-primary text-white py-3 rounded text-sm font-bold hover:bg-opacity-90 hover:shadow-md transition duration-300 disabled:bg-gray-200 disabled:cursor-not-allowed flex items-center justify-center gap-2" 
                            disabled
                            aria-label="Ürünü sepete ekle" 
                            aria-describedby="cart-button-description"
                            title="Sepete eklemek için renk ve beden seçimi yapmalısınız">
                        <span id="add-to-cart-text">Seçim Yapın</span>
                        <span id="loading-indicator" class="hidden">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                    <span id="cart-button-description" class="sr-only">Sepete eklemek için önce renk ve beden seçimi yapmalısınız</span>
                </div>
                
                <!-- Stok Durumu -->
                <div class="stock-info">
                    <p class="text-xs text-green-600" id="stock-status"></p>
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
                </nav>
            </div>
            
            <div class="tab-content mt-8">
                <div id="description" class="tab-pane">
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed"><?php echo $product['description']; ?></p>
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
            <!-- Benzer Ürünler (Optimize Edilmiş) -->
            <?php
            $category_slug = isset($product['category_slug']) ? $product['category_slug'] : null;
            $similar_products = [];
            if ($category_slug) {
                // Mevcut ürün hariç, aynı kategoriden 5 ürün getir (4 tane göstermek için)
                $all_products = get_product_models(5, 0, $category_slug, null);
                
                foreach($all_products as $p) {
                    if (count($similar_products) >= 4) break;
                    if ($p['id'] != $product_id) {
                        $similar_products[] = $p;
                    }
                }
            }
            
            foreach($similar_products as $p): 
            ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
                    <div class="aspect-square bg-gray-100">
                        <img src="<?php echo isset($p['image_url']) ? $p['image_url'] : ''; ?>"
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
    
    // Sayfa yüklendiğinde ilk rengi seç
    if (productColors.length > 0) {
        selectedColor = productColors[0].id;
    }
    
    // Stok olmayan bedenlerin üstünü çiz
    function updateSizeButtonsBasedOnStock() {
        if (!selectedColor) return;
        
        document.querySelectorAll('.size-option').forEach(button => {
            const sizeId = parseInt(button.dataset.size);
            const variant = productVariants.find(v => 
                v.color_id === selectedColor && v.size_id === sizeId
            );
            
            if (!variant || variant.stock_quantity <= 0) {
                button.classList.add('line-through', 'opacity-50');
                button.disabled = true;
            } else {
                button.classList.remove('line-through', 'opacity-50');
                button.disabled = false;
            }
        });
    }
    
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
            
            // Önce bedenlerin görünümünü güncelle
            updateSizeButtonsBasedOnStock();
            updateStockStatus();
        });
    });
    
    // Sayfa yüklendiğinde bedenleri güncelle
    if (selectedColor) {
        updateSizeButtonsBasedOnStock();
    }
    
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
            document.getElementById('selected-size').textContent = this.dataset.sizeValue;
            
            updateStockStatus();
        });
    });
    
    // Stok durumunu güncelle
    function updateStockStatus() {
        const addToCartBtn = document.getElementById('add-to-cart');
        const addToCartText = document.getElementById('add-to-cart-text');
        const stockStatus = document.getElementById('stock-status');
        
        if (selectedColor && selectedSize) {
            const variant = productVariants.find(v => 
                v.color_id === selectedColor && v.size_id === selectedSize
            );
            
            if (variant && variant.stock_quantity > 0) {
                stockStatus.textContent = variant.stock_quantity <= 3 ? 'Son ' + variant.stock_quantity + ' ürün!' : ''; 
                stockStatus.className = 'text-xs text-green-600';
                addToCartBtn.disabled = false;
                addToCartBtn.title = 'Ürünü sepete eklemek için tıklayın';
                addToCartText.textContent = 'Sepete Ekle';
            } else {
                stockStatus.textContent = 'Tükendi';
                stockStatus.className = 'text-xs text-red-600';
                addToCartBtn.disabled = true;
                addToCartBtn.title = 'Bu ürün tükenmiştir';
                addToCartText.textContent = 'Stokta Yok';
            }
        } else {
            stockStatus.textContent = '';
            stockStatus.className = 'text-xs text-gray-600';
            addToCartBtn.disabled = true;
            addToCartBtn.title = 'Sepete eklemek için renk ve beden seçimi yapmalısınız';
            addToCartText.textContent = 'Seçim Yapın';
        }
    }
    
    
    // Sepete ekle
    document.getElementById('add-to-cart').addEventListener('click', function() {
        if (selectedColor && selectedSize) {
            const colorName = productColors.find(c => c.id === selectedColor).name;
            const sizeValue = document.getElementById('selected-size').textContent;
            
            // Yükleme göstergesini göster
            const loadingIndicator = document.getElementById('loading-indicator');
            const addToCartText = document.getElementById('add-to-cart-text');
            
            loadingIndicator.classList.remove('hidden');
            this.disabled = true;
            addToCartText.textContent = 'Ekleniyor...';
            
            // Sepete ekleme işlemini simüle et (gerçek uygulamada AJAX isteği olabilir)
            setTimeout(() => {
                loadingIndicator.classList.add('hidden');
                this.disabled = false;
                addToCartText.textContent = 'Sepete Ekle';
                
                // Başarılı mesajı göster
                alert(`Sepete eklendi:\n<?php echo $product['name']; ?>\nRenk: ${colorName}\nBeden: ${sizeValue}`);
                
                // Stok durumunu güncelle (gerçek uygulamada API'den güncel stok bilgisi alınabilir)
                updateStockStatus();
            }, 800); // 800ms gecikme ile işlemi simüle et
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
