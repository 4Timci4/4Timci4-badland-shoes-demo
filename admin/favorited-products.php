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

<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e0e0e0;
    }

    .title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin: 0 0 8px 0;
    }

    .subtitle {
        color: #666;
        font-size: 14px;
        margin: 0;
    }

    .stats {
        display: flex;
        gap: 40px;
        margin: 20px 0;
        font-size: 14px;
        color: #666;
    }

    .search {
        margin: 20px 0;
    }

    .search input {
        width: 300px;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .search input:focus {
        outline: none;
        border-color: #333;
    }

    .product-list {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }

    .product-item {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-item:hover {
        background: #fafafa;
    }

    .product-main {
        flex: 1;
    }

    .product-name {
        font-size: 16px;
        font-weight: 500;
        color: #333;
        margin: 0 0 4px 0;
    }

    .product-id {
        font-size: 12px;
        color: #999;
        margin: 0 0 12px 0;
    }

    .variants {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .variant {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        background: #f5f5f5;
        border-radius: 12px;
        font-size: 12px;
        color: #666;
    }

    .color-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 6px;
        border: 1px solid #ddd;
    }

    .favorite-count {
        text-align: right;
        min-width: 80px;
    }

    .favorite-number {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        display: block;
    }

    .favorite-label {
        font-size: 12px;
        color: #999;
    }

    .rank {
        width: 24px;
        height: 24px;
        background: #f0f0f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 500;
        color: #666;
        margin-right: 16px;
        flex-shrink: 0;
    }

    .rank.top-3 {
        background: #333;
        color: white;
    }

    .actions {
        display: flex;
        gap: 8px;
        margin-left: 20px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border: 1px solid #ddd;
        background: white;
        color: #666;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 12px;
    }

    .action-btn:hover {
        background: #f5f5f5;
        color: #333;
    }

    .empty {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
</style>

<div class="container">
    <div class="header">
        <h1 class="title">Favori Raporları</h1>
        <p class="subtitle">En çok favoriye alınan ürünler ve varyantları</p>

        <div class="stats">
            <span><strong><?= $total_products ?></strong> ürün</span>
            <span><strong><?= $total_favorites ?></strong> toplam favori</span>
        </div>
    </div>

    <div class="search">
        <input type="text" placeholder="Ürün ara..." id="searchInput" onkeyup="filterProducts()">
    </div>

    <div class="product-list">
        <?php if (empty($favorited_products)): ?>
            <div class="empty">
                <p>Henüz favoriye eklenen ürün bulunmuyor.</p>
            </div>
        <?php else: ?>
            <?php foreach ($favorited_products as $index => $product): ?>
                <div class="product-item" data-name="<?= strtolower(htmlspecialchars($product['product_name'])) ?>">
                    <div class="rank <?= $index < 3 ? 'top-3' : '' ?>">
                        <?= $index + 1 ?>
                    </div>

                    <div class="product-main">
                        <h3 class="product-name"><?= htmlspecialchars($product['product_name']) ?></h3>
                        <div class="product-id">ID: <?= $product['product_id'] ?></div>

                        <div class="variants">
                            <?php foreach (array_slice($product['variants'], 0, 6) as $variant): ?>
                                <div class="variant">
                                    <div class="color-dot" style="background-color: <?= htmlspecialchars($variant['color_hex']) ?>">
                                    </div>
                                    <?= htmlspecialchars($variant['color_name']) ?> / <?= htmlspecialchars($variant['size_name']) ?>
                                    (<?= $variant['favorite_count'] ?>)
                                </div>
                            <?php endforeach; ?>

                            <?php if (count($product['variants']) > 6): ?>
                                <div class="variant">
                                    +<?= count($product['variants']) - 6 ?> daha
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="actions">
                        <a href="#" class="action-btn" title="Görüntüle">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="#" class="action-btn" title="Düzenle">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>

                    <div class="favorite-count">
                        <span class="favorite-number"><?= $product['total_favorites'] ?></span>
                        <span class="favorite-label">favori</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function filterProducts() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const items = document.querySelectorAll('.product-item[data-name]');

        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>