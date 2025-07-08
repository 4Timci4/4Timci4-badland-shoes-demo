<!-- Gizli dosya yükleme alanı -->
<input type="file" id="hidden-file-input" name="product_images[]" multiple accept="image/*" class="hidden">

<!-- Product Images Management Card -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Ürün Görselleri</h3>
                <p class="text-gray-600 text-sm">Görsel yükleme ve yönetim</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-blue-600"><?= count($productImages ?? []) ?></div>
                <div class="text-xs text-gray-500">Toplam Görsel</div>
            </div>
        </div>
    </div>
    
    <!-- Yükleme formu (varsayılan olarak gizli) -->
    <div id="upload-container" class="hidden border-b border-gray-100 p-5 bg-gray-50">
        <form id="image-upload-form" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="upload_images">
            
            <div class="flex items-center gap-5">
                <div class="w-1/3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Renk Seçimi <span class="text-gray-400 font-normal">(Opsiyonel)</span>
                    </label>
                    <select name="color_id" id="color-select" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        <option value="">Tüm Renkler</option>
                        <?php foreach ($all_colors as $color): ?>
                            <option value="<?= $color['id'] ?>">
                                <?= htmlspecialchars($color['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="w-1/3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Seçilen Görsel Sayısı
                    </label>
                    <div class="flex items-center gap-2">
                        <span id="selected-file-count" class="inline-flex items-center justify-center px-3 py-2 bg-blue-100 text-blue-800 font-medium rounded-lg min-w-[40px] text-center">0</span>
                        <button type="button" id="select-more-files" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                            <i class="fas fa-folder-open mr-1"></i> Dosya Seç
                        </button>
                    </div>
                </div>
                
                <div class="w-1/3 flex items-end space-x-2">
                    <button type="button" id="cancel-upload" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-times mr-1"></i> İptal
                    </button>
                    
                    <button type="submit" class="flex-1 px-5 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 shadow-sm hover:shadow-md transition-all duration-200">
                        <i class="fas fa-upload mr-2"></i> Yükle
                    </button>
                </div>
            </div>
            
            <div id="image-preview" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mt-4"></div>
        </form>
    </div>
    
    <div class="p-6 space-y-6">
        <?php if (!empty($productImagesByColor)): ?>
            <!-- Color Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex space-x-6 overflow-x-auto pb-2" id="colorTabs">
                    <?php $tab_index = 0; ?>
                    <?php foreach ($productImagesByColor as $color_id => $images): ?>
                        <?php
                        $color_name = 'Genel';
                        if ($color_id !== 'default') {
                            foreach ($all_colors as $color) {
                                if ($color['id'] == $color_id) {
                                    $color_name = $color['name'];
                                    break;
                                }
                            }
                        }
                        ?>
                        <button type="button" class="color-tab-btn py-3 px-4 text-sm font-medium border-b-2 transition-all duration-200 whitespace-nowrap <?= $tab_index === 0 ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>" 
                                onclick="switchColorTab('<?= $color_id ?>', this)"
                                data-color="<?= $color_id ?>">
                            <?= htmlspecialchars($color_name) ?>
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $tab_index === 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= count($images) ?>
                            </span>
                        </button>
                        <?php $tab_index++; ?>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Tab Contents -->
            <div id="colorTabsContent">
                <?php $tab_index = 0; ?>
                <?php foreach ($productImagesByColor as $color_id => $images): ?>
                    <?php
                    $color_name = 'Genel';
                    if ($color_id !== 'default') {
                        foreach ($all_colors as $color) {
                            if ($color['id'] == $color_id) {
                                $color_name = $color['name'];
                                break;
                            }
                        }
                    }
                    
                    // Sort images by sort_order
                    usort($images, function($a, $b) {
                        return ($a['sort_order'] ?? 999) - ($b['sort_order'] ?? 999);
                    });
                    ?>
                    <div class="color-tab-content <?= $tab_index === 0 ? 'block' : 'hidden' ?>" 
                         id="color-<?= $color_id ?>" 
                         data-color="<?= $color_id ?>">
                        
                        <?php if (count($images) > 1): ?>
                            <div class="mb-4 flex justify-end space-x-2">
                                <button type="button" 
                                        id="save-order-btn-<?= $color_id ?>" 
                                        class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 font-medium rounded-lg hover:bg-green-200 transition-colors"
                                        onclick="saveImageOrder('<?= $color_id ?>')">
                                    <i class="fas fa-save mr-2"></i>
                                    Sıralamayı Kaydet
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Image Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="images-<?= $color_id ?>">
                            <?php $image_index = 0; ?>
                            <?php foreach ($images as $image): ?>
                                <?php $image_index++; ?>
                                <div class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-200" 
                                     data-image-id="<?= $image['id'] ?>">
                                    <div class="relative">
                                        <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                                             class="w-full h-48 object-cover rounded-t-xl"
                                             alt="<?= htmlspecialchars($image['alt_text'] ?? '') ?>">
                                        
                                        <!-- Primary Badge -->
                                        <?php if ($image['is_primary']): ?>
                                            <div class="absolute top-3 left-3">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                    <i class="fas fa-star mr-1"></i> Ana Görsel
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Action Buttons -->
                                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <div class="flex flex-col space-y-1">
                                                <?php
                                                    $image_url = $image['image_url'] ?? '';
                                                    $original_url = str_replace('/optimized/', '/original/', $image_url);
                                                    $original_url = preg_replace('/_optimized(\..+?)$/', '$1', $original_url);
                                                ?>
                                                <button type="button" 
                                                        class="w-8 h-8 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                        onclick="viewImage('<?= htmlspecialchars($original_url) ?>')"
                                                        title="Büyük Görüntüle">
                                                    <i class="fas fa-search-plus text-sm"></i>
                                                </button>
                                                <button type="button" 
                                                        class="w-8 h-8 bg-white rounded-lg shadow-md flex items-center justify-center text-gray-600 hover:text-green-600 hover:bg-green-50 transition-colors"
                                                        onclick="downloadImage('<?= htmlspecialchars($original_url) ?>')"
                                                        title="İndir">
                                                    <i class="fas fa-download text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Card Body -->
                                    <div class="p-4">
                                        <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                                            <span>Sıra: <span class="image-order"><?= $image_index ?></span></span>
                                            
                                            <?php if (count($images) > 1): ?>
                                                <div class="flex space-x-1">
                                                    <?php if ($image_index > 1): ?>
                                                        <button type="button" 
                                                                class="w-6 h-6 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-md"
                                                                onclick="moveImage(this, 'up')"
                                                                title="Yukarı Taşı">
                                                            <i class="fas fa-chevron-up text-xs"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($image_index < count($images)): ?>
                                                        <button type="button" 
                                                                class="w-6 h-6 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-md"
                                                                onclick="moveImage(this, 'down')"
                                                                title="Aşağı Taşı">
                                                            <i class="fas fa-chevron-down text-xs"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="flex space-x-2">
                                            <?php if (!$image['is_primary']): ?>
                                                <button type="button" 
                                                        onclick="setPrimaryImage(<?= $image['id'] ?>)" 
                                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition-colors text-sm font-medium"
                                                        title="Ana Görsel Yap">
                                                    <i class="fas fa-star mr-1"></i>
                                                    Ana Yap
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="button" 
                                                    onclick="deleteImage(<?= $image['id'] ?>, this)" 
                                                    class="inline-flex items-center justify-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium"
                                                    title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                <span class="text-blue-800 text-sm">Görselleri sıralamak için yukarı/aşağı oklarını kullanın ve "Sıralamayı Kaydet" butonuna tıklayın.</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($images)): ?>
                            <div class="text-center py-8 bg-gray-50 rounded-xl">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-images text-gray-400 text-xl"></i>
                                </div>
                                <p class="text-gray-600">Bu kategoride henüz görsel bulunmuyor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php $tab_index++; ?>
                <?php endforeach; ?>
            </div>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="text-center py-8 bg-gray-50 rounded-xl">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-images text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Henüz görsel yüklenmemiş</h3>
                <p class="text-gray-600 mb-4">Bu ürün için henüz hiç görsel yüklenmemiş.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Upload Progress -->
    <div id="upload-progress" class="hidden px-6 py-4 border-t border-gray-200">
        <div class="flex items-center">
            <div class="w-full bg-gray-200 rounded-full mr-2 h-2.5">
                <div id="progress-bar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
            </div>
            <span id="progress-text" class="text-sm font-medium text-gray-500 min-w-[40px] text-right">0%</span>
        </div>
    </div>
</div>

<!-- Resim görüntüleme modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Görsel Önizleme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modal-image" src="" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Görsel Yönetimi - JavaScript
 */

// Global scope'ta tanımlanması gereken fonksiyonlar (onclick için)
function switchColorTab(colorId, element) {
    document.querySelectorAll('.color-tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('block');
    });
    
    const targetContent = document.getElementById('color-' + colorId);
    if (targetContent) {
        targetContent.classList.remove('hidden');
        targetContent.classList.add('block');
    }
    
    document.querySelectorAll('.color-tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
        btn.classList.add('border-transparent', 'text-gray-500');
        const badge = btn.querySelector('span');
        if (badge) {
            badge.classList.remove('bg-blue-100', 'text-blue-800');
            badge.classList.add('bg-gray-100', 'text-gray-800');
        }
    });
    
    element.classList.remove('border-transparent', 'text-gray-500');
    element.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
    const activeBadge = element.querySelector('span');
    if (activeBadge) {
        activeBadge.classList.remove('bg-gray-100', 'text-gray-800');
        activeBadge.classList.add('bg-blue-100', 'text-blue-800');
    }
}

