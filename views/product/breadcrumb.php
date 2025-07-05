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
