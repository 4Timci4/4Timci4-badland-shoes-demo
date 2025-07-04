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