function viewImage(url) {
    const modalImage = document.getElementById('modal-image');
    if (modalImage) {
        modalImage.src = url;
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        imageModal.show();
    }
}

function downloadImage(url) {
    const link = document.createElement('a');
    link.href = url;
    link.download = url.split('/').pop();
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function moveImage(button, direction) {
    const imageCard = button.closest('[data-image-id]');
    const container = imageCard.parentElement;
    const cards = Array.from(container.children);
    const currentIndex = cards.indexOf(imageCard);
    
    // Hareket ettirme işlemleri
    if (direction === 'up' && currentIndex > 0) {
        container.insertBefore(imageCard, cards[currentIndex - 1]);
    } else if (direction === 'down' && currentIndex < cards.length - 1) {
        container.insertBefore(imageCard, cards[currentIndex + 1].nextSibling);
    }
    
    // Sıra numaralarını güncelle
    updateImageOrder(container);
}

function updateImageOrder(container) {
    // Tüm görsel kartlarını seç ve sıra numaralarını güncelle
    const cards = Array.from(container.children);
    cards.forEach((card, index) => {
        const orderElement = card.querySelector('.image-order');
        if (orderElement) {
            orderElement.textContent = index + 1;
        }
        
        // Yukarı/aşağı butonlarını güncelle
        const upButton = card.querySelector('button[onclick*="moveImage"][onclick*="up"]');
        const downButton = card.querySelector('button[onclick*="moveImage"][onclick*="down"]');
        
        if (upButton) {
            upButton.style.display = index > 0 ? 'flex' : 'none';
        }
        if (downButton) {
            downButton.style.display = index < cards.length - 1 ? 'flex' : 'none';
        }
    });
}

function saveImageOrder(colorId) {
    const container = document.getElementById('images-' + colorId);
    if (!container) return;
    
    const cards = Array.from(container.children);
    const orderData = [];
    
    cards.forEach((card, index) => {
        const imageId = card.getAttribute('data-image-id');
        if (imageId) {
            orderData.push({
                id: parseInt(imageId),
                sort_order: index + 1
            });
        }
    });
    
    if (orderData.length === 0) return;
    
    const csrf_token = document.querySelector('input[name="csrf_token"]').value;
    const product_id = <?= $product_id ?>;
    
    showNotification('Sıralama kaydediliyor...', 'info');
    
    fetch('ajax/image-upload.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: csrf_token,
            action: 'reorder_images',
            order_data: orderData,
            product_id: product_id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Görsel sıralaması kaydedildi.', 'success');
        } else {
            showNotification(data.error || 'Sıralama kaydedilirken hata oluştu.', 'error');
        }
    })
    .catch(error => {
        console.error('Reorder Error:', error);
        showNotification('Bağlantı hatası oluştu.', 'error');
    });
}

