<?php
/**
 * Ürün Resim Yükleme Sayfası
 */

require_once 'config/auth.php';
check_admin_auth();

require_once '../config/database.php';
require_once '../services/Product/ProductImageService.php';
require_once '../services/ProductService.php';
require_once '../services/CategoryService.php';

$pageTitle = 'Ürün Resim Yönetimi';
$productImageService = productImageService();
$productService = product_service();

// Ürün ID'si kontrolü
$product_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['product_id']) ? intval($_GET['product_id']) : 0);
if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Ürün bilgilerini getir
$product_data = $productService->getProductModel($product_id);
if (empty($product_data)) {
    header('Location: products.php');
    exit;
}
$product = $product_data[0];

// Renkleri getir
$colors = get_colors();

// Mevcut resimleri getir
$product_images = $productImageService->getProductImagesByColors($product_id);

// POST işlemleri
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error_message = 'Güvenlik hatası oluştu.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'upload':
                $color_id = !empty($_POST['color_id']) ? intval($_POST['color_id']) : null;
                
                if (!empty($_FILES['images']['name'][0])) {
                    $result = $productImageService->uploadProductImages($product_id, $color_id, $_FILES['images']);
                    
                    if ($result['success']) {
                        $success_message = "Başarıyla {$result['uploaded_count']} resim yüklendi.";
                        if (!empty($result['errors'])) {
                            $error_message = implode('<br>', $result['errors']);
                        }
                        // Sayfayı yenile
                        header('Location: product-image-upload.php?id=' . $product_id);
                        exit;
                    } else {
                        $error_message = implode('<br>', $result['errors']);
                    }
                } else {
                    $error_message = 'Lütfen yüklenecek resim seçin.';
                }
                break;
                
            case 'set_primary':
                $image_id = intval($_POST['image_id']);
                if ($productImageService->setPrimaryImage($image_id)) {
                    $success_message = 'Ana resim başarıyla ayarlandı.';
                } else {
                    $error_message = 'Ana resim ayarlanırken hata oluştu.';
                }
                break;
                
            case 'delete':
                $image_id = intval($_POST['image_id']);
                if ($productImageService->deleteImage($image_id)) {
                    $success_message = 'Resim başarıyla silindi.';
                } else {
                    $error_message = 'Resim silinirken hata oluştu.';
                }
                break;
                
            case 'reorder':
                $order_data = json_decode($_POST['order_data'], true);
                if ($productImageService->reorderImages($order_data)) {
                    $success_message = 'Resim sıralaması güncellendi.';
                } else {
                    $error_message = 'Sıralama güncellenirken hata oluştu.';
                }
                break;
        }
        
        // Resimleri yeniden getir
        $product_images = $productImageService->getProductImagesByColors($product_id);
    }
}

include 'includes/header.php';
?>

