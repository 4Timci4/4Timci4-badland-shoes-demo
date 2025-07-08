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
                    ?>
                    <div class="color-tab-content <?= $tab_index === 0 ? 'block' : 'hidden' ?>" 
                         id="color-<?= $color_id ?>" 
                         data-color="<?= $color_id ?>">
                        
                        
                        <!-- Image Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="sortable-<?= $color_id ?>">
                            <?php foreach ($images as $image): ?>
                                <div class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-200 cursor-move" 
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
                                            <span>Sıra: <?= $image['sort_order'] ?? 'N/A' ?></span>
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
                                <span class="text-blue-800 text-sm">Resimlerin sırasını değiştirmek için sürükleyip bırakın.</span>
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

// DOM elementlerine güvenli erişim
document.addEventListener('DOMContentLoaded', function() {
  try {
    // DOM elementleri
    const hiddenFileInput = document.getElementById('hidden-file-input');
    const uploadContainer = document.getElementById('upload-container');
    const uploadForm = document.getElementById('image-upload-form');
    const colorSelect = document.getElementById('color-select');
    const selectMoreFilesBtn = document.getElementById('select-more-files');
    const cancelUploadBtn = document.getElementById('cancel-upload');
    const fileCountEl = document.getElementById('selected-file-count');
    const imagePreview = document.getElementById('image-preview');
    const progressContainer = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    // Element kontrolü
    if (!hiddenFileInput || !uploadContainer || !uploadForm) {
      console.warn('Görsel yönetimi için gereken DOM elementleri bulunamadı');
      return; // Elementler yoksa işlemi sonlandır
    }

    // Görsel yükleme formunu açma
    function triggerFileInput(colorId = null) {
        // Renk seçimi varsa, select'i ayarla
        if (colorId && colorSelect) {
            colorSelect.value = colorId;
        }
        
        // Yükleme alanını göster ve dosya seçim penceresini aç
        uploadContainer.classList.remove('hidden');
        hiddenFileInput.click();
    }

    // Yükleme panelini sıfırlama ve kapatma
    function resetUploadPanel() {
        uploadContainer.classList.add('hidden');
        imagePreview.innerHTML = '';
        fileCountEl.textContent = '0';
        hiddenFileInput.value = '';
    }

    // Event Listeners - tüm elementlerin varlığını kontrol ederek ekle
    if (selectMoreFilesBtn) {
        selectMoreFilesBtn.addEventListener('click', function() {
            hiddenFileInput.click();
        });
    }

    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', resetUploadPanel);
    }

    // Görsel önizleme - element kontrolü
    if (hiddenFileInput) {
        hiddenFileInput.addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        // Dosya sayısını güncelle
        fileCountEl.textContent = e.target.files.length;
        
        // Önizleme alanını temizle ve yeni görselleri ekle
        imagePreview.innerHTML = '';
        
        Array.from(e.target.files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'relative group';
                    previewItem.innerHTML = `
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-blue-400 transition-colors">
                            <img src="${e.target.result}" class="w-full h-24 object-cover">
                            <div class="p-2">
                                <p class="text-xs text-gray-600 truncate">${file.name}</p>
                            </div>
                        </div>
                    `;
                    imagePreview.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Yükleme alanını göster
        uploadContainer.classList.remove('hidden');
    }
        });
    }

    // Global fonksiyonları window nesnesine ekle
    window.triggerFileInput = triggerFileInput;
    window.switchColorTab = switchColorTab;
    window.viewImage = viewImage;
    window.downloadImage = downloadImage;
    window.setPrimaryImage = setPrimaryImage;
    window.deleteImage = deleteImage;

    // Renk sekmesi değiştirme
    function switchColorTab(colorId, element) {
    // Tüm sekme içeriklerini gizle
    document.querySelectorAll('.color-tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('block');
    });
    
    // Seçili sekme içeriğini göster
    const targetContent = document.getElementById('color-' + colorId);
    if (targetContent) {
        targetContent.classList.remove('hidden');
        targetContent.classList.add('block');
    }
    
    // Sekme butonlarını güncelle
    document.querySelectorAll('.color-tab-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
        btn.classList.add('border-transparent', 'text-gray-500');
        
        // Rozet renklerini güncelle
        const badge = btn.querySelector('span');
        if (badge) {
            badge.classList.remove('bg-blue-100', 'text-blue-800');
            badge.classList.add('bg-gray-100', 'text-gray-800');
        }
    });
    
    // Tıklanan sekmeyi aktifleştir
    element.classList.remove('border-transparent', 'text-gray-500');
    element.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
    
    // Aktif sekmenin rozet renklerini güncelle
    const activeBadge = element.querySelector('span');
    if (activeBadge) {
        activeBadge.classList.remove('bg-gray-100', 'text-gray-800');
        activeBadge.classList.add('bg-blue-100', 'text-blue-800');
    }
}