function setPrimaryImage(imageId) {
    if (!confirm('Bu görseli ana görsel yapmak istediğinizden emin misiniz?')) return;
    
    const csrf_token = document.querySelector('input[name="csrf_token"]').value;
    const product_id = <?= $product_id ?>;
    
    showNotification('İşlem yapılıyor...', 'info');
    
    fetch('ajax/image-upload.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            'csrf_token': csrf_token,
            'action': 'set_primary',
            'image_id': imageId,
            'product_id': product_id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Ana görsel başarıyla güncellendi.', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.error || 'Bilinmeyen bir hata oluştu.', 'error');
        }
    })
    .catch(error => {
        console.error('Set Primary Image Error:', error);
        showNotification('Bağlantı hatası oluştu.', 'error');
    });
}

function deleteImage(imageId, buttonElement) {
    if (!confirm('Bu görseli silmek istediğinizden emin misiniz?')) return;

    const csrf_token = document.querySelector('input[name="csrf_token"]').value;
    const product_id = <?= $product_id ?>;
    const imageCard = buttonElement.closest('[data-image-id]');
    
    showNotification('Görsel siliniyor...', 'info');
    
    fetch('ajax/image-upload.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            'csrf_token': csrf_token,
            'action': 'delete_image',
            'image_id': imageId,
            'product_id': product_id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (imageCard) {
                imageCard.style.transition = 'all 0.3s ease';
                imageCard.style.opacity = '0';
                imageCard.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    const container = imageCard.parentElement;
                    imageCard.remove();
                    updateImageOrder(container);
                    updateTabCounts();
                    showNotification('Görsel başarıyla silindi.', 'success');
                }, 300);
            }
        } else {
            showNotification(data.error || 'Silme işlemi başarısız oldu.', 'error');
        }
    })
    .catch(error => {
        console.error('Delete Image Error:', error);
        showNotification('Bağlantı hatası oluştu.', 'error');
    });
}