<!-- Modern Image Management Content -->
<div class="space-y-6">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-images mr-3 text-blue-600"></i><?= $pageTitle ?>
            </h1>
            <p class="text-gray-600">Ürün resimlerini yükleyin, düzenleyin ve yönetin</p>
        </div>
        <div class="mt-4 lg:mt-0">
            <a href="product-edit.php?id=<?= $product['id'] ?>" 
               class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                <i class="fas fa-edit mr-2"></i>
                Ürünü Düzenle
            </a>
        </div>
    </div>

    <!-- Product Info Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($product['name']) ?></h2>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            ID: <?= $product['id'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($success_message): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            <span class="text-green-800 font-medium"><?= htmlspecialchars($success_message) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
            <span class="text-red-800 font-medium"><?= $error_message ?></span>
        </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-cloud-upload-alt mr-2 text-blue-600"></i>
                Yeni Resim Yükle
            </h3>
        </div>
        <div class="p-6">
            <form method="POST" enctype="multipart/form-data" id="upload-form" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <input type="hidden" name="action" value="upload">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Renk Seçimi <span class="text-gray-400 font-normal">(Opsiyonel)</span>
                        </label>
                        <select name="color_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Tüm Renkler</option>
                            <?php foreach ($colors as $color): ?>
                                <option value="<?= $color['id'] ?>">
                                    <?= htmlspecialchars($color['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Belirli bir renge ait resimler için renk seçin.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Resimler <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="images[]" 
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                               multiple accept="image/*" required>
                        <div class="text-sm text-gray-500 mt-1">
                            <p>Çoklu resim seçebilirsiniz. Desteklenen formatlar: JPG, PNG, GIF, WebP</p>
                            <p>Maksimum dosya boyutu: 5MB, Önerilen boyut: 1200x1200px</p>
                        </div>
                    </div>
                </div>
                
                <div id="image-preview" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4"></div>
                
                <div class="flex justify-start">
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-upload mr-2"></i>
                        Resimleri Yükle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mevcut resimler -->
    <?php if (!empty($product_images)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-images me-2"></i>Mevcut Resimler
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Renk tabları -->
                        <ul class="nav nav-tabs" id="colorTabs" role="tablist">
                            <?php $tab_index = 0; ?>
                            <?php foreach ($product_images as $color_id => $images): ?>
                                <?php
                                $color_name = 'Genel';
                                if ($color_id !== 'default') {
                                    foreach ($colors as $color) {
                                        if ($color['id'] == $color_id) {
                                            $color_name = $color['name'];
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $tab_index === 0 ? 'active' : '' ?>" 
                                            id="color-<?= $color_id ?>-tab" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#color-<?= $color_id ?>" 
                                            type="button" role="tab">
                                        <?= htmlspecialchars($color_name) ?>
                                        <span class="badge bg-secondary ms-1"><?= count($images) ?></span>
                                    </button>
                                </li>
                                <?php $tab_index++; ?>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Tab içerikleri -->
                        <div class="tab-content mt-3" id="colorTabsContent">
                            <?php $tab_index = 0; ?>
                            <?php foreach ($product_images as $color_id => $images): ?>
                                <div class="tab-pane fade <?= $tab_index === 0 ? 'show active' : '' ?>" 
                                     id="color-<?= $color_id ?>" 
                                     role="tabpanel">
                                    
                                    <div class="row" id="sortable-<?= $color_id ?>">
                                        <?php foreach ($images as $image): ?>
                                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-image-id="<?= $image['id'] ?>">
                                                <div class="card image-card">
                                                    <div class="position-relative">
                                                        <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                                                             class="card-img-top" 
                                                             style="height: 200px; object-fit: cover;"
                                                             alt="<?= htmlspecialchars($image['alt_text']) ?>">
                                                        
                                                        <?php if ($image['is_primary']): ?>
                                                            <span class="badge bg-warning position-absolute top-0 start-0 m-2">
                                                                <i class="fas fa-star"></i> Ana Resim
                                                            </span>
                                                        <?php endif; ?>
                                                        
                                                        <div class="position-absolute top-0 end-0 m-2">
                                                            <div class="btn-group-vertical">
                                                                <?php
                                                                    $image_url = $image['image_url'] ?? '';
                                                                    $original_url = str_replace('/optimized/', '/original/', $image_url);
                                                                    $original_url = preg_replace('/_optimized(\..+?)$/', '$1', $original_url);
                                                                ?>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-light"
                                                                        onclick="viewImage('<?= htmlspecialchars($original_url) ?>')"
                                                                        title="Büyük Görüntüle">
                                                                    <i class="fas fa-search-plus"></i>
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-sm btn-outline-light"
                                                                        onclick="downloadImage('<?= htmlspecialchars($original_url) ?>')"
                                                                        title="İndir">
                                                                    <i class="fas fa-download"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card-body p-2">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <small class="text-muted">
                                                                Sıra: <?= $image['sort_order'] ?? 'N/A' ?>
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="btn-group w-100" role="group">
                                                            <?php if (!$image['is_primary']): ?>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                                    <input type="hidden" name="action" value="set_primary">
                                                                    <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                                                    <button type="submit" class="btn btn-outline-warning btn-sm" title="Ana Resim Yap">
                                                                        <i class="fas fa-star"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                            
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Bu resmi silmek istediğinizden emin misiniz?')">
                                                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Sil">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if (count($images) > 1): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Resimlerin sırasını değiştirmek için sürükleyip bırakın.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php $tab_index++; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Henüz resim yüklenmemiş</h5>
                        <p class="text-muted">Bu ürün için henüz hiç resim yüklenmemiş. Yukarıdaki formu kullanarak resim yükleyebilirsiniz.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Resim görüntüleme modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resim Görüntüle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modal-image" src="" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>

<script>
// Resim önizleme
document.querySelector('input[name="images[]"]').addEventListener('change', function(e) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-lg-2 col-md-3 col-sm-4 col-6 mb-2';
                col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                        <div class="card-body p-1">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Resim modal
function viewImage(url) {
    document.getElementById('modal-image').src = url;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Resim indirme
function downloadImage(url) {
    const link = document.createElement('a');
    link.href = url;
    link.download = url.split('/').pop();
    link.click();
}

// Sortable (sürükle-bırak)
<?php if (!empty($product_images)): ?>
    <?php foreach (array_keys($product_images) as $color_id): ?>
        new Sortable(document.getElementById('sortable-<?= $color_id ?>'), {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                const items = evt.to.children;
                const orderData = [];
                
                for (let i = 0; i < items.length; i++) {
                    const imageId = items[i].getAttribute('data-image-id');
                    orderData.push({
                        id: parseInt(imageId),
                        sort_order: i + 1
                    });
                }
                
                // AJAX ile sıralamayı güncelle
                const formData = new FormData();
                formData.append('csrf_token', '<?= generate_csrf_token() ?>');
                formData.append('action', 'reorder');
                formData.append('order_data', JSON.stringify(orderData));
                
                fetch('', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    // Sayfa yenilenmeden güncelleme bildirimi
                    // Toast notification burada eklenebilir
                });
            }
        });
    <?php endforeach; ?>
<?php endif; ?>
</script>

<style>
.image-card {
    transition: transform 0.2s;
}

.image-card:hover {
    transform: translateY(-2px);
}

.sortable-ghost {
    opacity: 0.4;
}

.sortable-chosen {
    transform: scale(1.02);
}

.sortable-drag {
    transform: rotate(5deg);
}

#image-preview .card {
    border: 2px dashed #dee2e6;
}
</style>

<?php include 'includes/footer.php'; ?>