// Büyük görsel görüntüleme
function viewImage(url) {
    document.getElementById('modal-image').src = url;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Görsel indirme
function downloadImage(url) {
    const link = document.createElement('a');
    link.href = url;
    link.download = url.split('/').pop();
    link.click();
}

// Ana görsel yapma
function setPrimaryImage(imageId) {
    if (!confirm('Bu görseli ana görsel yapmak istediğinizden emin misiniz?')) {
        return;
    }
    
    const csrf_token = document.querySelector('input[name="csrf_token"]').value;
    const product_id = <?= $product_id ?>;
    
    // İlerleme göstergesi
    showNotification('İşlem yapılıyor...', 'info');
    
    // AJAX isteği
    fetch('ajax/image-upload.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
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
            showNotification('Ana görsel başarıyla güncellendi', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification(data.error || 'Bilinmeyen hata', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bağlantı hatası', 'error');
    });
}

// Görsel silme
function deleteImage(imageId, buttonElement) {
    if (!confirm('Bu görseli silmek istediğinizden emin misiniz?')) {
        return;
    }
    
    const csrf_token = document.querySelector('input[name="csrf_token"]').value;
    const product_id = <?= $product_id ?>;
    
    // Görselin bulunduğu karta ulaş
    const imageCard = buttonElement.closest('[data-image-id="' + imageId + '"]');
    
    // İlerleme göstergesi
    showNotification('Görsel siliniyor...', 'info');
    
    // AJAX isteği
    fetch('ajax/image-upload.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
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
            // Görsel kartını animasyonla kaldır
            if (imageCard) {
                imageCard.style.transition = 'all 0.3s ease';
                imageCard.style.opacity = '0';
                imageCard.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    imageCard.remove();
                    
                    // Mevcut sekmede kalan görsel sayısını kontrol et
                    const currentTab = document.querySelector('.color-tab-content.block');
                    const remainingImages = currentTab.querySelectorAll('[data-image-id]');
                    
                    // Görsel kalmadıysa boş durum mesajını göster
                    if (remainingImages.length === 0) {
                        const emptyMessage = document.createElement('div');
                        emptyMessage.className = 'text-center py-8 bg-gray-50 rounded-xl';
                        emptyMessage.innerHTML = `
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-images text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-600">Bu kategoride henüz görsel bulunmuyor.</p>
                        `;
                        currentTab.appendChild(emptyMessage);
                    }
                    
                    // Sekme sayaçlarını güncelle
                    updateTabCounts();
                    
                    // Başarı bildirimi göster
                    showNotification('Görsel başarıyla silindi', 'success');
                }, 300);
            }
        } else {
            showNotification(data.error || 'Silme işlemi başarısız oldu', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bağlantı hatası', 'error');
    });
}

// Sekme sayaçlarını güncelleme
function updateTabCounts() {
    document.querySelectorAll('.color-tab-btn').forEach(tab => {
        const colorId = tab.getAttribute('data-color');
        const tabContent = document.getElementById('color-' + colorId);
        
        if (tabContent) {
            const imageCount = tabContent.querySelectorAll('[data-image-id]').length;
            const countBadge = tab.querySelector('span');
            
            if (countBadge) {
                countBadge.textContent = imageCount;
            }
        }
    });
}