function triggerFileInput(colorId = null) {
    const uploadContainer = document.getElementById('upload-container');
    const colorSelect = document.getElementById('color-select');
    const hiddenFileInput = document.getElementById('hidden-file-input');

    if (uploadContainer && colorSelect && hiddenFileInput) {
        if (colorId) {
            colorSelect.value = colorId;
        }
        uploadContainer.classList.remove('hidden');
        hiddenFileInput.click();
    }
}

function showNotification(message, type = 'info') {
    document.querySelectorAll('.alert-notification').forEach(alert => alert.remove());
    const typeClasses = {
        success: 'bg-green-50 border-green-200 text-green-800',
        error: 'bg-red-50 border-red-200 text-red-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    const iconClasses = {
        success: 'fas fa-check-circle text-green-500',
        error: 'fas fa-exclamation-triangle text-red-500',
        info: 'fas fa-info-circle text-blue-500'
    };
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert-notification fixed top-5 right-5 p-4 rounded-lg border shadow-lg z-50 flex items-center ${typeClasses[type] || typeClasses.info}`;
    alertDiv.innerHTML = `<i class="${iconClasses[type] || iconClasses.info} mr-3"></i><span>${message}</span>`;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transform = 'translateY(-20px)';
        alertDiv.style.transition = 'all 0.5s ease';
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}

function updateTabCounts() {
    document.querySelectorAll('.color-tab-btn').forEach(tab => {
        const colorId = tab.dataset.color;
        const tabContent = document.getElementById('color-' + colorId);
        if (tabContent) {
            const imageCount = tabContent.querySelectorAll('[data-image-id]').length;
            const countBadge = tab.querySelector('span');
            if (countBadge) {
                countBadge.textContent = imageCount;
            }
            if (imageCount === 0) {
                const emptyMessage = tabContent.querySelector('.text-center.py-8');
                if (!emptyMessage) {
                    const newEmptyMessage = document.createElement('div');
                    newEmptyMessage.className = 'text-center py-8 bg-gray-50 rounded-xl';
                    newEmptyMessage.innerHTML = `<div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-images text-gray-400 text-xl"></i></div><p class="text-gray-600">Bu kategoride henüz görsel bulunmuyor.</p>`;
                    tabContent.querySelector('.grid').appendChild(newEmptyMessage);
                }
            }
        }
    });
}

