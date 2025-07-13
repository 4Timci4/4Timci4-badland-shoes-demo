<?php


require_once 'config/auth.php';
check_admin_auth();


$page_title = 'Favori Raporları';
$breadcrumb_items = [
    ['title' => 'Favori Raporları', 'url' => 'favorited-products.php', 'icon' => 'fas fa-heart']
];


$favorited_products = get_favorited_variants_summary();


$total_products = count($favorited_products);
$total_favorites = 0;
foreach ($favorited_products as $product) {
    $total_favorites += $product['total_favorites'];
}


include 'includes/header.php';
?>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 pb-4 border-b border-gray-200">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Favori Raporları</h1>
        <p class="mt-1 text-sm text-gray-600">En çok favoriye alınan ürünler ve varyantları</p>

        <div class="mt-4 flex flex-col sm:flex-row gap-4 text-sm text-gray-600">
            <span><strong class="font-semibold text-gray-900"><?= $total_products ?></strong> ürün</span>
            <span><strong class="font-semibold text-gray-900"><?= $total_favorites ?></strong> toplam favori</span>
        </div>
    </div>

    <div class="mb-6">
        <input type="text" placeholder="Ürün ara..." id="searchInput" onkeyup="filterProducts()"
            class="w-full sm:w-80 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="divide-y divide-gray-200">
            <?php if (empty($favorited_products)): ?>
                <div class="p-12 text-center text-gray-500">
                    <p>Henüz favoriye eklenen ürün bulunmuyor.</p>
                </div>
            <?php else: ?>
                <?php foreach ($favorited_products as $index => $product): ?>
                    <div class="p-4 sm:p-6 product-item"
                        data-name="<?= strtolower(htmlspecialchars($product['product_name'])) ?>">
                        <div class="flex flex-col sm:flex-row items-start gap-4">
                            <div
                                class="rank flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-semibold text-sm <?= $index < 3 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                                <?= $index + 1 ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars($product['product_name']) ?>
                                </h3>
                                <div class="text-xs text-gray-500 mb-3">ID: <?= $product['product_id'] ?></div>

                                <div class="flex flex-wrap gap-2">
                                    <?php foreach (array_slice($product['variants'], 0, 6) as $variant): ?>
                                        <div
                                            class="inline-flex items-center px-2.5 py-1 bg-gray-100 rounded-full text-xs text-gray-700">
                                            <div class="w-3 h-3 rounded-full mr-2 border border-gray-300"
                                                style="background-color: <?= htmlspecialchars($variant['color_hex']) ?>">
                                            </div>
                                            <?= htmlspecialchars($variant['color_name']) ?> /
                                            <?= htmlspecialchars($variant['size_name']) ?>
                                            (<?= $variant['favorite_count'] ?>)
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (count($product['variants']) > 6): ?>
                                        <div
                                            class="inline-flex items-center px-2.5 py-1 bg-gray-100 rounded-full text-xs text-gray-700">
                                            +<?= count($product['variants']) - 6 ?> daha
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                                <div class="text-center sm:text-right">
                                    <span
                                        class="text-xl sm:text-2xl font-bold text-gray-900"><?= $product['total_favorites'] ?></span>
                                    <span class="text-xs text-gray-500 block">favori</span>
                                </div>
                                <div class="flex gap-2 w-full sm:w-auto">
                                    <a href="product-edit.php?id=<?= $product['product_id'] ?>"
                                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium"
                                        title="Düzenle">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function filterProducts() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const items = document.querySelectorAll('.product-item');

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>