// Bildirim gösterme fonksiyonu
function showNotification(message, type = 'info') {
    // Mevcut bildirimleri kaldır
    const existingAlerts = document.querySelectorAll('.alert-notification');
    existingAlerts.forEach(alert => alert.remove());
    
    // Bildirim tipi sınıfları
    const typeClasses = {
        'success': 'bg-green-50 border-green-200 text-green-800',
        'error': 'bg-red-50 border-red-200 text-red-800',
        'info': 'bg-blue-50 border-blue-200 text-blue-800'
    };
    
    // İkon sınıfları
    const iconClasses = {
        'success': 'fas fa-check-circle text-green-500',
        'error': 'fas fa-exclamation-triangle text-red-500',
        'info': 'fas fa-info-circle text-blue-500'
    };
    
    // Bildirim oluştur
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert-notification fixed top-5 right-5 p-4 rounded-lg border shadow-lg z-50 flex items-center ${typeClasses[type] || typeClasses.info}`;
    alertDiv.innerHTML = `
        <i class="${iconClasses[type] || iconClasses.info} mr-3"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(alertDiv);
    
    // 3 saniye sonra kaldır
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transform = 'translateY(-20px)';
        alertDiv.style.transition = 'all 0.5s ease';
        
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}

    // Form gönderimi - AJAX ile görsel yükleme
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Gizli dosya alanından dosyaları al
    if (hiddenFileInput.files.length === 0) {
        showNotification('Lütfen yüklenecek görsel seçin', 'error');
        return;
    }
    
    // Form verilerini oluştur
    const formData = new FormData(this);
    
    // Dosyaları ekle
    for (let i = 0; i < hiddenFileInput.files.length; i++) {
        formData.append('product_images[]', hiddenFileInput.files[i]);
    }
    
    // İlerleme çubuğunu göster
    progressContainer.classList.remove('hidden');
    progressBar.style.width = '0%';
    progressText.textContent = '0%';
    
    // Kullanıcı deneyimi için yapay ilerleme
    let progress = 0;
    const progressInterval = setInterval(() => {
        if (progress < 80) {
            progress += 5;
            progressBar.style.width = progress + '%';
            progressText.textContent = progress + '%';
        }
    }, 150);
    
    // AJAX yükleme
    fetch('ajax/image-upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        progressText.textContent = '100%';
        
        if (data.success) {
            // Başarı bildirimi göster
            showNotification(data.message || 'Görseller başarıyla yüklendi', 'success');
            
            // Formu sıfırla
            resetUploadPanel();
            
            // Sayfayı yenilemeden önce biraz bekle
            setTimeout(() => {
                progressContainer.classList.add('hidden');
                window.location.reload();
            }, 1000);
        } else {
            // Hata bildirimi göster
            showNotification(data.message || 'Yükleme sırasında bir hata oluştu', 'error');
            
            // İlerleme çubuğunu gizle
            setTimeout(() => {
                progressContainer.classList.add('hidden');
            }, 2000);
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        console.error('Upload error:', error);
        
        // Hata bildirimi göster
        showNotification('Bağlantı hatası: ' + error.message, 'error');
        
        // İlerleme çubuğunu gizle
        setTimeout(() => {
            progressContainer.classList.add('hidden');
        }, 2000);
    });
        });
    }

    // Sortable başlatma
    if (typeof Sortable !== 'undefined') {
    // Sürükle-bırak işlemi için Sortable'ı başlat
    document.querySelectorAll('[id^="sortable-"]').forEach(el => {
        if (typeof Sortable !== 'undefined') {
            new Sortable(el, {
                animation: 200,
                ghostClass: 'opacity-40',
                chosenClass: 'scale-105',
                dragClass: 'rotate-1',
                onEnd: function(evt) {
                    const items = evt.to.children;
                    const orderData = [];
                    
                    // Yeni sıralamayı oluştur
                    for (let i = 0; i < items.length; i++) {
                        const imageId = items[i].getAttribute('data-image-id');
                        if (imageId) {
                            orderData.push({
                                id: parseInt(imageId),
                                sort_order: i + 1
                            });
                        }
                    }
                    
                    // Sıralama boşsa işlem yapma
                    if (orderData.length === 0) return;
                    
                    // CSRF token ve ürün ID'si
                    const csrf_token = document.querySelector('input[name="csrf_token"]').value;
                    const product_id = <?= $product_id ?>;
                    
                    // AJAX ile sıralama güncelleme
                    fetch('ajax/image-upload.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
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
                            // Sessiz başarı bildirimi
                            showNotification('Görsel sıralaması güncellendi', 'success');
                        } else {
                            console.error('Sıralama güncellenirken hata oluştu');
                            showNotification('Sıralama güncellenirken hata oluştu', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Sıralama hatası:', error);
                        showNotification('Bağlantı hatası', 'error');
                    });
                }
            });
        }
    });
    }
  } catch (error) {
    console.error('Görsel yönetimi JavaScript hatası:', error);
  }
});
</script>