// DOM hazır olduğunda
document.addEventListener('DOMContentLoaded', function() {
    const hiddenFileInput = document.getElementById('hidden-file-input');
    const uploadContainer = document.getElementById('upload-container');
    const uploadForm = document.getElementById('image-upload-form');
    const selectMoreFilesBtn = document.getElementById('select-more-files');
    const cancelUploadBtn = document.getElementById('cancel-upload');
    const fileCountEl = document.getElementById('selected-file-count');
    const imagePreview = document.getElementById('image-preview');
    const progressContainer = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    function resetUploadPanel() {
        uploadContainer.classList.add('hidden');
        if(imagePreview) imagePreview.innerHTML = '';
        if(fileCountEl) fileCountEl.textContent = '0';
        hiddenFileInput.value = '';
    }
    
    if (selectMoreFilesBtn) {
        selectMoreFilesBtn.addEventListener('click', () => hiddenFileInput.click());
    }
    
    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', resetUploadPanel);
    }
    
    if (hiddenFileInput) {
        hiddenFileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                fileCountEl.textContent = e.target.files.length;
                imagePreview.innerHTML = '';
                Array.from(e.target.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            const previewItem = document.createElement('div');
                            previewItem.className = 'relative group';
                            previewItem.innerHTML = `<div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-blue-400 transition-colors"><img src="${ev.target.result}" class="w-full h-24 object-cover"><div class="p-2"><p class="text-xs text-gray-600 truncate">${file.name}</p></div></div>`;
                            imagePreview.appendChild(previewItem);
                        };
                        reader.readAsDataURL(file);
                    }
                });
                uploadContainer.classList.remove('hidden');
            }
        });
    }
    
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (hiddenFileInput.files.length === 0) {
                showNotification('Lütfen yüklenecek görsel seçin.', 'error');
                return;
            }
            const formData = new FormData(uploadForm);
            for (let i = 0; i < hiddenFileInput.files.length; i++) {
                formData.append('product_images[]', hiddenFileInput.files[i]);
            }
            
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            
            let progress = 0;
            const progressInterval = setInterval(() => {
                if (progress < 80) {
                    progress += 5;
                    progressBar.style.width = progress + '%';
                    progressText.textContent = progress + '%';
                }
            }, 150);
            
            fetch('ajax/image-upload.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressText.textContent = '100%';
                if (data.success) {
                    showNotification(data.message || 'Görseller başarıyla yüklendi.', 'success');
                    resetUploadPanel();
                    setTimeout(() => {
                        progressContainer.classList.add('hidden');
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Yükleme sırasında bir hata oluştu.', 'error');
                    setTimeout(() => progressContainer.classList.add('hidden'), 2000);
                }
            })
            .catch(error => {
                clearInterval(progressInterval);
                console.error('Upload error:', error);
                showNotification('Bağlantı hatası: ' + error.message, 'error');
                setTimeout(() => progressContainer.classList.add('hidden'), 2000);
            });
        });
    }
});
</script>
